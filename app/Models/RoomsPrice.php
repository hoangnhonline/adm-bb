<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Helper;

class RoomsPrice extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'rooms_price';	

	/**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'hotel_id',
        'room_id',        
        'from_date',
        'to_date',
        'price',
        'price_goc',
        'status',        
        'created_user',
        'updated_user',
    ];
    
    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotels', 'hotel_id');
    }
    public function room()
    {
        return $this->belongsTo('App\Models\Rooms', 'room_id');
    }
    public static function getPriceByDate($room_id, $date){
        $price = 0;
        $rs = self::where('room_id', $room_id)->where('from_date', '<=', $date)->where('to_date', '>=', $date)->first();
        return !$rs ? 0 : $rs->price;

    }
    public static function getPriceFromTo($room_id, $from_date, $to_date){
        $dateArr = Helper::getDateFromRange($from_date, $to_date);
        $priceArr = [];
        foreach($dateArr as $date){
            $priceArr[date('d/m', strtotime($date))] =  self::getPriceByDate($room_id, $date);
        }
        return $priceArr;
    }
    
    
}
