<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class HotelFeatured extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'hotel_featured';	

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
                    'hotel_id', 
                    'featured_id',                     
                ];
    
}
