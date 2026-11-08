<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class CostDetail extends Model  {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'cost_detail';   

     /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['content', 'amount', 'price', 'total', 'notes', 'image_url', 'date_use', 'status'];
    
    
}
