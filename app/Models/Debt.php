<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Debt extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'debt';	

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
    protected $fillable = ['image_url', 'content', 'amount', 'pay_date', 'status', 'city_id'];
    
}
