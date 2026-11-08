<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class PaymentRequest extends Model  {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'payment_request';   

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
    protected $fillable = ['user_id', 'total_money', 'image_url', 'payer', 'status', 'content', 'notes', 'bank_info_id', 'unc_url', 'city_id', 'date_pay', 'booking_id', 'urgent'];
    
    public function bank()
    {
        return $this->belongsTo('App\Models\BankInfo', 'bank_info_id');
    }
    public function user()
    {
        return $this->belongsTo('App\Models\Account', 'user_id');
    }
}
