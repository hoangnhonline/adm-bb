<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class TaskDetail extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'task_detail';

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
    protected $fillable = ['staff_id', 
                            'task_id', 
                            'department_id', 
                            'content',
                            'notes',
                            'content_result',
                            'status',
                            'percent',                           
                            'task_date', 
                            'task_deadline', 
                            ];

    public function staff()
    {
        return $this->belongsTo('App\Models\Account', 'staff_id');
    }
     public function task()
    {
        return $this->belongsTo('App\Models\Task', 'task_id');
    }
    public function department()
    {
        return $this->belongsTo('App\Models\Department', 'department_id');
    }
}
