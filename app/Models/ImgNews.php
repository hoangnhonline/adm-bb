<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ImgNews extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'pt_hotel_images';	

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
    protected $fillable = [
                    'hotel_id', 
                    'image_url', 
                    'display_order',                    
                    'type',
                    'status'
                ];
    
}