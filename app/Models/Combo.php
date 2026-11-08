<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Combo extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'combo';	

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
    protected $fillable = [
        'name',
        'slug',
        'description',
        'content',
        'nights',
        'hotel_id',
        'tour_id',
        'banner_url',
        'image_list',
        'type',        
        'is_hot',
        'city_id',
        'price',
        'meta_title',
        'meta_desc',
        'meta_keywords',
        'status',
        'thumbnail_id',
        'video_id'
    ];

    public function images()
    {
        return $this->hasMany('App\Models\ComboImg', 'combo_id');
    }
    public function thumbnail()
    {
        return $this->belongsTo('App\Models\ComboImg', 'thumbnail_id');
    }
    public function rooms()
    {
        return $this->hasMany('App\Models\Rooms', 'hotel_id')->orderBy('display_order');
    }
    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotels', 'hotel_id');
    }
}
