<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class BookingBbcLogs extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'booking_bbc_logs';

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
                            'user_id', 
                            'content', 
                            'action'           
                            ];
    
    public function booking()
    {
        return $this->belongsTo('App\Models\BookingBbc', 'booking_id');
    }   
}
