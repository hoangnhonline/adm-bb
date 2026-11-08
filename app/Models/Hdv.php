<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Hdv extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'hdv';	

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
    protected $fillable = ['name', 'user_id', 'status'];
    public function partner()
    {
        return $this->belongsTo('App\Models\Account', 'user_id');
    }
}
