<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class CateBbc extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'cate_bbc';	

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
    protected $fillable = ['name', 'price', 'duration', 'type', 'display_order', 'status', 'hon_son', 'chup_anh', 'bai_bien', 'short_name', 'is_load'];
    
}
