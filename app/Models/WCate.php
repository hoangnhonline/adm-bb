<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class WCate extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'w_cate';	

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
    protected $fillable = ['name', 'slug', 'is_hot', 'status', 'display_order', 'description', 'cate_id', 'created_user', 'updated_user'];

    public function cate()
    {
        return $this->belongsTo('App\Models\WArticlesCate', 'cate_id');
    }
    public function articles()
    {
        return $this->hasMany('App\Models\WArticles', 'child_id');
    }
    public function banners($id)
    {
        return WBanner::where('object_id', $id)->where('object_type', 1)->get()->count();
    }

}
