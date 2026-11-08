<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class BookingCustomer extends Model  {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'booking_customer';

     /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 
        'yob', 
        'booking_id',
        'export'
    ];
    public function booking()
    {
        return $this->belongsTo('App\Models\Booking', 'booking_id');
    }
}
