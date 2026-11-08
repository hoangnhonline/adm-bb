<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Product extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'w_users';

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
    protected $fillable = ['fullname', 'email', 'password', 'status', 'changed_password', 'remember_token', 'role', 'leader_id', 'created_user', 'updated_user', 'display_name'];
    
    public function articles()
    {
        return $this->hasMany('App\Models\WArticles', 'created_user');
    }
    public function products()
    {
        return $this->hasMany('App\Models\Product', 'created_user');
    }
}