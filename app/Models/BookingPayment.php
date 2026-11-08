<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class BookingPayment extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'booking_payment';

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
                            'amount', 
                            'image_url', 
                            'type',
                            'pay_date',
                            'notes',
                            'status'           
                            ];
    
    public function booking()
    {
        return $this->belongsTo('App\Models\Booking', 'booking_id');
    }   
}
