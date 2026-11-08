<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Menu extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'menu';	

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
    protected $fillable = ['title', 'type', 'url', 'display_order', 'title_attr', 'status', 'menu_id', 'slug', 'object_id', 'parent_id'];
   
}
