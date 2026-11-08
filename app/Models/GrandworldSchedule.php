<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class GrandworldSchedule extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'grandworld_schedule';

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
    protected $fillable = ['date_book', 
                            'adults',
                            'childs', 
                            'booking_id', 
                            'status',
                            'camera_id'                            
                            ];
  
    public function booking()
    {
        return $this->belongsTo('App\Models\Booking', 'booking_id');
    }
    public function camera()
    {
        return $this->belongsTo('App\User', 'camera_id');
    }
}