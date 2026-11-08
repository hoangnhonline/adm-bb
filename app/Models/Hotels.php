<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Hotels extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'hotels';	

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
        'name',
        'slug',
        'description',
        'stars',
        'hotel_type',
        'city_id',
        'latitude',
        'longitude',
        'amenities',
        'banner_url',
        'payment_opt',
        'is_hot',
        'check_in',
        'check_out',
        'policy',
        'surcharge',
        'status',
        'display_order',
        'related',
        'comm_fixed',
        'comm_percentage',
        'tax_fixed',
        'tax_percentage',
        'email',
        'phone',
        'website',
        'refundable',
        'arrivalpay',
        'tripadvisor_id',
        'thumbnail_image',
        'thumbnail_id',
        'near',
        'diem_noi_bat',
        'meta_title',
        'meta_keywords',
        'meta_desc',
        'created_user',
        'updated_user',
        'created_at',
        'updated_at',
        'com_type',
        'com_value',
        'lowest_price',
        'partner',
        'related_id',
        'title_mail'
    ];

    public function images()
    {
        return $this->hasMany('App\Models\HotelImg', 'hotel_id');
    }
    public function thumbnail()
    {
        return $this->belongsTo('App\Models\HotelImg', 'thumbnail_id');
    }
    public function rooms()
    {
        return $this->hasMany('App\Models\Rooms', 'hotel_id')->orderBy('display_order');
    }
    public static function getHotelMinPrice($hotel_id){
        $rs = RoomsPrice::where('hotel_id', $hotel_id)->where('to_date', '>=', date('Y-m-d'))->orderBy('price', 'asc')->first();
        return !$rs ? 0 : $rs->price;
    }
    public static function getHotelMinPriceGoc($hotel_id){
        $rs = RoomsPrice::where('hotel_id', $hotel_id)->where('to_date', '>=', date('Y-m-d'))->orderBy('price', 'asc')->first();
        return !$rs ? 0 : $rs->price;
    }
    public function type()
    {
        return $this->belongsTo('App\Models\HotelsTypesSettings', 'hotel_type');
    }
}
