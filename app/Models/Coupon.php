<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Coupon extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'code';

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
    protected $fillable = ['code', 'zalo_id', 'user_id', 'ctv_id', 'shop_id', 'date_use'];
    
    public function shop()
    {
        return $this->belongsTo('App\Models\Shop', 'shop_id');
    }
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}