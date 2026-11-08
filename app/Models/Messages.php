<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Messages extends Model  {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'messages';  

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
                            'content', 
                            'zalo_id', 
                            'status',
                            'ctv_id'
                            ];

  

}
