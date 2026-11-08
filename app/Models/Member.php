<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Member extends Model  {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'member';  

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
    protected $fillable = ['name', 
                            'slug', 
                            'alias',
                            'is_hot', 
                            'status', 
                            'display_order', 
                            'description', 
                            'image_url', 
                            'content', 
                            'meta_id', 
                            'created_user', 
                            'updated_user',
                            ];

  

}
