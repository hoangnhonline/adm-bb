<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Media extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'media';	

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
    protected $fillable = ['link', 'user_id', 'date_photo', 'type', 'tour_id', 'area_id'];
    
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}
