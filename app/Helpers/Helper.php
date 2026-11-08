<?php
namespace App\Helpers;
use App\Helpers\simple_html_dom;
use App\Models\City;
use App\Models\Booking;
use App\Models\Partner;
use App\Models\Collecter;
use App\Models\Cost;
use App\Models\Hdv;
use DB, Image, Auth;
use App\User;
class Helper
{
    public static $privateKey = 'enilnohngnaoh';

    public static function mahoa($action, $string) {
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $secret_key = 'plan';
        $secret_iv = 'lux';
        // hash
        $key = hash('sha256', $secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        if ( $action == 'mahoa' ) {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else if( $action == 'giaima' ) {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }
    public static function calTourPrice($tour_id, $tour_type, $level, $adults, $childs, $use_date, $cano_type = null){
        $result = [];
        $query = TourPrice::where([
            'tour_id' => $tour_id,
            'tour_type' => $tour_type,
            'level' => $level
        ]);
        $totalAdults = $adults;
        if($tour_type == 1 || $tour_type == 3){
            $totalAdults = floor($adults + $childs/2);
        }
        
        $query->where('from_date','<=', $use_date);
        $query->where('to_date','>=', $use_date);
        $query->where('from_adult','<=', $totalAdults);
        $query->where('to_adult','>=', $totalAdults);
        if($cano_type){
            $query->where('cano_type', $cano_type);
        }
        $rs = $query->first();
        if($rs){
            $result = $rs->toArray();
        }
        return $result;
    }
    public static function getListHdv($user_id){
        return Hdv::where(['status' => 1, 'user_id' => $user_id])->get();
    }
    public static function randCode($code){
        $code1 = substr($code, 0, 3);
        $code2 = substr($code, 3);

      
      $characters = 'ASDFGHJKLQWERTYUIOPZXCVBNMqwertyuiopasdfghjklzxcvbnm';
      $randstring1 = $randstring2 = $randstring3 = '';
      for ($i = 0; $i < 3; $i++) {
          $randstring1 .= $characters[rand(0, 48)];
      }
      for ($i = 0; $i < 3; $i++) {
          $randstring2 .= $characters[rand(0, 48)];
      }
      for ($i = 0; $i < 3; $i++) {
          $randstring3 .= $characters[rand(0, 48)];
      }
      $encode = $randstring1.$code1.$randstring2.$code2.$randstring3;
      return $encode;
    }
    public static function encodeLink($string){
        $returnString = "";
        $charsArray = str_split("e7NjchMCEGgTpsx3mKXbVPiAqn8DLzWo_6.tvwJQ-R0OUrSak954fd2FYyuH~1lIBZ");
        $charsLength = count($charsArray);
        $stringArray = str_split($string);
        $keyArray = str_split(hash('sha256',self::$privateKey));
        $randomKeyArray = array();
        while(count($randomKeyArray) < $charsLength){
            $randomKeyArray[] = $charsArray[rand(0, $charsLength-1)];
        }
        for ($a = 0; $a < count($stringArray); $a++){
            $numeric = ord($stringArray[$a]) + ord($randomKeyArray[$a%$charsLength]);
            $returnString .= $charsArray[floor($numeric/$charsLength)];
            $returnString .= $charsArray[$numeric%$charsLength];
        }
        $randomKeyEnc = '';
        for ($a = 0; $a < $charsLength; $a++){
            $numeric = ord($randomKeyArray[$a]) + ord($keyArray[$a%count($keyArray)]);
            $randomKeyEnc .= $charsArray[floor($numeric/$charsLength)];
            $randomKeyEnc .= $charsArray[$numeric%$charsLength];
        }
        return $randomKeyEnc.hash('sha256',$string).$returnString;
    }
    public static function decodeLink($string){
        $returnString = "";
        $charsArray = str_split("e7NjchMCEGgTpsx3mKXbVPiAqn8DLzWo_6.tvwJQ-R0OUrSak954fd2FYyuH~1lIBZ");
        $charsLength = count($charsArray);
        $keyArray = str_split( hash( 'sha256', self::$privateKey ));
        $stringArray = str_split(substr($string, ( $charsLength * 2 ) + 64));
        $sha256 = substr( $string, ( $charsLength * 2 ), 64);
        $randomKeyArray = str_split( substr( $string, 0, $charsLength*2 ));
        $randomKeyDec = array();
        if(count($randomKeyArray) < 132) return false;
        for ($a = 0; $a < $charsLength*2; $a+=2){
            $numeric = array_search($randomKeyArray[$a],$charsArray) * $charsLength;
            $numeric += array_search($randomKeyArray[$a+1],$charsArray);
            $numeric -= ord($keyArray[floor($a/2)%count($keyArray)]);
            $randomKeyDec[] = chr($numeric);
        }
        for ($a = 0; $a < count($stringArray); $a+=2){
            $numeric = array_search($stringArray[$a],$charsArray) * $charsLength;
            $numeric += array_search($stringArray[$a+1],$charsArray);
            $numeric -= ord($randomKeyDec[floor($a/2)%$charsLength]);
            $returnString .= chr($numeric);
        }
        if(hash('sha256',$returnString) != $sha256){
            return false;
        }else{
            return $returnString;
        }
    }
    public static function calTotalPrice($location_id, $level, $adults, $childs, $cap_nl, $cap_te, $meals, $meals_te){
        $price_cap_nl = 390000;
        $price_cap_te = 255000;
        $price_meals = 200000;
        $price_meals_te = 100000;
        $price_adults = 500000;
        $price_childs = 285000;
        switch ($level) {
            case 1: // sales
                if($adults <= 3){
                    $price_adults = 500000;
                    $price_childs = 285000;
                }elseif($adults >= 4 && $adults <= 7){
                    $price_adults = $price_adults-20000;                    
                }elseif($adults >= 8 && $adults <= 12){
                    $price_adults = $price_adults-40000;                    
                }elseif($adults > 12){
                    $price_adults = $price_adults-80000;                    
                }
                break;
            case 2:
                $price_adults = $price_adults - 100000;
                $price_childs = $price_childs = 1;
            default:
                # code...
                break;
        }
    }
    public static function getUserIdPushNoti($booking_id, $type = 1){
        //$type = 1 normal, type = 2 private for sales
        $detail = Booking::find($booking_id);        
        $role = Auth::user()->role;
        if($type == 1){
            if($role == 1 || $role == 2){ 
                //admin thi push cho dieu hanh, sale, hdv
                $userIdPush = [$detail->dieuhanh_id, $detail->user_id, $detail->hdv_id];
            }elseif($role == 3){
                //dieu hanh thi push cho admin, sale, hdv
                $userIdPush = [1, $detail->user_id, $detail->hdv_id];
            }elseif($role == 4){
                //sales thi push cho admin, dieu hanh, hdv
                $userIdPush = [1, $detail->dieuhanh_id, $detail->hdv_id];
            }elseif($role == 5){
                //sales thi push cho admin, dieu hanh, hdv
                $userIdPush = [1, $detail->dieuhanh_id, $detail->user_id];
            } 
        }else{
            if($role == 1 || $role == 2){ 
                //admin thi push cho dieu hanh, sale, hdv
                $userIdPush = [$detail->user_id];
            }elseif($role == 3){
                //dieu hanh thi push cho admin, sale, hdv
                $userIdPush = [1, $detail->user_id];
            }
        }
        
        return $userIdPush;
    }
    public static function getLevel($level){
        switch ($level) {
            case 1:
                $hh = 'HH 90K';
                break;
            case 2:
                $hh = 'NET 500K';
                break;
            case 3:
                $hh = 'NET 490K';
                break;
            case 4:
                $hh = 'NET 480K';
                break;
            case 5:
                $hh = 'NET 100K';
                break;
            case 6:
                $hh = 'HH 0';
                break;
            case 7:
                $hh = 'NET 450K';
                break;
            default:
                $hh = 0;
                break;
        }
        return $hh;
    }
    public static function parseLog($detailLog){
        //echo $content;
         $str = ''; 
        $detailBooking = Booking::find($detailLog->booking_id);
        if($detailBooking->type == 1){
            $str.='PTT'.$detailLog->booking_id." - ";
        }
        $userDetail = User::find($detailLog->user_id);
        $str.= '<b style="color:#06b7a4">'.$userDetail->name."</b> đã cập nhật vào lúc ".'<b style="color:#06b7a4">'.date('d/m H:i', strtotime($detailLog->created_at)).'</b>';
        $contentArr = json_decode($detailLog->content, true);
        if(isset($contentArr['old'])){
            $oldArr = $contentArr['old'];
            $newArr = $contentArr['new'];          
            
            foreach($newArr as $key => $value){
                switch ($key) {
                    
                    case 'nguoi_thu_tien':
                        if($oldArr[$key] == null){
                            $str .= "<br>Người thu tiền: ";
                            if($value == 1){
                                $str.= '"<b style="color:red">Sales</b>"';
                            }elseif($value == 2){
                                $str.= '"<b style="color:red">CTY</b>"';
                            }elseif($value == 3){
                                $str.= '"<b style="color:red">HDV</b>"';
                            }    
                        }else{
                            $str .= "<br>Người thu tiền từ ";
                            if($oldArr[$key] == 1){
                                $str.= '"Sales"';
                            }elseif($oldArr[$key] == 2){
                                $str.= '"CTY"';
                            }elseif($oldArr[$key] == 3){
                                $str.= '"HDV"';
                            }else{
                                 $str.= '"Chưa chọn"';
                            }
                            $str .= " thành ";
                            if($value == 1){
                                $str.= '"<b style="color:red">Sales</b>"';
                            }elseif($value == 2){
                                $str.= '"<b style="color:red">CTY</b>"';
                            }elseif($value == 3){
                                $str.= '"<b style="color:red">HDV</b>"';
                            }else{
                                 $str.= '"<b style="color:red">Chưa chọn</b>"';
                            }  
                        }
                        
                        break;
                    case 'cap_nl':
                        $str .= "<br>Cáp NL từ "; 
                        $str.= number_format($oldArr[$key]);                    
                        $str .= " thành ";
                        $str .= '"<b style="color:red">'.number_format($value).'</b>"';                        
                        break;
                    case 'cap_te':
                        $str .= "<br>Cáp TE từ "; 
                        $str.= number_format($oldArr[$key]);                    
                        $str .= " thành ";
                        $str .= '"<b style="color:red">'.number_format($value).'</b>"';                        
                        break;
                    case 'meals':
                        $str .= "<br>Phần ăn NL từ "; 
                        $str.= number_format($oldArr[$key]);                    
                        $str .= " thành ";
                        $str .= '"<b style="color:red">'.number_format($value).'</b>"';                        
                        break;
                    case 'meals_te':
                        $str .= "<br>Phần ăn TE từ "; 
                        $str.= number_format($oldArr[$key]);                    
                        $str .= " thành ";
                        $str .= '"<b style="color:red">'.number_format($value).'</b>"';                        
                        break;
                    case 'status':
                        $str .= "<br>Trạng thái từ ";
                        if($oldArr[$key] == 1){
                            $str.= '"Mới"';
                        }elseif($oldArr[$key] == 2){
                            $str.= '"Hoàn tất"';
                        }elseif($oldArr[$key] == 3){
                            $str.= '"Hủy"';
                        }else{
                             $str.= '"Chưa chọn"';
                        }
                        $str .= " thành ";
                        if($value == 1){
                            $str.= '"<b style="color:red">Mới</b>"';
                        }elseif($value == 2){
                            $str.= '"<b style="color:red">Hoàn tất</b>"';
                        }elseif($value == 3){
                            $str.= '"<b style="color:red">Hủy</b>"';
                        }else{
                             $str.= '"<b style="color:red">Chưa chọn</b>"';
                        }
                        break;
                    case 'hoa_hong_sales':
                        $str .= "<br>Hoa hồng sales từ ";                   
                        $str.= number_format($oldArr[$key]);                    
                        $str .= " thành ";
                        $str .= '"<b style="color:red">'.number_format($value).'</b>"';
                        break;
                    case 'hoa_hong_cty':
                        $str .= "<br>Hoa hồng CTY từ ";                   
                        $str.= number_format($oldArr[$key]);                    
                        $str .= " thành ";
                        $str .= '"<b style="color:red">'.number_format($value).'</b>"';
                        break;
                    case 'name':
                        $str .= "<br>Tên khách hàng từ ";                   
                        $str.= '"'.$oldArr[$key].'"';                    
                        $str .= " thành ";
                        $str .= '"<b style="color:red">'.$value.'</b>"';
                        break;
                    case 'phone':
                        $str .= "<br>Số điện thoại từ ";                   
                        $str.= '"'.$oldArr[$key].'"';                    
                        $str .= " thành ";
                        $str .= '"<b style="color:red">'.$value.'</b>"';
                        break;
                    case 'phone_1':
                        $str .= "<br>Số điện thoại 2 từ ";                   
                        $str.= '"'.$oldArr[$key].'"';                    
                        $str .= " thành ";
                        $str .= '"<b style="color:red">'.$value.'</b>"';
                        break;
                    case 'use_date':
                        $str .= "<br>Ngày đi từ ";                   
                        $str.= '"'.$oldArr[$key].'"';                    
                        $str .= " thành ";
                        $str .= '"<b style="color:red">'.$value.'</b>"';
                        break;
                    case 'location_id':
                        if(isset($oldArr[$key]) && $oldArr[$key] > 0){
                            $str .= "<br>Điểm đón từ ";                   
                            $str.= '"'.self::getLocationName($oldArr[$key]).'"';                    
                            $str .= " thành ";
                            $str .= '"<b style="color:red">'.self::getLocationName($value).'</b>"';
                        }else{
                            $str .= "<br>Điểm đón ";
                            $str .= " thành ";
                            $str .= '"<b style="color:red">'.self::getLocationName($value).'</b>"';
                        }
                        
                        break;
                    case 'adults':
                        $str .= "<br>Người lớn từ ";                   
                        $str.= '"'.$oldArr[$key].'"';                    
                        $str .= " thành ";
                        $str .= '"<b style="color:red">'.$value.'</b>"';
                        break;
                    case 'childs':
                        $str .= "<br>Trẻ em từ ";                   
                        $str.= '"'.$oldArr[$key].'"';                    
                        $str .= " thành ";
                        $str .= '"<b style="color:red">'.$value.'</b>"';
                        break;
                    case 'infants':
                        $str .= "<br>Em bé từ ";                   
                        $str.= '"'.$oldArr[$key].'"';                    
                        $str .= " thành ";
                        $str .= '"<b style="color:red">'.$value.'</b>"';
                        break;
                    case 'meals':
                        $str .= "<br>Phần ăn từ ";                   
                        $str.= '"'.$oldArr[$key].'"';                    
                        $str .= " thành ";
                        $str .= '"<b style="color:red">'.$value.'</b>"';
                        break;
                    case 'meals_te':
                        $str .= "<br>Phần ăn trẻ em từ ";                   
                        $str.= '"'.$oldArr[$key].'"';                    
                        $str .= " thành ";
                        $str .= '"<b style="color:red">'.$value.'</b>"';
                        break;
                    case 'total_price_adult':
                        $str .= "<br>Tiền người lớn từ ";                   
                        $str.= number_format($oldArr[$key]);                    
                        $str .= " thành ";
                        $str .= '"<b style="color:red">'.number_format($value).'</b>"';
                    case 'total_price_child':
                        $str .= "<br>Tiền trẻ em từ ";                   
                        $str.= number_format($oldArr[$key]);                    
                        $str .= " thành ";
                        $str .= '"<b style="color:red">'.number_format($value).'</b>"';
                        break;
                    case 'extra_fee':
                        $str .= "<br>Tiền phụ thu từ ";                   
                        $str.= number_format($oldArr[$key]);                    
                        $str .= " thành ";
                        $str .= '"<b style="color:red">'.number_format($value).'</b>"';
                        break;
                    case 'discount':
                        $str .= "<br>Giảm giá từ ";                   
                        $str.= number_format($oldArr[$key]);                    
                        $str .= " thành ";
                        $str .= '"<b style="color:red">'.number_format($value).'</b>"';
                        break;
                    case 'total_price':
                        $str .= "<br>Tổng tiền từ ";                   
                        $str.= number_format($oldArr[$key]);                    
                        $str .= " thành ";
                        $str .= '"<b style="color:red">'.number_format($value).'</b>"';
                        break;
                    case 'tien_coc':
                        $str .= "<br>Tiền cọc từ ";                   
                        $str.= number_format($oldArr[$key]);                    
                        $str .= " thành ";
                        $str .= '"<b style="color:red">'.number_format($value).'</b>"';
                        break;
                    case 'mu_di_bo':
                        $str .= "<br>Mũ đi bộ từ ";                   
                        $str.= number_format($oldArr[$key]);                    
                        $str .= " thành ";
                        $str .= '"<b style="color:red">'.number_format($value).'</b>"';
                        break;
                    case 'nguoi_thu_coc':
                        if($oldArr[$key] == null){
                            $str .= "<br>Người thu cọc: ";
                            if($value == 1){
                                $str.= '"<b style="color:red">Sales</b>"';
                            }elseif($value == 2){
                                $str.= '"<b style="color:red">CTY</b>"';
                            }   
                        }else{
                            $str .= "<br>Người thu cọc từ ";
                            if($oldArr[$key] == 1){
                                $str.= '"Sales"';
                            }elseif($oldArr[$key] == 2){
                                $str.= '"CTY"';
                            }else{
                                 $str.= '"Chưa chọn"';
                            }
                            $str .= " thành ";
                            if($value == 1){
                                $str.= '"<b style="color:red">Sales</b>"';
                            }elseif($value == 2){
                                $str.= '"<b style="color:red">CTY</b>"';
                            }else{
                                 $str.= '"<b style="color:red">Chưa chọn</b>"';
                            }  
                        }
                        
                        break;
                    case 'con_lai':
                        $str .= "<br>Còn lại từ ";                   
                        $str.= number_format($oldArr[$key]);                    
                        $str .= " thành ";
                        $str .= '"<b style="color:red">'.number_format($value).'</b>"';
                        break;
                    case 'hdv_thu':
                        $str .= "<br>HDV thu từ ";                   
                        $str.= number_format($oldArr[$key]);                    
                        $str .= " thành ";
                        $str .= '"<b style="color:red">'.number_format($value).'</b>"';
                        break;
                    case 'hdv_notes':
                        $str .= "<br>HDV ghi chú từ ";                   
                        $str.= $oldArr[$key];                    
                        $str .= " thành ";
                        $str .= '"<b style="color:red">'.$value.'</b>"';
                        break;
                    case 'danh_sach':
                        if(!is_null($oldArr[$key])){
                            $str .= "<br>Danh sách từ ";          
                        }else{
                            $str .= "<br>Danh sách ";    
                        }
                                 
                        $str.= $oldArr[$key];                    
                        $str .= " thành ";
                        $str .= '"<b style="color:red">'.$value.'</b>"';
                        break;
                    case 'notes':
                        if(!is_null($oldArr[$key])){
                            $str .= "<br>Ghi chú từ ";            
                        }else{
                            $str .= "<br>Ghi chú ";     
                        }
                                        
                        $str.= $oldArr[$key];                    
                        $str .= " thành ";
                        $str .= '"<b style="color:red">'.$value.'</b>"';
                        break;
                    case 'cano_id':
                        $str .= "<br>Cano từ ";    
                        $cano_id_old = $oldArr[$key]; 
                        if($cano_id_old > 0){              
                            $str.= Partner::find($cano_id_old)->name;            
                        }
                        $str .= " thành ";
                        $str .= '"<b style="color:red">'.Partner::find($value)->name.'</b>"';
                        break;
                    default:
                        # code...
                        break;
                }
                //var_dump($str);
            }           
            return $str;

        }else{ // tao moi

           return '<b style="color:#06b7a4">'.$userDetail->name."</b> đã tạo booking vào lúc ".'<b style="color:#06b7a4">'.date('d/m H:i', strtotime($detailLog->created_at)).'</b>';
        }
        
    }
    public static function getLocationName($location_id){
        return Location::find($location_id)->name;
    }
    public static function shout(string $string)
    {
        return strtoupper($string);
    }
    public static function accountAvai(){        
        if( Auth::user()->status == 2 ){
            echo "Tài khoản đã bị khóa. ";die();
        }
    }
    public static function getChild($table, $column, $parent_id){
        $listData = DB::table($table)->where($column, $parent_id)->get();
        
            echo '<option value="">--chọn--</option>';
        
        if(!empty(  (array) $listData  )){
            
            foreach($listData as $data){
                echo "<option value=".$data->id.">".$data->name."</option>";
            }
        }
    }
    public static function view($object_id, $object_type){
        $rs = CounterValues::where(['object_id' => $object_id, 'object_type' => $object_type])->first();
        if($rs){
            return $rs->all_value;
        }else{
            return 0;
        }
    }
    public static function counter( $object_id, $object_type){
        // ip-protection in seconds
        $counter_expire = 600;

        // ignore agent list
        $counter_ignore_agents = array('bot', 'bot1', 'bot3');

        // ignore ip list
        //$counter_ignore_ips = array('127.0.0.2', '127.0.0.3');
        $counter_ignore_ips = [];
        // get basic information
        $counter_agent = $_SERVER['HTTP_USER_AGENT'];
        $counter_ip = $_SERVER['REMOTE_ADDR']; 
        $counter_time = time();

        $ignore = false; 
           
        // get counter information   
        $rs1 = CounterValues::where(['object_id' => $object_id, 'object_type' => $object_type])->first();   

        // fill when empty
        if (!$rs1)
        {   

            $tmpArr = [
                'object_id' => $object_id,
                'object_type' => $object_type,
                'day_id' => date("z"),
                'day_value' => 1,
                'all_value' => 1
            ];
          CounterValues::create($tmpArr);
          $rs1 = CounterValues::where(['object_id' => $object_id, 'object_type' => $object_type])->first();
          
          $ignore = true;
        }   
        
        $day_id = $rs1->day_id;
        $day_value = $rs1->day_value;
        $all_value = $rs1->all_value;
        // check ignore lists
        $length = sizeof($counter_ignore_agents);
        for ($i = 0; $i < $length; $i++)
        {
          if (substr_count($counter_agent, strtolower($counter_ignore_agents[$i])))
          {
             $ignore = true;
             break;
          }
        }

        $length = sizeof($counter_ignore_ips);
        for ($i = 0; $i < $length; $i++)
        {
          if ($counter_ip == $counter_ignore_ips[$i])
          {
             $ignore = true;
             break;
          }
        }

        
        // delete free ips
        if ($ignore == false)
        {           
            $time = time();
            CounterIps::where(['object_id' =>$object_id, 'object_type' => $object_type, 'ip' => $counter_ip])->whereRaw("$time-visit >= $counter_expire")->delete();
        }
 
        // check for entry
        if ($ignore == false)
        {
            $rs2 = CounterIps::where(['ip' => $counter_ip, 'object_id' => $object_id, 'object_type' => $object_type])->get();
          
          if ( $rs2->count() > 0)
          {
            $modelCouterIps = CounterIps::where('ip', $counter_ip)->where(['object_id' => $object_id, 'object_type' => $object_type]);
            $modelCouterIps->update(['visit' => time()]);   
            $ignore = true;          
          }
          else
          {
             // insert ip
             CounterIps::create(['ip' => $counter_ip, 'visit' => time(), 'object_id' => $object_id, 'object_type' => $object_type]);
          }       
        }
        // add counter
        if ($ignore == false)
        {
          // day
          if ($day_id == date("z")) 
          {
             $day_value++; 
          }
          else 
          {
             $day_value = 1;
             $day_id = date("z");
          }
          // all
          $all_value++; 

        $modelCouterValues = CounterValues::where(['object_id' => $object_id, 'object_type' => $object_type]);
        $modelCouterValues->update([
                'day_id' => $day_id,
                'day_value' => $day_value,
                'all_value' => $all_value
        ]);
         
        }
    }
    public static function xml_entities($string) {
        return str_replace(
                array("&", "<", ">", '"', "'"), array("&amp;", "&lt;", "&gt;", "&quot;", "&apos;"), $string
        );
    }
    public static function getNextOrder($table, $where = []){
        return DB::table($table)->where($where)->max('display_order') + 1;
    }
    
    public static function showImage($image_url, $type = 'original'){

        //return strpos($image_url, 'http') === false ? config('study.upload_url') . $type . '/' . $image_url : $image_url;        
        return strpos($image_url, 'http') === false ? env('APP_URL') . $image_url : $image_url;        

    }
    public static function showImageNew($image_url, $type = 'original'){

        //return strpos($image_url, 'http') === false ? config('study.upload_url') . $type . '/' . $image_url : $image_url;        
        return strpos($image_url, 'http') === false ? env('APP_URL') .'/uploads/'. $image_url : $image_url;        

    }
    public static function showImageThumb($image_url, $object_type = 1, $folder = ''){             
        // type = 1 : original 2 : thumbs
        //object_type = 1 : product, 2 :article  3: project          
        $tmpArrImg = explode('/', $image_url);
                        
        $image_url = config('plan.upload_url_thumbs').end($tmpArrImg);           
        if(strpos($image_url, 'http') === false){
            if($object_type == 1){
                return env('APP_URL') . $folder. $image_url;
            }elseif($object_type == 2){
                return env('APP_URL') . $folder. $image_url;
            }else{
                return env('APP_URL') . $folder. $image_url;
            }    
        }else{
            return $image_url;
        }
        
    }
    public static function seo(){
        $seo = [];
        $arrTmpSeo = DB::table('info_seo')->get();
        $arrSeo = $arrUrl = [];
        foreach($arrTmpSeo as $tmpSeo){
          $arrSeo[$tmpSeo->url] = ['title' => $tmpSeo->title, 'description' => $tmpSeo->description, 'keywords' => $tmpSeo->keywords, 'image_url' => $tmpSeo->image_url];
          $arrUrl[] = $tmpSeo->url;

        }
        if(in_array(url()->current(), $arrUrl)){
          $seo = $arrSeo[url()->current()];
        }
        if(empty($seo)){
          $seo['title'] = $seo['description'] = $seo['keywords'] = "Trang chủ NhaDat";
        }      
        return $seo;
    }
    
    public static function getName( $id, $table){
        $rs = DB::table($table)->where('id', $id)->first();

        return $rs ? $rs->name : "";
    }
    public static function calDayDelivery( $city_id ){
        
        $tmp = City::find($city_id);

        $region_id = $tmp->region_id;        
        $endDay = $region_id == 1 ? time() + 8*3600*24 : time() + 9*3600*24;
        $arrDate = self::createDateRangeArray(date('Y-m-d'), date('Y-m-d', $endDay));        
        return $arrDate;
    }

      

    public static function uploadPhoto($file, $base_folder = '', $date_dir=false){
    
        $return = [];

        $basePath = '';

        $basePath = $base_folder ? $basePath .= $base_folder ."/" : $basePath = $basePath;

        $basePath = $date_dir == true ? $basePath .= date('Y/m/d'). '/'  : $basePath = $basePath;        
        
        $desPath = config('plantotravel.upload_path'). $basePath;
       
        $desThumbsPath = config('plantotravel.upload_thumbs_path'). $basePath;
        //set name for file
        $fileName = $file->getClientOriginalName();
        
        $tmpArr = explode('.', $fileName);

        // Get image extension
        $imgExt = array_pop($tmpArr);

        // Get image name exclude extension
        $imgNameOrigin = preg_replace('/(.*)(_\d+x\d+)/', '$1', implode('.', $tmpArr));        

        $imgName = str_slug($imgNameOrigin, '-');
        
        $imgName = $imgName."-".time();

        $newFileName = "{$imgName}.{$imgExt}";
       //var_dump($desPath, $newFileName);die;
        if( $file->move($desPath, $newFileName) ){            
            $imagePath = $basePath.$newFileName;
            $return['image_name'] = $newFileName;
            $return['image_path'] = $imagePath;
        }

        return $return;
    }

    public static function changeFileName($str) {
        $str = self::stripUnicode($str);
        $str = str_replace("?", "", $str);
        $str = str_replace("&", "", $str);
        $str = str_replace("'", "", $str);
        $str = str_replace("  ", " ", $str);
        $str = trim($str);
        $str = mb_convert_case($str, MB_CASE_LOWER, 'utf-8');
        $str = str_replace(" ", "-", $str);
        $str = str_replace("---", "-", $str);
        $str = str_replace("--", "-", $str);
        $str = str_replace('"', '', $str);
        $str = str_replace('"', "", $str);
        $str = str_replace(":", "", $str);
        $str = str_replace("(", "", $str);
        $str = str_replace(")", "", $str);
        $str = str_replace(",", "", $str);
        $str = str_replace(".", "", $str);
        $str = str_replace(".", "", $str);
        $str = str_replace(".", "", $str);
        $str = str_replace("%", "", $str);
        $str = str_replace("“", "", $str);
        $str = str_replace("”", "", $str);
        return $str;
    }

    public static function stripUnicode($str) {
        if (!$str)
            return false;
        $unicode = array(
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ',
            'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ằ|Ẳ|Ẵ|Ặ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'd' => 'đ',
            'D' => 'Đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'E' => 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'I' => 'Í|Ì|Ỉ|Ĩ|Ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'O' => 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
            'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
            '' => '?',
            '-' => '/'
        );
        foreach ($unicode as $khongdau => $codau) {
            $arr = explode("|", $codau);
            $str = str_replace($arr, $khongdau, $str);
        }
        return $str;
    }

    public static function smsParser($dataArr){
        try{
            $content = $dataArr['body'];
            $pat = '/^TK ?(?<so_tk>\d+) GD: ?[\-|\+]?(?<so_tien>[\d,]+)VND (?<thoi_gian>\d{2}\/\d{2}\/\d{2,4} \d{2}:\d{2}) (?<so_du>SD: ?[\d,]+VND) ND: ?(?<noi_dung>.*?)$/i';
            if (preg_match($pat, $content, $sms)) {
                foreach ($sms as $key => $val) {
                    if (is_numeric($key)) {
                        unset($sms[$key]);
                    }
                }
                $amount = str_replace([','], [''], $sms['so_tien']);
                $noiDung = trim($sms['noi_dung']);
                $contentToSave = $content;
                $pay_date = $sms['thoi_gian'];
                isset($sms['so_du']) && ($contentToSave = str_replace($sms['so_du'], 'SD:0VND', $contentToSave));
                $created_at = $updated_at = date('Y-m-d H:i:s', time());
                $account_no = $sms['so_tk'];

                $collecter_id = 5;
                if($account_no == '0938766885'){
                    $collecter_id = 7;
                }elseif($account_no == '0949350752'){
                    $collecter_id = 6;
                }elseif($account_no == '0364503454'){
                    $collecter_id = 12;
                }elseif($account_no == '00901424868'){
                    $collecter_id = 6;
                }elseif($account_no == '0911380111'){
                    $collecter_id = 2;
                }                 

                //Nếu là chi tiền theo mã
                $pat = '/^(?<useless>.*?)PQSP ?(?<code_ids>.*?) [FT|Trace|CT|Tu|Den|-](?<ending>.*?)$/i';
                if (preg_match($pat, $noiDung, $noiDungArr)) {
                    $smsError = false;
                    $bookingIds = $noiDungArr['code_ids'];
                    if(!empty($bookingIds)){
                        $bookingIds = explode(' ', $bookingIds);
                        $remaining = abs(floatval($amount));
                        $runningIndex = 0;
                        $timeChiTien = date("Y/m/d H:i:s");
                        foreach ($bookingIds as $bookingId){                            
                            $booking = Booking::find($bookingId);
                            //Check if booking is exists
                            if(!empty($booking)){
                                try{
                                    echo $booking->id;
                                    
                                    $booking->unc_type = 2;
                                    $booking->status = 2;
                                    $booking->nguoi_thu_tien = $collecter_id;                                    
                                    $booking->sms_thu = $contentToSave;
                                    $booking->save();                                    

                                }  catch (\Exception $ex){
                                    $myfile = fopen("logs.txt", "a") or die("Unable to open file!");
                                    fwrite($myfile, 'Errors: ' . date("Y/m/d H:i:s") . " " .$ex->getMessage()."\n");
                                    fclose($myfile);
                                    return;
                                }
                            }
                            $runningIndex++;
                        }
                    }
                    return true;
                } // //Nếu là chi tiền theo mã
 
                //Nếu là chi tiền theo mã
                $pat = '/^(?<useless>.*?)COST ?(?<code_ids>.*?) [FT|Trace|CT|Tu|Den|-](?<ending>.*?)$/i';                 
            
                if (preg_match($pat, $noiDung, $noiDungArr)) {

                    $smsError = false;
                    $costIds = $noiDungArr['code_ids'];
                    if(!empty($costIds)){
                        $costIds = explode(' ', $costIds);
                       
                        $runningIndex = 0;
                        $timeChiTien = date("Y/m/d H:i:s");
                        foreach ($costIds as $costId){                            
                            $cost = Cost::find($costId);
                            //Check if booking is exists
                            if(!empty($cost)){
                                try{                                   
                                    $cost->thuc_chi = $amount;                                    
                                    $cost->unc_type = 2;
                                    $cost->status = 2;
                                    $cost->nguoi_chi = $collecter_id;
                                    $cost->time_chi_tien = $timeChiTien;
                                    $cost->sms_chi = $contentToSave;
                                    $cost->save();
                                    $arr = $cost->toArray();
                                    
                                    // luu booking_payment
                                    if($cost->booking_id){
                                        $tmpArrBk = explode(',', $cost->booking_id);
                                        foreach($tmpArrBk as $booking_id){                                            
                                            if($booking_id > 0){
                                                $bk = Booking::find($booking_id);
                                                $bk->update(['sms_chi' => $contentToSave]);
                                            }
                                        }

                                    } // end luu booking_payment
                                    dd($arr);
                                }  catch (\Exception $ex){
                                    $myfile = fopen("logs.txt", "a") or die("Unable to open file!");
                                    fwrite($myfile, 'Errors: ' . date("Y/m/d H:i:s") . " " .$ex->getMessage()."\n");
                                    fclose($myfile);
                                    return;
                                }
                            }
                            $runningIndex++;
                        }
                    }
                    return true;
                } // //Nếu là chi tiền theo mã

                return true;
            } else {
                $myfile = fopen("logs.txt", "a") or die("Unable to open file!");
                fwrite($myfile, 'V2 Errors: ' . date("Y/m/d H:i:s") . " Can not parse content "."\n");
                fclose($myfile);
                return false;
            }
        }catch(\Exception $ex){
            $myfile = fopen("logs.txt", "a") or die("Unable to open file!");
            fwrite($myfile, 'Errors: ' . date("Y/m/d H:i:s") . " " .$ex->getMessage()."\n");
            fclose($myfile);
            return;
        }
    }

    public static function multiexplode ($delimiters,$string) {
        $ready = str_replace($delimiters, $delimiters[0], $string);
        $launch = explode($delimiters[0], $ready);
        return  $launch;
    }

    public static function getCollecterNameArr(){
        $arr = [];
        $rs = Collecter::all();
        foreach($rs as $coll){
            $arr[$coll->id] = $coll->name;
        }
        return $arr;
    }

    public static function getVietNamBanks(){
        return [
            [
                "id" => 17,
                "name" => "Ngân hàng TMCP Công thương Việt Nam",
                "code" => "ICB",
                "bin" => "970415",
                "shortName" => "VietinBank",
                "logo" => "https://api.vietqr.io/img/ICB.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "VietinBank",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "ICBVVNVX"
            ],
            [
                "id" => 43,
                "name" => "Ngân hàng TMCP Ngoại Thương Việt Nam",
                "code" => "VCB",
                "bin" => "970436",
                "shortName" => "Vietcombank",
                "logo" => "https://api.vietqr.io/img/VCB.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "Vietcombank",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "BFTVVNVX"
            ],
            [
                "id" => 4,
                "name" => "Ngân hàng TMCP Đầu tư và Phát triển Việt Nam",
                "code" => "BIDV",
                "bin" => "970418",
                "shortName" => "BIDV",
                "logo" => "https://api.vietqr.io/img/BIDV.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "BIDV",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "BIDVVNVX"
            ],
            [
                "id" => 42,
                "name" => "Ngân hàng Nông nghiệp và Phát triển Nông thôn Việt Nam",
                "code" => "VBA",
                "bin" => "970405",
                "shortName" => "Agribank",
                "logo" => "https://api.vietqr.io/img/VBA.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "Agribank",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "VBAAVNVX"
            ],
            [
                "id" => 26,
                "name" => "Ngân hàng TMCP Phương Đông",
                "code" => "OCB",
                "bin" => "970448",
                "shortName" => "OCB",
                "logo" => "https://api.vietqr.io/img/OCB.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "OCB",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "ORCOVNVX"
            ],
            [
                "id" => 21,
                "name" => "Ngân hàng TMCP Quân đội",
                "code" => "MB",
                "bin" => "970422",
                "shortName" => "MBBank",
                "logo" => "https://api.vietqr.io/img/MB.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "MBBank",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "MSCBVNVX"
            ],
            [
                "id" => 38,
                "name" => "Ngân hàng TMCP Kỹ thương Việt Nam",
                "code" => "TCB",
                "bin" => "970407",
                "shortName" => "Techcombank",
                "logo" => "https://api.vietqr.io/img/TCB.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "Techcombank",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "VTCBVNVX"
            ],
            [
                "id" => 2,
                "name" => "Ngân hàng TMCP Á Châu",
                "code" => "ACB",
                "bin" => "970416",
                "shortName" => "ACB",
                "logo" => "https://api.vietqr.io/img/ACB.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "ACB",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "ASCBVNVX"
            ],
            [
                "id" => 47,
                "name" => "Ngân hàng TMCP Việt Nam Thịnh Vượng",
                "code" => "VPB",
                "bin" => "970432",
                "shortName" => "VPBank",
                "logo" => "https://api.vietqr.io/img/VPB.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "VPBank",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "VPBKVNVX"
            ],
            [
                "id" => 39,
                "name" => "Ngân hàng TMCP Tiên Phong",
                "code" => "TPB",
                "bin" => "970423",
                "shortName" => "TPBank",
                "logo" => "https://api.vietqr.io/img/TPB.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "TPBank",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "TPBVVNVX"
            ],
            [
                "id" => 36,
                "name" => "Ngân hàng TMCP Sài Gòn Thương Tín",
                "code" => "STB",
                "bin" => "970403",
                "shortName" => "Sacombank",
                "logo" => "https://api.vietqr.io/img/STB.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "Sacombank",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "SGTTVNVX"
            ],
            [
                "id" => 12,
                "name" => "Ngân hàng TMCP Phát triển Thành phố Hồ Chí Minh",
                "code" => "HDB",
                "bin" => "970437",
                "shortName" => "HDBank",
                "logo" => "https://api.vietqr.io/img/HDB.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "HDBank",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "HDBCVNVX"
            ],
            [
                "id" => 44,
                "name" => "Ngân hàng TMCP Bản Việt",
                "code" => "VCCB",
                "bin" => "970454",
                "shortName" => "VietCapitalBank",
                "logo" => "https://api.vietqr.io/img/VCCB.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "VietCapitalBank",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "VCBCVNVX"
            ],
            [
                "id" => 31,
                "name" => "Ngân hàng TMCP Sài Gòn",
                "code" => "SCB",
                "bin" => "970429",
                "shortName" => "SCB",
                "logo" => "https://api.vietqr.io/img/SCB.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "SCB",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "SACLVNVX"
            ],
            [
                "id" => 45,
                "name" => "Ngân hàng TMCP Quốc tế Việt Nam",
                "code" => "VIB",
                "bin" => "970441",
                "shortName" => "VIB",
                "logo" => "https://api.vietqr.io/img/VIB.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "VIB",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "VNIBVNVX"
            ],
            [
                "id" => 35,
                "name" => "Ngân hàng TMCP Sài Gòn - Hà Nội",
                "code" => "SHB",
                "bin" => "970443",
                "shortName" => "SHB",
                "logo" => "https://api.vietqr.io/img/SHB.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "SHB",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "SHBAVNVX"
            ],
            [
                "id" => 10,
                "name" => "Ngân hàng TMCP Xuất Nhập khẩu Việt Nam",
                "code" => "EIB",
                "bin" => "970431",
                "shortName" => "Eximbank",
                "logo" => "https://api.vietqr.io/img/EIB.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "Eximbank",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "EBVIVNVX"
            ],
            [
                "id" => 22,
                "name" => "Ngân hàng TMCP Hàng Hải",
                "code" => "MSB",
                "bin" => "970426",
                "shortName" => "MSB",
                "logo" => "https://api.vietqr.io/img/MSB.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "MSB",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "MCOBVNVX"
            ],
            [
                "id" => 53,
                "name" => "TMCP Việt Nam Thịnh Vượng - Ngân hàng số CAKE by VPBank",
                "code" => "CAKE",
                "bin" => "546034",
                "shortName" => "CAKE",
                "logo" => "https://api.vietqr.io/img/CAKE.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "CAKE",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => null
            ],
            [
                "id" => 54,
                "name" => "TMCP Việt Nam Thịnh Vượng - Ngân hàng số Ubank by VPBank",
                "code" => "Ubank",
                "bin" => "546035",
                "shortName" => "Ubank",
                "logo" => "https://api.vietqr.io/img/UBANK.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "Ubank",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => null
            ],
            [
                "id" => 58,
                "name" => "Ngân hàng số Timo by Ban Viet Bank (Timo by Ban Viet Bank)",
                "code" => "TIMO",
                "bin" => "963388",
                "shortName" => "Timo",
                "logo" => "https://vietqr.net/portal-service/resources/icons/TIMO.png",
                "transferSupported" => 1,
                "lookupSupported" => 0,
                "short_name" => "Timo",
                "support" => 0,
                "isTransfer" => 1,
                "swift_code" => null
            ],
            [
                "id" => 57,
                "name" => "Viettel Money",
                "code" => "VTLMONEY",
                "bin" => "971005",
                "shortName" => "ViettelMoney",
                "logo" => "https://api.vietqr.io/img/VIETTELMONEY.png",
                "transferSupported" => 0,
                "lookupSupported" => 1,
                "short_name" => "ViettelMoney",
                "support" => 0,
                "isTransfer" => 0,
                "swift_code" => null
            ],
            [
                "id" => 56,
                "name" => "VNPT Money",
                "code" => "VNPTMONEY",
                "bin" => "971011",
                "shortName" => "VNPTMoney",
                "logo" => "https://api.vietqr.io/img/VNPTMONEY.png",
                "transferSupported" => 0,
                "lookupSupported" => 1,
                "short_name" => "VNPTMoney",
                "support" => 0,
                "isTransfer" => 0,
                "swift_code" => null
            ],
            [
                "id" => 34,
                "name" => "Ngân hàng TMCP Sài Gòn Công Thương",
                "code" => "SGICB",
                "bin" => "970400",
                "shortName" => "SaigonBank",
                "logo" => "https://api.vietqr.io/img/SGICB.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "SaigonBank",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "SBITVNVX"
            ],
            [
                "id" => 3,
                "name" => "Ngân hàng TMCP Bắc Á",
                "code" => "BAB",
                "bin" => "970409",
                "shortName" => "BacABank",
                "logo" => "https://api.vietqr.io/img/BAB.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "BacABank",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "NASCVNVX"
            ],
            [
                "id" => 30,
                "name" => "Ngân hàng TMCP Đại Chúng Việt Nam",
                "code" => "PVCB",
                "bin" => "970412",
                "shortName" => "PVcomBank",
                "logo" => "https://api.vietqr.io/img/PVCB.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "PVcomBank",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "WBVNVNVX"
            ],
            [
                "id" => 27,
                "name" => "Ngân hàng Thương mại TNHH MTV Đại Dương",
                "code" => "Oceanbank",
                "bin" => "970414",
                "shortName" => "Oceanbank",
                "logo" => "https://api.vietqr.io/img/OCEANBANK.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "Oceanbank",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "OCBKUS3M"
            ],
            [
                "id" => 24,
                "name" => "Ngân hàng TMCP Quốc Dân",
                "code" => "NCB",
                "bin" => "970419",
                "shortName" => "NCB",
                "logo" => "https://api.vietqr.io/img/NCB.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "NCB",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "NVBAVNVX"
            ],
            [
                "id" => 37,
                "name" => "Ngân hàng TNHH MTV Shinhan Việt Nam",
                "code" => "SHBVN",
                "bin" => "970424",
                "shortName" => "ShinhanBank",
                "logo" => "https://api.vietqr.io/img/SHBVN.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "ShinhanBank",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "SHBKVNVX"
            ],
            [
                "id" => 1,
                "name" => "Ngân hàng TMCP An Bình",
                "code" => "ABB",
                "bin" => "970425",
                "shortName" => "ABBANK",
                "logo" => "https://api.vietqr.io/img/ABB.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "ABBANK",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "ABBKVNVX"
            ],
            [
                "id" => 41,
                "name" => "Ngân hàng TMCP Việt Á",
                "code" => "VAB",
                "bin" => "970427",
                "shortName" => "VietABank",
                "logo" => "https://api.vietqr.io/img/VAB.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "VietABank",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "VNACVNVX"
            ],
            [
                "id" => 23,
                "name" => "Ngân hàng TMCP Nam Á",
                "code" => "NAB",
                "bin" => "970428",
                "shortName" => "NamABank",
                "logo" => "https://api.vietqr.io/img/NAB.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "NamABank",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "NAMAVNVX"
            ],
            [
                "id" => 29,
                "name" => "Ngân hàng TMCP Xăng dầu Petrolimex",
                "code" => "PGB",
                "bin" => "970430",
                "shortName" => "PGBank",
                "logo" => "https://api.vietqr.io/img/PGB.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "PGBank",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "PGBLVNVX"
            ],
            [
                "id" => 46,
                "name" => "Ngân hàng TMCP Việt Nam Thương Tín",
                "code" => "VIETBANK",
                "bin" => "970433",
                "shortName" => "VietBank",
                "logo" => "https://api.vietqr.io/img/VIETBANK.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "VietBank",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "VNTTVNVX"
            ],
            [
                "id" => 5,
                "name" => "Ngân hàng TMCP Bảo Việt",
                "code" => "BVB",
                "bin" => "970438",
                "shortName" => "BaoVietBank",
                "logo" => "https://api.vietqr.io/img/BVB.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "BaoVietBank",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "BVBVVNVX"
            ],
            [
                "id" => 33,
                "name" => "Ngân hàng TMCP Đông Nam Á",
                "code" => "SEAB",
                "bin" => "970440",
                "shortName" => "SeABank",
                "logo" => "https://api.vietqr.io/img/SEAB.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "SeABank",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "SEAVVNVX"
            ],
            [
                "id" => 52,
                "name" => "Ngân hàng Hợp tác xã Việt Nam",
                "code" => "COOPBANK",
                "bin" => "970446",
                "shortName" => "COOPBANK",
                "logo" => "https://api.vietqr.io/img/COOPBANK.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "COOPBANK",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => null
            ],
            [
                "id" => 20,
                "name" => "Ngân hàng TMCP Bưu Điện Liên Việt",
                "code" => "LPB",
                "bin" => "970449",
                "shortName" => "LienVietPostBank",
                "logo" => "https://api.vietqr.io/img/LPB.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "LienVietPostBank",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "LVBKVNVX"
            ],
            [
                "id" => 19,
                "name" => "Ngân hàng TMCP Kiên Long",
                "code" => "KLB",
                "bin" => "970452",
                "shortName" => "KienLongBank",
                "logo" => "https://api.vietqr.io/img/KLB.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "KienLongBank",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "KLBKVNVX"
            ],
            [
                "id" => 55,
                "name" => "Ngân hàng Đại chúng TNHH Kasikornbank",
                "code" => "KBank",
                "bin" => "668888",
                "shortName" => "KBank",
                "logo" => "https://api.vietqr.io/img/KBANK.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "KBank",
                "support" => 3,
                "isTransfer" => 1,
                "swift_code" => "KASIVNVX"
            ],
            [
                "id" => 48,
                "name" => "Ngân hàng Liên doanh Việt - Nga",
                "code" => "VRB",
                "bin" => "970421",
                "shortName" => "VRB",
                "logo" => "https://api.vietqr.io/img/VRB.png",
                "transferSupported" => 0,
                "lookupSupported" => 1,
                "short_name" => "VRB",
                "support" => 0,
                "isTransfer" => 0,
                "swift_code" => null
            ],
            [
                "id" => 8,
                "name" => "DBS Bank Ltd - Chi nhánh Thành phố Hồ Chí Minh",
                "code" => "DBS",
                "bin" => "796500",
                "shortName" => "DBSBank",
                "logo" => "https://api.vietqr.io/img/DBS.png",
                "transferSupported" => 0,
                "lookupSupported" => 0,
                "short_name" => "DBSBank",
                "support" => 0,
                "isTransfer" => 0,
                "swift_code" => "DBSSVNVX"
            ],
            [
                "id" => 49,
                "name" => "Ngân hàng TNHH MTV Woori Việt Nam",
                "code" => "WVN",
                "bin" => "970457",
                "shortName" => "Woori",
                "logo" => "https://api.vietqr.io/img/WVN.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "Woori",
                "support" => 0,
                "isTransfer" => 1,
                "swift_code" => null
            ],
            [
                "id" => 50,
                "name" => "Ngân hàng Kookmin - Chi nhánh Hà Nội",
                "code" => "KBHN",
                "bin" => "970462",
                "shortName" => "KookminHN",
                "logo" => "https://api.vietqr.io/img/KBHN.png",
                "transferSupported" => 0,
                "lookupSupported" => 0,
                "short_name" => "KookminHN",
                "support" => 0,
                "isTransfer" => 0,
                "swift_code" => null
            ],
            [
                "id" => 51,
                "name" => "Ngân hàng Kookmin - Chi nhánh Thành phố Hồ Chí Minh",
                "code" => "KBHCM",
                "bin" => "970463",
                "shortName" => "KookminHCM",
                "logo" => "https://api.vietqr.io/img/KBHCM.png",
                "transferSupported" => 0,
                "lookupSupported" => 0,
                "short_name" => "KookminHCM",
                "support" => 0,
                "isTransfer" => 0,
                "swift_code" => null
            ],
            [
                "id" => 6,
                "name" => "Ngân hàng Thương mại TNHH MTV Xây dựng Việt Nam",
                "code" => "CBB",
                "bin" => "970444",
                "shortName" => "CBBank",
                "logo" => "https://api.vietqr.io/img/CBB.png",
                "transferSupported" => 0,
                "lookupSupported" => 1,
                "short_name" => "CBBank",
                "support" => 0,
                "isTransfer" => 0,
                "swift_code" => "GTBAVNVX"
            ],
            [
                "id" => 25,
                "name" => "Ngân hàng Nonghyup - Chi nhánh Hà Nội",
                "code" => "NHB HN",
                "bin" => "801011",
                "shortName" => "Nonghyup",
                "logo" => "https://api.vietqr.io/img/NHB.png",
                "transferSupported" => 0,
                "lookupSupported" => 0,
                "short_name" => "Nonghyup",
                "support" => 0,
                "isTransfer" => 0,
                "swift_code" => null
            ],
            [
                "id" => 7,
                "name" => "Ngân hàng TNHH MTV CIMB Việt Nam",
                "code" => "CIMB",
                "bin" => "422589",
                "shortName" => "CIMB",
                "logo" => "https://api.vietqr.io/img/CIMB.png",
                "transferSupported" => 1,
                "lookupSupported" => 1,
                "short_name" => "CIMB",
                "support" => 0,
                "isTransfer" => 1,
                "swift_code" => "CIBBVNVN"
            ],
            [
                "id" => 9,
                "name" => "Ngân hàng TMCP Đông Á",
                "code" => "DOB",
                "bin" => "970406",
                "shortName" => "DongABank",
                "logo" => "https://api.vietqr.io/img/DOB.png",
                "transferSupported" => 0,
                "lookupSupported" => 1,
                "short_name" => "DongABank",
                "support" => 0,
                "isTransfer" => 0,
                "swift_code" => "EACBVNVX"
            ],
            [
                "id" => 11,
                "name" => "Ngân hàng Thương mại TNHH MTV Dầu Khí Toàn Cầu",
                "code" => "GPB",
                "bin" => "970408",
                "shortName" => "GPBank",
                "logo" => "https://api.vietqr.io/img/GPB.png",
                "transferSupported" => 0,
                "lookupSupported" => 1,
                "short_name" => "GPBank",
                "support" => 0,
                "isTransfer" => 0,
                "swift_code" => "GBNKVNVX"
            ],
            [
                "id" => 13,
                "name" => "Ngân hàng TNHH MTV Hong Leong Việt Nam",
                "code" => "HLBVN",
                "bin" => "970442",
                "shortName" => "HongLeong",
                "logo" => "https://api.vietqr.io/img/HLBVN.png",
                "transferSupported" => 0,
                "lookupSupported" => 1,
                "short_name" => "HongLeong",
                "support" => 0,
                "isTransfer" => 0,
                "swift_code" => "HLBBVNVX"
            ],
            [
                "id" => 40,
                "name" => "Ngân hàng United Overseas - Chi nhánh TP. Hồ Chí Minh",
                "code" => "UOB",
                "bin" => "970458",
                "shortName" => "UnitedOverseas",
                "logo" => "https://api.vietqr.io/img/UOB.png",
                "transferSupported" => 0,
                "lookupSupported" => 1,
                "short_name" => "UnitedOverseas",
                "support" => 0,
                "isTransfer" => 0,
                "swift_code" => null
            ],
            [
                "id" => 14,
                "name" => "Ngân hàng TNHH MTV HSBC (Việt Nam)",
                "code" => "HSBC",
                "bin" => "458761",
                "shortName" => "HSBC",
                "logo" => "https://api.vietqr.io/img/HSBC.png",
                "transferSupported" => 0,
                "lookupSupported" => 1,
                "short_name" => "HSBC",
                "support" => 0,
                "isTransfer" => 0,
                "swift_code" => "HSBCVNVX"
            ],
            [
                "id" => 15,
                "name" => "Ngân hàng Công nghiệp Hàn Quốc - Chi nhánh Hà Nội",
                "code" => "IBK - HN",
                "bin" => "970455",
                "shortName" => "IBKHN",
                "logo" => "https://api.vietqr.io/img/IBK.png",
                "transferSupported" => 0,
                "lookupSupported" => 0,
                "short_name" => "IBKHN",
                "support" => 0,
                "isTransfer" => 0,
                "swift_code" => null
            ],
            [
                "id" => 28,
                "name" => "Ngân hàng TNHH MTV Public Việt Nam",
                "code" => "PBVN",
                "bin" => "970439",
                "shortName" => "PublicBank",
                "logo" => "https://api.vietqr.io/img/PBVN.png",
                "transferSupported" => 0,
                "lookupSupported" => 1,
                "short_name" => "PublicBank",
                "support" => 0,
                "isTransfer" => 0,
                "swift_code" => "VIDPVNVX"
            ],
            [
                "id" => 16,
                "name" => "Ngân hàng Công nghiệp Hàn Quốc - Chi nhánh TP. Hồ Chí Minh",
                "code" => "IBK - HCM",
                "bin" => "970456",
                "shortName" => "IBKHCM",
                "logo" => "https://api.vietqr.io/img/IBK.png",
                "transferSupported" => 0,
                "lookupSupported" => 0,
                "short_name" => "IBKHCM",
                "support" => 0,
                "isTransfer" => 0,
                "swift_code" => null
            ],
            [
                "id" => 18,
                "name" => "Ngân hàng TNHH Indovina",
                "code" => "IVB",
                "bin" => "970434",
                "shortName" => "IndovinaBank",
                "logo" => "https://api.vietqr.io/img/IVB.png",
                "transferSupported" => 0,
                "lookupSupported" => 1,
                "short_name" => "IndovinaBank",
                "support" => 0,
                "isTransfer" => 0,
                "swift_code" => null
            ],
            [
                "id" => 32,
                "name" => "Ngân hàng TNHH MTV Standard Chartered Bank Việt Nam",
                "code" => "SCVN",
                "bin" => "970410",
                "shortName" => "StandardChartered",
                "logo" => "https://api.vietqr.io/img/SCVN.png",
                "transferSupported" => 0,
                "lookupSupported" => 1,
                "short_name" => "StandardChartered",
                "support" => 0,
                "isTransfer" => 0,
                "swift_code" => "SCBLVNVX"
            ]
        ];
    }
}