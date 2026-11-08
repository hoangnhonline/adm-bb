<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class BookingBill extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'booking_bill';

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
    protected $fillable = ['booking_id', 
                            'driver_id',
                            'shop_id',
                            'amount', 
                            'image_url', 
                            'type',
                            'pay_date',
                            'notes'               
                            ];
    
    public function booking()
    {
        return $this->belongsTo('App\Models\Booking', 'booking_id');
    }   
}
