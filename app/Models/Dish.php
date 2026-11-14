<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Dish extends Model  {
    use \App\Traits\LogsActivity;


	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'dish';	

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
    protected $fillable = ['name', 'price', 'description', 'category_id', 'display_order', 'status', 'branch_id', 'image_url'];
    
}
