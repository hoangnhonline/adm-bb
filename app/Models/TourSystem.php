<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class TourSystem extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'tour_system';	

	 /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'city_id', 'display_order', 'status'
    ];
    
}
