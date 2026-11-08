<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class BankInfo extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'bank_info';	

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
    protected $fillable = ['name', 'bank_name', 'bank_no', 'bank_branch', 'status', 'account_name'];
    
}
