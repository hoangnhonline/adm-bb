<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Featured extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'featured';

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
    protected $fillable = [
                            'name',                            
                            'status', 
                            'display_order'
                        ];
                            
}
