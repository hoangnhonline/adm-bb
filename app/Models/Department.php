<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Department extends Model  {

  /**
   * The database table used by the model.
   *
   * @var string
   */
  protected $table = 'department';

   /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

      'name',
      'display_order',
      'status'

    ];


    public function staff()
    {
        return $this->hasMany('App\Models\Account', 'department_id');
    }

}
