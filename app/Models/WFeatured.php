<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class WFeatured extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'w_featured';	

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
    protected $fillable = ['name', 'status', 'display_order', 'description', 'image_url'];

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
    