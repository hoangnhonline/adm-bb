<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class CostType extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'cost_type';

	 /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    protected $fillable = [ 'total_money', 'type', 'status', 'date_use', 'notes', 'image_url', 'content'];

    public function details()
    {
        return $this->hasMany('App\Models\CostDetail', 'cost_id');
    } 
    
}