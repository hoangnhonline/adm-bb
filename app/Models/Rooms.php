<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\RoomsPrice;

class Rooms extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'rooms';	

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
        'name',
        'description',
        'adults',
        'children',
        'min_stay',
        'is_hot',
        'amenities',
        'display_order',
        'extra_bed',
        'extra_bed_charges',
        'added_on',
        'quantity',
        'status',
        'image_url',
        'breakfast',
        'created_user',
        'updated_user',
    ];
    
    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotels', 'hotel_id');
    }
    public function prices()
    {
        return $this->hasMany('App\Models\RoomsPrice', 'room_id');
    }
    public static function getRoomMinPrice($room_id){
        $rs = RoomsPrice::where('room_id', $room_id)->where('to_date', '>=', date('Y-m-d'))->orderBy('price', 'asc')->first();
        return !$rs ? 0 : $rs->price;
    }

}
