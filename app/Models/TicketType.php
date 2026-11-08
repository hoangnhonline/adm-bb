<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class TicketType extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'ticket_type';

	 /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    protected $fillable = ['name', 'price', 'type', 'company_id', 'display_order','status', 'display_order','city_id'];
    
}
