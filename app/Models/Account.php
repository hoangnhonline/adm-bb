<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Account extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

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
    protected $fillable = ['name', 'email', 'password', 'status', 'changed_password', 'remember_token', 'role',  'created_user', 'updated_user', 'phone', 'user_type', 'phone', 'code', 'level','city_id','is_staff','birthday','image_url','is_leader', 'is_partner','department_id','date_join','salary', 'status', 'user_id_manage', 'debt_type', 'is_view'];

    public function articles()
    {
        return $this->hasMany('App\Models\WWArticles', 'created_user');
    }
    public function products()
    {
        return $this->hasMany('App\Models\WProduct', 'created_user');
    }

    public function department()
    {
        return $this->belongsTo('App\Models\Department', 'department_id');
    }
    public function userManage()
    {
        return $this->belongsTo('App\Models\Account', 'user_id_manage');
    }
    public function taskDetail()
    {
        return $this->hasMany('App\Models\TaskDetail', 'staff_id');
    }

    public function task()
    {
        return $this->hasMany('App\Models\Task', 'updated_user');
    }

}
