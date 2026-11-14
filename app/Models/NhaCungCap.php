<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class NhaCungCap extends Model  {
    use \App\Traits\LogsActivity;


	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'nha_cung_cap';	

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
    protected $fillable = ['name', 'group_name', 'phone', 'address', 'contact_name', 'notes', 'display_order', 'status'];
    
}
