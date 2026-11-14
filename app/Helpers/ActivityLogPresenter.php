<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

/**
 * Hiển thị lịch sử thay đổi dễ đọc: tên cột -> nhãn như trên form, số tiền có dấu phẩy,
 * ID liên kết -> tên (món, chi nhánh, loại chi phí, người thu/chi, NCC, nguyên liệu, đối tác, user).
 */
class ActivityLogPresenter
{
    // nhãn dùng chung
    protected static $common = [
        'id' => 'ID',
        'branch_id' => 'Chi nhánh',
        'status' => 'Trạng thái',
        'notes' => 'Ghi chú',
        'name' => 'Tên',
        'phone' => 'Số điện thoại',
        'email' => 'Email',
        'display_order' => 'Thứ tự',
        'image_url' => 'Ảnh',
        'created_user' => 'Người tạo',
        'user_id' => 'Người dùng',
        'city_id' => 'Thành phố',
        'description' => 'Mô tả',
        'address' => 'Địa chỉ',
        'contact_name' => 'Người liên hệ',
        'group_name' => 'Nhóm',
        'type' => 'Loại',
    ];

    // nhãn theo bảng (lấy theo form nhập)
    protected static $labels = [
        'booking' => [
            'name' => 'Tên khách', 'phone' => 'Số điện thoại', 'use_date' => 'Ngày', 'table_no' => 'Số bàn', 'bill_no' => 'Số bill',
            'total_price' => 'Tổng tiền (Total)', 'discount' => 'Giảm giá (Discount)', 'con_lai' => 'Doanh thu (Revenue)',
            'nguoi_thu_tien' => 'Người thu tiền (Receiver)', 'rupees' => 'Rupees', 'notes' => 'Ghi chú',
            'per_com' => 'Chiết khấu (%)', 'commision' => 'Tiền chiết khấu', 'tien_coc' => 'Tiền cọc', 'extra_fee' => 'Phụ thu',
            'take_away' => 'Mang đi (Take away)', 'indian' => 'Khách Ấn', 'no_vat' => 'Không VAT', 'da_thu' => 'Đã thu',
            'partner_id' => 'Đối tác (cũ)', 'hdv_id' => 'HDV', 'doi_tac_id' => 'Đối tác', 'co_chiet_khau' => 'Có chiết khấu (hoa hồng)',
            'ghi_no' => 'Ghi nợ', 'bill_type' => 'Loại bill', 'pax' => 'Số khách buffet', 'com_amount' => 'Hoa hồng đối tác',
            'checked' => 'Đã kiểm tra', 'xe_4t' => 'Xe 4T', 'chiet_khau' => 'Chiết khấu',
        ],
        'booking_detail' => [
            'booking_id' => 'Bill', 'dish_id' => 'Món', 'amount' => 'Số lượng', 'price' => 'Đơn giá', 'total_price' => 'Thành tiền',
            'notes' => 'Ghi chú món', 'is_drink' => 'Đồ uống', 'done' => 'Đã ra món', 'done_qty' => 'SL đã ra',
        ],
        'booking_payment' => ['booking_id' => 'Bill', 'amount' => 'Số tiền', 'pay_date' => 'Ngày thanh toán', 'type' => 'Hình thức'],
        'cost' => [
            'date_use' => 'Ngày (Date)', 'cate_id' => 'Loại chi phí (Cost category)', 'content' => 'Nội dung (Content)',
            'price' => 'Đơn giá (Price)', 'amount' => 'Số lượng (Amount)', 'total_money' => 'Tổng tiền (Total)',
            'ncc_id' => 'Nhà cung cấp', 'nguoi_chi' => 'Người chi (Payer)', 'image_url' => 'Ảnh', 'image_list' => 'Ảnh khác',
            'partner_id' => 'Đối tác', 'bank_info_id' => 'Tài khoản ngân hàng', 'unc_type' => 'Loại chứng từ',
            'thuc_chi' => 'Thực chi', 'detail_total' => 'Tổng chi tiết hàng', 'detail_check' => 'Kiểm tra chi tiết',
            'time_chi_tien' => 'Thời gian chi', 'is_fixed' => 'Chi phí cố định', 'booking_id' => 'Bill',
        ],
        'cost_detail' => [
            'cost_id' => 'Chi phí', 'ingredient_id' => 'Nguyên liệu', 'quantity' => 'Số lượng', 'unit' => 'Đơn vị',
            'unit_price' => 'Đơn giá', 'amount' => 'Thành tiền', 'need_review' => 'Cần kiểm tra', 'source' => 'Nguồn nhập',
        ],
        'cost_payment' => ['cost_id' => 'Chi phí', 'amount' => 'Số tiền', 'type' => 'Hình thức'],
        'cost_type' => ['name' => 'Tên loại chi phí', 'group_name' => 'Nhóm báo cáo', 'is_show' => 'Hiện nhanh'],
        'partners' => [
            'name' => 'Tên đối tác', 'type' => 'Loại đối tác', 'doi_tac' => 'Là đối tác bán hàng', 'cost_type_id' => 'Loại chi phí',
            'tax_code' => 'Mã số thuế', 'bank_info' => 'Tài khoản ngân hàng', 'pay_term_days' => 'Hạn thanh toán (ngày)',
            'com_buffet_per_pax' => 'Hoa hồng buffet / khách', 'com_alacarte_pct' => 'Hoa hồng alacarte (%)', 'phone_2' => 'Số điện thoại 2',
        ],
        'partner_transactions' => [
            'partner_id' => 'Đối tác', 'date' => 'Ngày', 'type' => 'Loại giao dịch', 'amount' => 'Số tiền',
            'pay_method' => 'Quỹ', 'cost_id' => 'Chi phí liên quan', 'updated_user' => 'Người sửa',
        ],
        'dish' => ['name' => 'Tên món', 'price' => 'Giá', 'category_id' => 'Danh mục', 'is_drink' => 'Đồ uống', 'is_bread' => 'Bánh mì'],
        'ingredient' => ['name' => 'Tên nguyên liệu', 'name_en' => 'Tên tiếng Anh', 'aliases' => 'Tên gọi khác', 'cost_type_id' => 'Loại chi phí', 'unit' => 'Đơn vị'],
        'nha_cung_cap' => ['name' => 'Tên nhà cung cấp'],
        'users' => ['name' => 'Họ tên', 'role' => 'Quyền', 'level' => 'Cấp', 'is_partner' => 'Là đối tác', 'is_staff' => 'Là nhân viên',
            'salary' => 'Lương', 'role_id' => 'Nhóm quyền', 'date_join' => 'Ngày vào làm', 'birthday' => 'Ngày sinh', 'is_view' => 'Chỉ xem', 'changed_password' => 'Đã đổi mật khẩu'],
        'branch' => ['name' => 'Tên chi nhánh'],
        'roles' => ['name' => 'Tên nhóm', 'all_branches' => 'Thấy tất cả chi nhánh', 'permissions' => 'Quyền'],
        'user_branches' => ['branch_ids' => 'Chi nhánh được gán'],
        'collecter' => ['name' => 'Tên'],
    ];

    // cột tiền (định dạng 1,234,567)
    protected static $money = [
        '*' => ['total_price', 'discount', 'con_lai', 'commision', 'tien_coc', 'extra_fee', 'total_money', 'unit_price',
            'thuc_chi', 'detail_total', 'com_amount', 'com_buffet_per_pax', 'salary', 'rupees'],
        'booking_detail' => ['price'],
        'booking_payment' => ['amount'],
        'cost' => ['price'],
        'cost_detail' => ['amount'],
        'cost_payment' => ['amount'],
        'partner_transactions' => ['amount'],
        'dish' => ['price'],
    ];

    // cột số lượng (bỏ .00)
    protected static $qty = ['amount', 'quantity', 'done_qty', 'pax'];

    // cột Có/Không
    protected static $bool = ['take_away', 'no_vat', 'da_thu', 'co_chiet_khau', 'ghi_no', 'is_drink', 'is_bread', 'done', 'need_review',
        'doi_tac', 'all_branches', 'is_fixed', 'checked', 'xe_4t', 'is_partner', 'is_staff', 'is_view', 'is_show', 'changed_password'];

    // cột ID -> bảng tra tên
    protected static $refs = [
        'dish_id' => 'dish', 'branch_id' => 'branch', 'cate_id' => 'cost_type', 'cost_type_id' => 'cost_type',
        'nguoi_chi' => 'collecter', 'nguoi_thu_tien' => 'collecter', 'pay_method' => 'collecter', 'ncc_id' => 'nha_cung_cap',
        'ingredient_id' => 'ingredient', 'doi_tac_id' => 'partners', 'created_user' => 'users', 'updated_user' => 'users',
        'user_id' => 'users', 'role_id' => 'roles',
    ];
    // cột partner_id: booking -> users (cũ), còn lại -> partners
    protected static $refsByTable = [
        'booking' => ['partner_id' => 'users', 'hdv_id' => 'users'],
        'cost' => ['partner_id' => 'partners'],
        'partner_transactions' => ['partner_id' => 'partners'],
    ];

    protected $names = []; // [table][id] => name
    protected $bookingDetailDish = []; // booking_detail.id => dish_id (dòng đã xóa không còn)
    protected $costDetailIng = [];

    /** Nạp trước tên các ID xuất hiện trong trang log để không query từng dòng. */
    public function prepare($items)
    {
        $need = [];
        foreach ($items as $it) {
            $rows = [json_decode((string) $it->old_values, true) ?: [], json_decode((string) $it->new_values, true) ?: []];
            foreach ($rows as $r) {
                foreach ($r as $k => $v) {
                    $t = $this->refTable($it->model, $k);
                    if ($t && is_numeric($v) && $v > 0) $need[$t][(int) $v] = 1;
                }
            }
            if ($it->model == 'booking_detail') $need['_bd'][(int) $it->model_id] = 1;
            if ($it->model == 'cost_detail') $need['_cd'][(int) $it->model_id] = 1;
        }
        if (!empty($need['_bd'])) {
            $this->bookingDetailDish = DB::table('booking_detail')->whereIn('id', array_keys($need['_bd']))->pluck('dish_id', 'id')->all();
            foreach ($this->bookingDetailDish as $d) if ($d) $need['dish'][(int) $d] = 1;
        }
        if (!empty($need['_cd'])) {
            $this->costDetailIng = DB::table('cost_detail')->whereIn('id', array_keys($need['_cd']))->pluck('ingredient_id', 'id')->all();
            foreach ($this->costDetailIng as $d) if ($d) $need['ingredient'][(int) $d] = 1;
        }
        // dòng đã bị xóa khỏi bảng: lấy món / nguyên liệu từ chính log thêm/xóa của dòng đó
        foreach (['_bd' => ['booking_detail', 'dish_id', 'bookingDetailDish', 'dish'], '_cd' => ['cost_detail', 'ingredient_id', 'costDetailIng', 'ingredient']] as $tag => $cfg) {
            list($tbl, $col, $prop, $refTbl) = $cfg;
            $missing = array_diff(array_keys($need[$tag] ?? []), array_keys($this->$prop));
            if (!$missing) continue;
            $rows = DB::table('activity_log')->where('model', $tbl)->whereIn('model_id', $missing)
                ->whereIn('action', ['created', 'deleted'])->get(['model_id', 'old_values', 'new_values']);
            foreach ($rows as $r) {
                $vals = (json_decode((string) $r->new_values, true) ?: []) + (json_decode((string) $r->old_values, true) ?: []);
                if (!empty($vals[$col])) {
                    $this->{$prop}[(int) $r->model_id] = (int) $vals[$col];
                    $need[$refTbl][(int) $vals[$col]] = 1;
                }
            }
        }
        unset($need['_bd'], $need['_cd']);
        foreach ($need as $table => $ids) {
            try {
                $this->names[$table] = DB::table($table)->whereIn('id', array_keys($ids))->pluck('name', 'id')->all();
            } catch (\Throwable $e) {
                $this->names[$table] = [];
            }
        }
        return $this;
    }

    protected function refTable($model, $key)
    {
        if (isset(self::$refsByTable[$model][$key])) return self::$refsByTable[$model][$key];
        return self::$refs[$key] ?? null;
    }

    public function label($model, $key)
    {
        $l = self::$labels[$model][$key] ?? self::$common[$key] ?? null;
        return $l === null ? $key : __($l);
    }

    /** Thứ tự hiển thị: theo thứ tự khai báo nhãn trên form, cột lạ để cuối. */
    public function sortKeys($model, array $keys)
    {
        $order = array_flip(array_merge(array_keys(self::$labels[$model] ?? []), array_keys(self::$common)));
        usort($keys, function ($a, $b) use ($order) {
            return ($order[$a] ?? 999) <=> ($order[$b] ?? 999);
        });
        return $keys;
    }

    /** Giá trị đã định dạng (đã escape HTML). */
    public function value($model, $key, $v)
    {
        if ($v === null || $v === '') return '<i class="text-muted">' . e(__('trống')) . '</i>';
        if (is_array($v)) return e(json_encode($v, JSON_UNESCAPED_UNICODE));

        $money = array_merge(self::$money['*'], self::$money[$model] ?? []);
        if (in_array($key, $money) && is_numeric($v)) return e(number_format((float) $v)) . ' đ';

        $ref = $this->refTable($model, $key);
        if ($ref) {
            $name = $this->names[$ref][(int) $v] ?? null;
            return $name !== null ? e($name) . ' <small class="text-muted">#' . e($v) . '</small>' : '#' . e($v);
        }
        if (in_array($key, ['booking_id', 'cost_id']) && is_numeric($v)) {
            $url = null;
            try { $url = $key == 'booking_id' ? route('booking.edit', $v) : route('cost.edit', $v); } catch (\Throwable $e) {}
            return $url ? '<a href="' . e($url) . '" target="_blank">#' . e($v) . '</a>' : '#' . e($v);
        }
        if (in_array($key, self::$bool)) return ((int) $v) ? e(__('Có')) : e(__('Không'));
        if (in_array($key, self::$qty) && is_numeric($v)) return e(rtrim(rtrim(number_format((float) $v, 2), '0'), '.'));

        $enum = $this->enumValue($model, $key, $v);
        if ($enum !== null) return e($enum);

        if (preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', (string) $v)) {
            $ts = strtotime($v);
            return e(strlen($v) > 10 ? date('d/m/Y H:i', $ts) : date('d/m/Y', $ts));
        }
        $s = (string) $v;
        return e(mb_strlen($s) > 300 ? mb_substr($s, 0, 300) . '…' : $s);
    }

    protected function enumValue($model, $key, $v)
    {
        $map = null;
        if ($key == 'status') {
            if ($model == 'booking') $map = [0 => 'Đã xóa', 1 => 'Mới', 2 => 'Hoàn tất', 3 => 'Hủy', 4 => 'Đã tiếp nhận', 5 => 'Đang triển khai'];
            elseif ($model == 'cost') $map = [0 => 'Đã xóa', 1 => 'Chưa thanh toán (Not yet paid)', 2 => 'Đã thanh toán (Paid)', 3 => 'Trả sau (Pay later)'];
            elseif ($model == 'partner_transactions') $map = [0 => 'Đã hủy', 1 => 'Hiệu lực'];
            else $map = [0 => 'Ẩn / đã xóa', 1 => 'Hoạt động'];
        } elseif ($key == 'bill_type') {
            $map = [1 => 'Buffet', 2 => 'Alacarte'];
        } elseif ($key == 'type' && $model == 'partner_transactions') {
            $map = \App\Models\PartnerTransaction::$typeNames;
        } elseif ($key == 'type' && $model == 'partners') {
            $map = \App\Models\Partner::$doiTacTypes;
        } elseif ($key == 'detail_check') {
            $map = [0 => 'Khớp', 1 => 'Lệch tiền – cần kiểm tra'];
        } elseif ($key == 'indian') {
            $map = [1 => 'Có', 2 => 'Không (Not Indian)'];
        }
        if ($map === null) return null;
        $r = $map[$v] ?? $map[(int) $v] ?? null;
        return $r === null ? null : __($r);
    }

    /** Mô tả đối tượng: vd "Món: Butter Chicken – bill #123". Trả về HTML đã escape. */
    public function subject($it, array $old, array $new)
    {
        $row = $new + $old;
        if ($it->model == 'booking_detail') {
            $dishId = $row['dish_id'] ?? ($this->bookingDetailDish[$it->model_id] ?? null);
            $dish = $dishId ? ($this->names['dish'][(int) $dishId] ?? __('Món') . ' #' . $dishId) : __('Món');
            $bill = $row['booking_id'] ?? null;
            return '<b>' . e($dish) . '</b>' . ($bill ? ' – <a href="' . e(route('booking.edit', $bill)) . '" target="_blank">' . e(__('bill')) . ' #' . e($bill) . '</a>' : '');
        }
        if ($it->model == 'cost_detail') {
            $ingId = $row['ingredient_id'] ?? ($this->costDetailIng[$it->model_id] ?? null);
            $ing = $ingId ? ($this->names['ingredient'][(int) $ingId] ?? __('Nguyên liệu') . ' #' . $ingId) : __('Hàng');
            $cost = $row['cost_id'] ?? null;
            return '<b>' . e($ing) . '</b>' . ($cost ? ' – <a href="' . e(route('cost.edit', $cost)) . '" target="_blank">' . e(__('chi phí')) . ' #' . e($cost) . '</a>' : '');
        }
        if ($it->model == 'booking' && !empty($row['table_no'])) return e(__('Bàn')) . ' ' . e($row['table_no']);
        if ($it->model == 'cost' && !empty($row['content'])) return e(mb_substr($row['content'], 0, 60));
        if (!empty($row['name'])) return '<b>' . e($row['name']) . '</b>';
        return '';
    }
}
