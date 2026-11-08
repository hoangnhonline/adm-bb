<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class JetskiHistory extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'jetski_history';	

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
    protected $fillable = ['jetski_id', 'no_minutes', 'status', 'time_start', 'time_end', 'use_date'];
    
}
