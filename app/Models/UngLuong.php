<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class UngLuong extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'ung_luong';

	 /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    protected $fillable = [ 'total_money', 'status', 'date_use', 'notes', 'nguoi_chi', 'partner_id', 'created_user', 'updated_user'];

    
    public function partner()
    {
        return $this->belongsTo('App\Models\Partner', 'partner_id');
    }
    public function user()
    {
        return $this->belongsTo('App\User', 'created_user');
    }
}