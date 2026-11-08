<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Task extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'task';	

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
    protected $fillable = ['name', 'type', 'department_id', 'status','created_user','updated_user'];

    public function taskDetail()
    {
        return $this->hasMany('App\Models\TaskDetail', 'task_id');
    }
    
    public function department()
    {
        return $this->belongsTo('App\Models\Department', 'department_id');
    }    
    
    public function updatedUser()
    {
        return $this->belongsTo('App\Models\Account', 'updated_user');
    }  
    

}
