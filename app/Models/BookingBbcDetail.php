<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class BookingBbcDetail extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'booking_bbc_detail';

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
    protected $fillable = ['booking_id', 
                            'cate_id',                           
                            'amount', 
                            'price', 
                            'total_price'        
                            ];

    public function booking()
    {
        return $this->belongsTo('App\Models\BookingBbc', 'booking_id');
    } 
    public function cate()
    {
        return $this->belongsTo('App\Models\CateBbc', 'cate_id');
    }  
}
