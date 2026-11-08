<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class TourDnPrice extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'tour_dn_price';	

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
    protected $fillable = ['partner_id', 'price_adult', 'price_child', 'price_infant','status','created_at','updated_at','created_user','updated_user'
    ];
    
}
