<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserZalo;

class ZaloController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }
    public function zalo(){
        
        $ch = curl_init('https://openapi.zalo.me/v2.0/oa/getfollowers?access_token=D_H0KyiFGoWqy49MpmjCB3xnIbhE5IvOL-yfGFfpJJzL_p8whM117Ws7G1kN8qKrCwWR8x8aPLy_b1yWsmXaMIU8PblN46XyPxbdVlauP01WeWq6mrbq14tN5Y_iA6DLSjC4KlzoUabnx0SrmXy7CcMXHq_93ZP7OPPLFyCBMZnpyLyvr7ak06IAKdtdSG5bIyv_URDk70O9wLbudsGEPIVZTcwM7WLB6er9Dhak2Wu5gKOfgo57KGMZ7rcZ1rjmNgeiQTe3AqmXktDAfq83JHYyT6wPEd9soSesJyqGHIe&data={%22offset%22:50,%22count%22:50}');
        # Setup request to send json via POST.                
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        # Return response instead of printing.
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        # Send request.
        $result = curl_exec($ch);
        
        curl_close($ch);
        $arr = json_decode($result, true);

        if(!empty($arr)){
            foreach($arr['data']['followers'] as $d){
                $user_id = $d['user_id'];
                $ch2 = curl_init('https://openapi.zalo.me/v2.0/oa/getprofile?access_token=D_H0KyiFGoWqy49MpmjCB3xnIbhE5IvOL-yfGFfpJJzL_p8whM117Ws7G1kN8qKrCwWR8x8aPLy_b1yWsmXaMIU8PblN46XyPxbdVlauP01WeWq6mrbq14tN5Y_iA6DLSjC4KlzoUabnx0SrmXy7CcMXHq_93ZP7OPPLFyCBMZnpyLyvr7ak06IAKdtdSG5bIyv_URDk70O9wLbudsGEPIVZTcwM7WLB6er9Dhak2Wu5gKOfgo57KGMZ7rcZ1rjmNgeiQTe3AqmXktDAfq83JHYyT6wPEd9soSesJyqGHIe&data={%22user_id%22:%22'.$user_id.'%22}');
                # Setup request to send json via POST.                
                curl_setopt( $ch2, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                # Return response instead of printing.
                curl_setopt( $ch2, CURLOPT_RETURNTRANSFER, true );
                # Send request.
                $result2 = curl_exec($ch2);
                
                curl_close($ch2);
                $arr2 = json_decode($result2, true);
                echo "<hr>";
                echo $arr2['data']['user_id'].": ".$arr2['data']['display_name'];                
            }
        }
    }
    public function zalo2(){
        
        $ch = curl_init('https://openapi.zalo.me/v2.0/oa/getfollowers?access_token=D_H0KyiFGoWqy49MpmjCB3xnIbhE5IvOL-yfGFfpJJzL_p8whM117Ws7G1kN8qKrCwWR8x8aPLy_b1yWsmXaMIU8PblN46XyPxbdVlauP01WeWq6mrbq14tN5Y_iA6DLSjC4KlzoUabnx0SrmXy7CcMXHq_93ZP7OPPLFyCBMZnpyLyvr7ak06IAKdtdSG5bIyv_URDk70O9wLbudsGEPIVZTcwM7WLB6er9Dhak2Wu5gKOfgo57KGMZ7rcZ1rjmNgeiQTe3AqmXktDAfq83JHYyT6wPEd9soSesJyqGHIe&data={%22offset%22:100,%22count%22:50}');
        # Setup request to send json via POST.                
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        # Return response instead of printing.
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        # Send request.
        $result = curl_exec($ch);
        
        curl_close($ch);
        $arr = json_decode($result, true);
        if(!empty($arr)){
            foreach($arr['data']['followers'] as $d){
                $user_id = $d['user_id'];
                $ch2 = curl_init('https://openapi.zalo.me/v2.0/oa/getprofile?access_token=D_H0KyiFGoWqy49MpmjCB3xnIbhE5IvOL-yfGFfpJJzL_p8whM117Ws7G1kN8qKrCwWR8x8aPLy_b1yWsmXaMIU8PblN46XyPxbdVlauP01WeWq6mrbq14tN5Y_iA6DLSjC4KlzoUabnx0SrmXy7CcMXHq_93ZP7OPPLFyCBMZnpyLyvr7ak06IAKdtdSG5bIyv_URDk70O9wLbudsGEPIVZTcwM7WLB6er9Dhak2Wu5gKOfgo57KGMZ7rcZ1rjmNgeiQTe3AqmXktDAfq83JHYyT6wPEd9soSesJyqGHIe&data={%22user_id%22:%22'.$user_id.'%22}');
                # Setup request to send json via POST.                
                curl_setopt( $ch2, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                # Return response instead of printing.
                curl_setopt( $ch2, CURLOPT_RETURNTRANSFER, true );
                # Send request.
                $result2 = curl_exec($ch2);
                
                curl_close($ch2);
                $arr2 = json_decode($result2, true);
                echo "<hr>";
                echo $arr2['data']['user_id'].": ".$arr2['data']['display_name'];                
            }
        }
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    
    public function index(Request $request)
    {

        $dataArr = $request->all();
        //write_log
        $message = trim($dataArr['message']['text']);        
        $zalo_id = $dataArr['sender']['id'];
        $userZalo = UserZalo::where('zalo_id', $zalo_id)->first();
        $ctv_id = $userZalo->id;
        $myfile = fopen("logs.txt", "a") or die("Unable to open file!");
        fwrite($myfile, '-------------------------------'."\n".$message."\n");   
        fwrite($myfile, 'Zalo ID: '.$zalo_id."\n");   
        fwrite($myfile, 'CTV ID: '.$ctv_id."\n");   
        
        //end write log
        // store data
        try{
            $rsMess = Messages::create([
                'content' => $message,
                'ctv_id' => $ctv_id,
                'status' => 1
            ]);
            $messages_id = $rsMess->id;
            //$s = trim($dataArr['message']['text']);
            $tmpArr = explode(PHP_EOL, $message);
            if(count($tmpArr) == 1){

            }else{

                // xu ly type LOẠI : TOUR
                $str = $tmpArr[0];
                $arrtmp = explode(':', $str);
              
                if(trim($arrtmp[0]) == 'LOẠI'){
                    $strTour = strtoupper(trim($arrtmp[1]));
                    if( $strTour == 'TOUR'){
                        $params['loai'] = 1; // 1:tour, 2:ks, 3:ve                
                    }
                }
               
                // ngay di
                $params = $this->processNgayDi($params, $tmpArr[1]);
                // Ten KH
                $arrtmp = explode(':', $tmpArr[2]);
                if(trim($arrtmp[0]) == 'TÊN KH'){
                    $params['ten_kh'] = trim($arrtmp[1]);
                }
                //dien thoai
                $arrtmp = explode(':', $tmpArr[3]);
                if(trim($arrtmp[0]) == 'ĐIỆN THOẠI'){
                    $params['dien_thoai'] = trim($arrtmp[1]);
                }
                //FB
                $arrtmp = explode(':', $tmpArr[4]);
                if(trim($arrtmp[0]) == 'FB'){
                    $params['fb'] = trim($arrtmp[1]);
                }
                //NƠI ĐÓN
                $arrtmp = explode(':', $tmpArr[5]);
                if(trim($arrtmp[0]) == 'NƠI ĐÓN'){
                    $params['noi_don'] = trim($arrtmp[1]);
                }
               
                //MÃ TOUR
                $arrtmp = explode(':', $tmpArr[6]);
                if(trim($arrtmp[0]) == 'MÃ TOUR'){
                    $params['ma_tour'] = trim($arrtmp[1]);
                }
                
                //NGƯỜI LỚN       
                $arrtmp = explode(':', $tmpArr[7]);
                if(trim($arrtmp[0]) == 'NGƯỜI LỚN'){
                    $tmp = explode('=', trim($arrtmp[1]));
                    $tien_tour_nguoi_lon = trim($tmp[1])."000";
                    $tien_tour_nguoi_lon = str_replace(",", "", $tien_tour_nguoi_lon);
                    $tien_tour_nguoi_lon = str_replace(".", "", $tien_tour_nguoi_lon);
                    $tmp1 = explode('*', trim($tmp[0]));
                    $so_nguoi_lon = $tmp1[0];
                    $gia_tour = str_replace(".", "", $tmp1[1]."000");
                    $gia_tour = str_replace(",", "", $gia_tour);
                    $params['nguoi_lon'] = $so_nguoi_lon;
                    $params['tien_nguoi_lon'] = $tien_tour_nguoi_lon;
                    $params['gia_tour'] = $gia_tour;
                }
                $arrtmp = explode(':', $tmpArr[8]);
                if(trim($arrtmp[0]) == 'TE TRÊN 1M'){
                    $params['tre_em'] = trim($arrtmp[1]);
                }
                $arrtmp = explode(':', $tmpArr[9]);
                if(trim($arrtmp[0]) == 'TE DƯỚI 1M'){
                    $params['em_be'] = trim($arrtmp[1]);
                }
                $arrtmp = explode(':', $tmpArr[10]);
                if(trim($arrtmp[0]) == 'PHỤ THU TE'){          
                    $phu_thu_te = trim($arrtmp[1])."000";
                    $phu_thu_te = str_replace(",", "", $phu_thu_te);
                    $phu_thu_te = str_replace(".", "", $phu_thu_te);
                    $params['tien_tre_em'] = $phu_thu_te;
                }
                // PHỤ THU ĐÓN : 250
                $arrtmp = explode(':', $tmpArr[11]);
                if(trim($arrtmp[0]) == 'PHỤ THU ĐÓN'){          
                    $phu_thu_don = trim($arrtmp[1])."000";
                    $phu_thu_don = str_replace(",", "", $phu_thu_don);
                    $phu_thu_don = str_replace(".", "", $phu_thu_don);
                    $params['dua_don'] = $phu_thu_don;
                }
                // TỔNG TIỀN : 3030
                $arrtmp = explode(':', $tmpArr[12]);
                if(trim($arrtmp[0]) == 'TỔNG TIỀN'){          
                    $tong_tien = trim($arrtmp[1])."000";
                    $tong_tien = str_replace(",", "", $tong_tien);
                    $tong_tien = str_replace(".", "", $tong_tien);
                    $tong_tien_code = $tien_tour_nguoi_lon + $phu_thu_te + $phu_thu_don;
                    if($tong_tien_code == $tong_tien){
                        $params['tong_tien'] = $tong_tien_code;
                    }else{
                        $loiArr[] = "Tính tổng tiền sai.";
                    }                
                }        
                
                // CỌC : 1030
                $arrtmp = explode(':', $tmpArr[13]);
                if(trim($arrtmp[0]) == 'CỌC'){          
                    $coc = trim($arrtmp[1])."000";
                    $coc = str_replace(",", "", $coc);
                    $coc = str_replace(".", "", $coc);
                    $params['tien_coc'] = $coc;
                }
                //  : Gọi trước khi đón 1 tiếng
                $arrtmp = explode(':', $tmpArr[14]);
                if(trim($arrtmp[0]) == 'GHI CHÚ'){
                    $params['ghi_chu'] = trim($arrtmp[1]);
                }
                $params['con_lai'] = $params['tong_tien'] - $params['tien_coc'];
                $params['status'] = 1;
                $params['ctv_id'] = $ctv_id;
                $params['messages_id'] = $messages_id;

                $rs = Booking::create($params);
                $this->replyMess($rs->id, $rs->loai, $zalo_id, $ctv_id);
            }

        }catch(\Exception $ex){
            $myfile = fopen("logs.txt", "a") or die("Unable to open file!");
            fwrite($myfile, 'Errors: '.$ex->getMessage()."\n"); 
            fclose($myfile);
        }
        //$str_test = implode('-', $params);
        //fwrite($myfile, 'Params: '.$str_test."\n"); 
        fclose($myfile); 
       
       
    }
    public function replyMess($booking_id, $booking_type, $zalo_id, $ctv_id){

        $url = 'https://openapi.zalo.me/v2.0/oa/message?access_token=D_H0KyiFGoWqy49MpmjCB3xnIbhE5IvOL-yfGFfpJJzL_p8whM117Ws7G1kN8qKrCwWR8x8aPLy_b1yWsmXaMIU8PblN46XyPxbdVlauP01WeWq6mrbq14tN5Y_iA6DLSjC4KlzoUabnx0SrmXy7CcMXHq_93ZP7OPPLFyCBMZnpyLyvr7ak06IAKdtdSG5bIyv_URDk70O9wLbudsGEPIVZTcwM7WLB6er9Dhak2Wu5gKOfgo57KGMZ7rcZ1rjmNgeiQTe3AqmXktDAfq83JHYyT6wPEd9soSesJyqGHIe';
        $strpad = str_pad($booking_id, 5, '0', STR_PAD_LEFT);

        $booking_code = 'T'.$ctv_id.$strpad;
        $arrData = [
            'recipient' => [
                'user_id' => $zalo_id,
            ], 
            'message' => [
                'text' => 'Đã nhận. Mã booking là '.$booking_code,
            ]
        ];
        $ch = curl_init( $url );
        # Setup request to send json via POST.
        $payload = json_encode( $arrData );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        # Return response instead of printing.
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        # Send request.
        $result = curl_exec($ch);
        curl_close($ch);


        $arrData = [
            'recipient' => [
                'user_id' => '7317386031055599346',
            ], 
            'message' => [
                'text' => 'Đã nhận. Mã booking là '.$booking_code,
            ]
        ];
        $ch = curl_init( $url );
        # Setup request to send json via POST.
        $payload = json_encode( $arrData );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        # Return response instead of printing.
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        # Send request.
        $result = curl_exec($ch);
        curl_close($ch);
        # Print response.
        echo "<pre>$result</pre>";

    }
    public function processNgayDi($params, $str){       
        $arrtmp = explode(':', $str);
        if(trim($arrtmp[0]) == 'NGÀY ĐI'){
            $tem = explode('/', trim($arrtmp[1]));
            
            $day = str_pad($tem[0], 2, "0", STR_PAD_LEFT);
            $month = str_pad($tem[1], 2, "0", STR_PAD_LEFT);
            if($month < date('m')){
                $year = date('Y') + 1;
            }else{
                $year = date('Y');
            }
            $params['ngay_di'] = $year."-".$month."-".$day;
            
        }
        return $params;
    }
    public function test(Request $request){
        $url = 'https://openapi.zalo.me/v2.0/oa/message?access_token=D_H0KyiFGoWqy49MpmjCB3xnIbhE5IvOL-yfGFfpJJzL_p8whM117Ws7G1kN8qKrCwWR8x8aPLy_b1yWsmXaMIU8PblN46XyPxbdVlauP01WeWq6mrbq14tN5Y_iA6DLSjC4KlzoUabnx0SrmXy7CcMXHq_93ZP7OPPLFyCBMZnpyLyvr7ak06IAKdtdSG5bIyv_URDk70O9wLbudsGEPIVZTcwM7WLB6er9Dhak2Wu5gKOfgo57KGMZ7rcZ1rjmNgeiQTe3AqmXktDAfq83JHYyT6wPEd9soSesJyqGHIe';
        $strpad = str_pad('1', 5, '0', STR_PAD_LEFT);
        $booking_code = 'T2'.$strpad;        
        $arrData = [
            'recipient' => [
                'user_id' => '7317386031055599346',
            ], 
            'message' => [
                'text' => 'Đã nhận. Mã booking là '.$booking_code,
            ],
        ];
        $ch = curl_init( $url );
        # Setup request to send json via POST.
        $payload = json_encode( $arrData );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        # Return response instead of printing.
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        # Send request.
        $result = curl_exec($ch);
        curl_close($ch);
        # Print response.
        echo "<pre>$result</pre>";
    }
    public function callback(Request $request)
    {
        $dataArr = $request->all();
        $s = json_encode($dataArr);
        $myfile = fopen("logs.txt", "a") or die("Unable to open file!");
       // $txt = $s."-".$callback_sign."\n";
        $txt = date('d/m H:i:s').$s."\n";
        fwrite($myfile, $txt);
        fclose($myfile);
    }
    public function download()
    {
        $detailShop = Shop::where('user_id', Auth::user()->id)->first();  
        $contents = [];
        $query = Orders::where('shop_id', $detailShop->id)->orderBy('updated_at', 'desc')->get();
        $i = 0;
         $contents[] = [
            'STT' => 'STT',
            'Họ tên' => 'Họ tên',
            'Điện thoại' => 'Điện thoại',
            'Địa chỉ' => 'Địa chỉ',
            'Email' => 'Email',
            'Tổng sản phẩm' => 'Tổng sản phẩm',
            'Tổng tiền' => 'Tổng tiền',
            'Sản phẩm' => 'Sản phẩm',
            'Thời gian đặt' => 'Thời gian đặt'
        ];
        foreach ($query as $item) {
            
            $str = '';
            foreach($item->details as $pro){
                $str.= '-'.$pro->name.' ('.$pro->amount.' x '.number_format($pro->price).')' ."\n";
            }
            if($item->name){
                $i++;
                $contents[] = [
                    'STT' => $i,
                    'Thông tin khách' => $item->name,
                    'Điện thoại' => $item->phone,
                    'Địa chỉ' => $item->address,
                    'Email' => $item->email,
                    'Tổng sản phẩm' => $item->total_product,
                    'Tổng tiền' => number_format($item->total_amount),
                    'Sản phẩm' => $str,
                    'Thời gian đặt' => date('d-m-Y H:i', strtotime($item->created_at))
                ];
            }
            
        }                

        Excel::create('donhang_' . date('YmdHi'), function ($excel) use ($contents) {
            // Set sheets
            $excel->getDefaultStyle()
            ->getAlignment()
            ->applyFromArray(array(
                'horizontal'    => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical'      => \PHPExcel_Style_Alignment::VERTICAL_TOP,
                'wrap'      => TRUE
            ));
            $excel->sheet('Sheet1', function ($sheet) use ($contents) {
                $sheet->fromArray($contents, null, 'A1', false, false);
            });
        })->export('xls');
    }
    public function storeShop(Request $request){
        $dataArr = $request->all();
        $subdomain = str_slug($dataArr['name'], '');
        $rs = Shop::where('subdomain', $subdomain)->get();
        if($rs->count() > 0){
            $subdomain = $subdomain.($rs->count()+1);
        }
        $this->validate($request,[   
            'name' => 'required',    
            'logo_url' => 'required',       
            'google_sheet_id' => 'required',                       
            'flow_id' => 'required',
        ],
        [   
            'name.required' => 'Bạn chưa nhập tên shop',            
            'logo_url.required' => 'Bạn chưa nhập đường dẫn logo',
            'google_sheet_id.required' => 'Bạn chưa nhập Google sheet ID',
            'flow_id.required' => 'Bạn chưa nhập Flow ID'
        ]);
        $dataArr['subdomain'] = $subdomain;
        $dataArr['user_id'] = Auth::user()->id;
        $dataArr['is_email'] = $is_email = $request->is_email ? 1 : 0;
        $dataArr['is_color'] = $is_color = $request->is_color ? 1 : 0;

        if($dataArr['id'] > 0){
            unset($dataArr['subdomain']);
            $rs = Shop::find($dataArr['id']);
            $rs->update($dataArr);
            Session::flash('message', 'Thiết lập thành công');
        }else{
            Session::flash('message', 'Cập nhật thành công');
            $rs = Shop::create($dataArr);    
        }
        $shop_id = $rs->id; 
        // delete sp cu
        $allP = Product::where('shop_id', $shop_id)->get();
        foreach($allP as $pro){
            ProductColorSize::where('product_id', $pro->id)->delete();
            $pro->delete();
        }
        Product::where('shop_id', $shop_id)->delete();
        //end delete sp cu
        $user_id = Auth::user()->id;       
        $sheets = Sheets::spreadsheet($dataArr['google_sheet_id'])
                    ->sheet('Sheet1')
                    ->get();

        $header = $sheets->pull(0);
        $rows = Sheets::sheet('Sheet1')->get();
        $i = 0;
        $dataArr = [];
        foreach($rows as $row){            
            $i++;
            if($i > 1 && !empty($row) && isset($row[5])){   

                //process mau sac
                $color_size_arr = [];
                if(isset($row[8]) && $is_color == 1){
                    $tmpArrColor = explode(';', $row[8]);
               
                    if(!empty($tmpArrColor)){

                        foreach($tmpArrColor as $colorSize){
                            $tmpArrColorSize = explode(':', $colorSize);
                            if(!empty($tmpArrColorSize)){
                                $colorName = trim($tmpArrColorSize[0]);
                                $slugName = str_slug($colorName);
                                //check mau da ton tai hay chua
                                $rsMau = Color::where('slug', $slugName)->first();
                                if($rsMau){
                                    $color_id = $rsMau->id;
                                }else{
                                    $rsMau = Color::create(['name' => $colorName, 'slug' => $slugName]);
                                    $color_id = $rsMau->id;
                                }

                                $tmpSizeArr = explode(',', $tmpArrColorSize[1]);
                                $arrSize = [];
                                if(!empty($tmpSizeArr)){
                                    foreach($tmpSizeArr as $size){
                                        $sizeName = trim($size);
                                        $slugSize = str_slug($sizeName);
                                        $rsSize = Size::where('slug', $slugSize)->first();
                                        if($rsSize){
                                            $size_id = $rsSize->id;
                                        }else{
                                            $rsSize = Size::create(['name' => $sizeName, 'slug' => $slugSize]);
                                            $size_id = $rsSize->id;
                                        }
                                        $arrSize[] = $size_id;
                                    }
                                }

                                // add
                                $color_size_arr[$color_id] = $arrSize;

                            }
                        }
                    }
                }
                
                //end process mau sac

                if($row[3] > $row[4]){
                    $price = ((int)$row[3])*1000;
                    $price_sale = ((int)$row[4])*1000;
                }else{
                    $price_sale = ((int)$row[3])*1000;
                    $price = ((int)$row[4])*1000;
                }
                $price_sale = $price_sale == 0 ? $price : $price_sale;
                $price = $price == 0 ? $price_sale : $price;
                $product['id'] = $row[0];
                $product['name'] = $row[1];
                $product['detail'] = $row[2];                    
                $product['price'] = $price;
                $product['price_sale'] = $price_sale;
                $product['img1'] = $row[5];
                $product['img2'] = $row[6];
                $product['img3'] = $row[7];
                $product['user_id'] = $user_id;
                $product['shop_id'] = $shop_id;
                
                $rs = Product::create($product);
                $product_id = $rs->id;    
                if(!empty($color_size_arr)){
                    foreach($color_size_arr as $color_id => $sizeArr ){
                        foreach($sizeArr as $size_id){
                            ProductColorSize::create(
                                [
                                    'product_id' => $product_id,
                                    'color_id' => $color_id,
                                    'size_id' => $size_id
                                ]
                            );
                        }
                    }
                }
            }            
        }

        return redirect()->route('home');
    }
    public function mysql_escape($inp)
    { 
        if(is_array($inp)) return array_map(__METHOD__, $inp);

        if(!empty($inp) && is_string($inp)) { 
            return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp); 
        } 

        return $inp; 
    }
}
