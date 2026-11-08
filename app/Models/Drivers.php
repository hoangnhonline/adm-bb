<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Drivers extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'drivers';

	 /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;


    protected $fillable = [      
      'name',
      'car_cate_id',
      'phone',
      'join_date',     
      'status',
      'cmnd',
      'image_url',
      'image_car',
      'city_id'
    ];
    public function bill()
    {
        return $this->hasMany('App\Models\BookingBill', 'driver_id');
    }
    public function booking()
    {
        return $this->hasMany('App\Models\Booking', 'driver_id');
    }
}	