<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cost;
use App\Models\CostType;
use App\Models\NhaCungCap;
use App\Models\Ingredient;
use App\Models\Branch;
use App\Models\Collecter;
use App\Services\CostDetailService;
use Auth, DB;

/**
 * API nhập chi phí nhanh: Claude đọc bill/text trong chat, anh Hoàng xác nhận, rồi Claude gọi API này
 * từ trình duyệt đã đăng nhập (cùng session + CSRF, nằm trong middleware auth).
 *
 *  GET  /cost/api/master-data      danh mục để map ID + csrf_token
 *  POST /cost/api/check-duplicate  kiểm tra bill trùng (cùng ngày + cùng tổng tiền)
 *  POST /cost/api/store-batch      lưu nhiều bill (all-or-nothing)
 *  GET  /cost/bill-upload          trang kéo thả ảnh bill -> uploads/bill-inbox/
 *  POST /cost/api/upload-bill      nhận ảnh bill (multipart files[])
 *  POST /cost/api/inbox-delete     bỏ 1 ảnh khỏi danh sách chờ (chuyển vào bill-inbox-trash/)
 *  GET  /cost/api/inbox            danh sách ảnh chờ nhập; store-batch nhận "inbox_files":["tên file"]
 */
class CostApiController extends Controller
{
    const DEFAULT_NGUOI_CHI = 2; // Tiền mặt (collecter id 2)
    const DEFAULT_STATUS = 2;    // Paid
    const SOURCE_API = 2;        // cost_detail.source: nhập qua API
    const MAX_IMAGE_BYTES = 8388608; // 8MB / ảnh
    const INBOX_DIR = 'bill-inbox/'; // ảnh bill chờ nhập, nằm trong public/uploads/

    /** Trang upload bill (kéo thả ảnh) */
    public function billUpload()
    {
        return view('cost.bill-upload');
    }

    /** POST multipart files[]: lưu ảnh vào uploads/bill-inbox/ */
    public function uploadBill(Request $request)
    {
        $files = $request->file('files', []);
        $saved = [];
        $errors = [];
        $map = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $dir = config('plantotravel.upload_path').self::INBOX_DIR;
        if(!is_dir($dir)){
            mkdir($dir, 0775, true);
        }
        foreach((array) $files as $file){
            if(!$file || !$file->isValid()){
                $errors[] = 'File upload lỗi';
                continue;
            }
            $info = @getimagesize($file->getRealPath());
            if(!$info || !isset($map[$info['mime']]) || $file->getSize() > self::MAX_IMAGE_BYTES){
                $errors[] = $file->getClientOriginalName().': chỉ nhận jpg/png/webp, tối đa 8MB';
                continue;
            }
            $name = date('Ymd-His').'-'.substr(md5(uniqid('', true)), 0, 6).'.'.$map[$info['mime']];
            $file->move($dir, $name);
            $saved[] = ['name' => $name, 'url' => config('plantotravel.upload_url').self::INBOX_DIR.$name];
        }
        return response()->json(['ok' => count($errors) == 0, 'saved' => $saved, 'errors' => $errors]);
    }

    /** POST {name}: bỏ ảnh khỏi danh sách chờ -> chuyển sang uploads/bill-inbox-trash/ (khôi phục được) */
    public function inboxDelete(Request $request)
    {
        $name = basename((string) $request->input('name'));
        $from = config('plantotravel.upload_path').self::INBOX_DIR.$name;
        if(!preg_match('/^[\w\-]+\.(jpg|png|webp)$/', $name) || !is_file($from)){
            return response()->json(['ok' => false, 'errors' => ['Không thấy ảnh '.$name]], 404);
        }
        $trash = config('plantotravel.upload_path').'bill-inbox-trash/';
        if(!is_dir($trash)){
            mkdir($trash, 0775, true);
        }
        if(!@rename($from, $trash.$name)){
            return response()->json(['ok' => false, 'errors' => ['Không xóa được ảnh '.$name]], 500);
        }
        return response()->json(['ok' => true]);
    }

    /** GET: danh sách ảnh bill đang chờ nhập (cũ nhất trước) */
    public function inbox()
    {
        $dir = config('plantotravel.upload_path').self::INBOX_DIR;
        $list = [];
        foreach(is_dir($dir) ? scandir($dir) : [] as $f){
            if(!preg_match('/^[\w\-]+\.(jpg|png|webp)$/', $f)) continue;
            $list[] = ['name' => $f, 'url' => config('plantotravel.upload_url').self::INBOX_DIR.$f,
                       'size' => filesize($dir.$f), 'uploaded_at' => date('Y-m-d H:i:s', filemtime($dir.$f))];
        }
        usort($list, function ($a, $b) { return strcmp($a['name'], $b['name']); });
        return response()->json(['items' => $list]);
    }

    public function masterData()
    {
        return response()->json([
            'csrf_token' => csrf_token(),
            'user' => ['id' => Auth::user()->id, 'name' => Auth::user()->name, 'branch_id' => Auth::user()->branch_id],
            'defaults' => ['nguoi_chi' => self::DEFAULT_NGUOI_CHI, 'status' => self::DEFAULT_STATUS],
            'status_list' => [['id' => 1, 'name' => 'Not yet paid'], ['id' => 2, 'name' => 'Paid'], ['id' => 3, 'name' => 'Pay later']],
            'cost_types' => CostType::where('status', 1)->orderBy('display_order')->get(['id', 'name']),
            'suppliers' => NhaCungCap::where('status', 1)->orderBy('display_order')->orderBy('name')->get(['id', 'name', 'group_name']),
            'ingredients' => Ingredient::active()->orderBy('name')->get(['id', 'name', 'name_en', 'aliases', 'unit', 'cost_type_id']),
            'branches' => Branch::where('status', 1)->orderBy('display_order')->get(['id', 'name']),
            'payers' => Collecter::where('status', 1)->orderBy('display_order')->get(['id', 'name']),
        ]);
    }

    /**
     * Body: {"items":[{"date_use":"12/11/2026","total_money":350000}, ...]}
     */
    public function checkDuplicate(Request $request)
    {
        $result = [];
        foreach((array) $request->input('items', []) as $i => $item){
            $result[] = [
                'index' => $i,
                'duplicates' => $this->findDuplicates($item['date_use'] ?? null, $item['total_money'] ?? null),
            ];
        }
        return response()->json(['items' => $result]);
    }

    /**
     * Body: {"costs":[{
     *    "date_use":"12/11/2026", "cate_id":5, "ncc_id":3, "branch_id":1,
     *    "nguoi_chi":1, "status":2, "total_money":350000, "notes":"...",
     *    "allow_duplicate":false,
     *    "images":[{"name":"bill.jpg","data":"<base64 hoặc data:image/jpeg;base64,...>"}],
     *    "details":[{"ingredient_id":12,"quantity":2,"unit":"kg","unit_price":50000,"amount":100000,"notes":"","need_review":0}]
     * }]}
     * Lỗi ở bất kỳ bill nào -> không lưu bill nào.
     */
    public function storeBatch(Request $request)
    {
        $costs = $request->input('costs', []);
        if(!is_array($costs) || count($costs) == 0){
            return response()->json(['ok' => false, 'errors' => ['Không có bill nào']], 422);
        }

        // 1. Kiểm tra toàn bộ trước khi ghi
        $errors = [];
        $prepared = [];
        $validCate = CostType::pluck('id')->all();
        $validNcc = NhaCungCap::pluck('id')->all();
        $validIng = Ingredient::pluck('id')->all();
        $validPayer = Collecter::pluck('id')->all();
        foreach($costs as $i => $c){
            $label = 'Bill #'.($i + 1);
            $date = $this->toSqlDate($c['date_use'] ?? null);
            $total = CostDetailService::toNumber($c['total_money'] ?? null);
            if(!$date) $errors[] = "$label: ngày không hợp lệ (dd/mm/yyyy)";
            if(!$total || $total <= 0) $errors[] = "$label: thiếu tổng tiền";
            $cateId = !empty($c['cate_id']) ? (int) $c['cate_id'] : null;
            if(!$cateId || !in_array($cateId, $validCate)) $errors[] = "$label: loại chi phí không hợp lệ";
            $nccId = !empty($c['ncc_id']) ? (int) $c['ncc_id'] : null;
            if($nccId && !in_array($nccId, $validNcc)) $errors[] = "$label: nhà cung cấp không hợp lệ";
            $nguoiChi = !empty($c['nguoi_chi']) ? (int) $c['nguoi_chi'] : self::DEFAULT_NGUOI_CHI;
            if(!in_array($nguoiChi, $validPayer)) $errors[] = "$label: người chi không hợp lệ";
            $status = !empty($c['status']) ? (int) $c['status'] : self::DEFAULT_STATUS;
            if(!in_array($status, [1, 2, 3])) $errors[] = "$label: status không hợp lệ";
            foreach((array) ($c['details'] ?? []) as $j => $d){
                if(!empty($d['ingredient_id']) && !in_array((int) $d['ingredient_id'], $validIng)){
                    $errors[] = "$label dòng ".($j + 1).": nguyên liệu không hợp lệ";
                }
            }
            $images = [];
            foreach((array) ($c['images'] ?? []) as $k => $img){
                $bin = $this->decodeImage($img['data'] ?? '');
                if($bin === null){
                    $errors[] = "$label ảnh ".($k + 1).": không đọc được ảnh (chỉ nhận jpg/png/webp, tối đa 8MB)";
                }else{
                    $images[] = ['name' => $img['name'] ?? 'bill', 'bin' => $bin[0], 'ext' => $bin[1]];
                }
            }
            $inboxFiles = [];
            foreach((array) ($c['inbox_files'] ?? []) as $f){
                $f = basename((string) $f);
                if(!preg_match('/^[\w\-]+\.(jpg|png|webp)$/', $f) || !is_file(config('plantotravel.upload_path').self::INBOX_DIR.$f)){
                    $errors[] = "$label: không thấy ảnh $f trong bill-inbox";
                }else{
                    $inboxFiles[] = $f;
                }
            }
            if($date && $total && empty($c['allow_duplicate'])){
                $dups = $this->findDuplicates($date, $total);
                if($dups){
                    $errors[] = "$label: nghi trùng với cost id ".implode(', ', array_column($dups, 'id'))." (gửi allow_duplicate=true nếu vẫn muốn lưu)";
                }
            }
            if(in_array($nguoiChi, [1, self::DEFAULT_NGUOI_CHI])){ // tiền mặt thì luôn Đã thanh toán
                $status = 2;
            }
            $prepared[] = [
                'data' => [
                    'date_use' => $date,
                    'cate_id' => $cateId,
                    'ncc_id' => $nccId,
                    'branch_id' => !empty($c['branch_id']) ? (int) $c['branch_id'] : Auth::user()->branch_id,
                    'nguoi_chi' => $nguoiChi,
                    'status' => $status,
                    'total_money' => $total,
                    'price' => $total,
                    'amount' => 1,
                    'notes' => isset($c['notes']) ? trim($c['notes']) : null,
                    'type' => 1,
                    'city_id' => 1,
                    'is_fixed' => 0,
                    'xe_4t' => 0,
                ],
                'details' => (array) ($c['details'] ?? []),
                'images' => $images,
                'inbox_files' => $inboxFiles,
            ];
        }
        if($errors){
            return response()->json(['ok' => false, 'errors' => $errors], 422);
        }

        // 2. Ghi ảnh + DB
        $userId = Auth::user()->id;
        $savedFiles = [];
        $movedFiles = [];
        try{
            $ids = DB::transaction(function () use ($prepared, $userId, &$savedFiles, &$movedFiles) {
                $ids = [];
                foreach($prepared as $p){
                    $paths = [];
                    foreach($p['images'] as $img){
                        $paths[] = $savedFiles[] = $this->saveImage($img);
                    }
                    foreach($p['inbox_files'] as $f){
                        $paths[] = $this->moveInboxFile($f, $movedFiles);
                    }
                    $data = $p['data'];
                    $data['created_user'] = $data['updated_user'] = $userId;
                    if($paths){
                        // Giống bill nhập tay: ảnh chính ở image_url (đường dẫn tương đối với uploads/)
                        $data['image_url'] = $paths[0];
                        if(count($paths) > 1){
                            $data['image_list'] = json_encode(array_map(function ($path) {
                                return $this->imageListValue($path);
                            }, $paths));
                        }
                    }
                    $cost = Cost::create($data);
                    CostDetailService::saveRows($cost, $p['details'], $userId, self::SOURCE_API);
                    $cost->refresh();
                    $ids[] = ['id' => $cost->id, 'date_use' => $cost->date_use, 'total_money' => $cost->total_money,
                              'detail_total' => $cost->detail_total, 'detail_check' => $cost->detail_check];
                }
                return $ids;
            });
        }catch(\Exception $e){
            foreach($savedFiles as $f){
                @unlink(config('plantotravel.upload_path').$f);
            }
            foreach($movedFiles as $m){ // trả ảnh về bill-inbox
                @rename(config('plantotravel.upload_path').$m[1], config('plantotravel.upload_path').$m[0]);
            }
            return response()->json(['ok' => false, 'errors' => ['Lỗi khi lưu: '.$e->getMessage()]], 500);
        }
        return response()->json(['ok' => true, 'saved' => $ids]);
    }

    private function findDuplicates($date, $total)
    {
        $date = $this->toSqlDate($date);
        $total = CostDetailService::toNumber($total);
        if(!$date || !$total) return [];
        return Cost::with('costType')->where('city_id', 1)->where('date_use', $date)->where('total_money', $total)
            ->get()->map(function ($c) {
                return ['id' => $c->id, 'date_use' => $c->date_use, 'total_money' => $c->total_money,
                        'cate' => $c->costType ? $c->costType->name : null, 'notes' => $c->notes];
            })->all();
    }

    /** "12/11/2026" hoặc "2026-11-12" -> "2026-11-12" */
    private function toSqlDate($value)
    {
        $value = trim((string) $value);
        if(preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $value, $m)){
            return checkdate($m[2], $m[1], $m[3]) ? sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]) : null;
        }
        if(preg_match('#^(\d{4})-(\d{1,2})-(\d{1,2})$#', $value, $m)){
            return checkdate($m[2], $m[3], $m[1]) ? sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]) : null;
        }
        return null;
    }

    /** @return array|null [binary, ext] */
    private function decodeImage($data)
    {
        if(!is_string($data) || $data === '') return null;
        if(strpos($data, 'base64,') !== false){
            $data = substr($data, strpos($data, 'base64,') + 7);
        }
        $bin = base64_decode($data, true);
        if($bin === false || strlen($bin) > self::MAX_IMAGE_BYTES) return null;
        $info = @getimagesizefromstring($bin);
        $map = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if(!$info || !isset($map[$info['mime']])) return null;
        return [$bin, $map[$info['mime']]];
    }

    /** Lưu ảnh vào public/uploads/Y/m/d/, trả về đường dẫn tương đối với uploads/ */
    private function saveImage($img)
    {
        $dir = date('Y/m/d').'/'; // cùng thư mục với ảnh upload từ form (uploads/Y/m/d/)
        $full = config('plantotravel.upload_path').$dir;
        if(!is_dir($full)){
            mkdir($full, 0777, true);
        }
        $name = str_slug(pathinfo($img['name'], PATHINFO_FILENAME), '-') ?: 'bill';
        $file = $name.'-'.time().'-'.substr(md5(uniqid('', true)), 0, 6).'.'.$img['ext'];
        if(file_put_contents($full.$file, $img['bin']) === false){
            throw new \Exception('Không ghi được ảnh '.$file);
        }
        return $dir.$file;
    }

    /** Chuyển ảnh từ uploads/bill-inbox/ sang uploads/Y/m/d/, trả về đường dẫn tương đối với uploads/ */
    private function moveInboxFile($name, &$movedFiles)
    {
        $dir = date('Y/m/d').'/';
        $full = config('plantotravel.upload_path').$dir;
        if(!is_dir($full)){
            mkdir($full, 0775, true);
        }
        $from = self::INBOX_DIR.$name;
        $to = $dir.'bill-'.$name;
        if(!@rename(config('plantotravel.upload_path').$from, config('plantotravel.upload_path').$to)){
            throw new \Exception('Không chuyển được ảnh '.$name);
        }
        $movedFiles[] = [$from, $to];
        return $to;
    }

    /** Định dạng phần tử trong cost.image_list (giống ảnh upload từ form, hiển thị bằng Helper::showImage) */
    private function imageListValue($path)
    {
        return '/uploads/'.$path;
    }
}
