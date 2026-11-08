<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class WBanner extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'w_banner';	

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
    protected $fillable = ['image_url', 'ads_url', 'time_start', 'time_end', 'object_id', 'object_type', 'type', 'display_order', 'status', 'created_user', 'updated_user',
    'title', 'description', 'button_text', 'button_link', 'mobile_url'
    ];
    
}
