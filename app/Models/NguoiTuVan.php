<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class NguoiTuVan extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'nguoi_tu_van';	

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
    protected $fillable = ['name', 'display_order', 'status'];
    
}