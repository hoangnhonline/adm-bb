<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class CostPayment extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'cost_payment';

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
    protected $fillable = ['cost_id', 
                            'amount', 
                            'type',
                            'image_url',
                            'notes'               
                            ];
    
    public function booking()
    {
        return $this->belongsTo('App\Models\Booking', 'booking_id');
    }   
}
