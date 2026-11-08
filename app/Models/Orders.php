<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Orders extends Model  {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'orders';   

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
    protected $fillable = ['date_use', 'table_no', 'time_in', 'sales_id', 'total_food', 'total_money', 'discount', 'actual_amount', 'status', 'notes', 'percent_discount', 'image_url'];

    public function details()
    {
        return $this->hasMany('App\Models\OrderDetail', 'order_id');
    } 
    
    
}
