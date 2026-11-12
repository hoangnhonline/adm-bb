<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Cost extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'cost';

	 /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    protected $fillable = [ 'total_money', 'type', 'status', 'date_use', 'notes', 'image_url', 'content', 'cate_id', 'booking_id', 'amount', 'price', 'nguoi_chi', 'hdv_id', 'partner_id', 'city_id', 'is_fixed', 'cano_nhap', 'hoang_the', 'branch_id', 'created_user', 'updated_user','unc_type', 'time_chi_tien', 'sms_chi', 'thuc_chi', 'bank_info_id', 'xe_4t', 'image_list', 'ncc_id', 'detail_total', 'detail_check'];

    public function details()
    {
        return $this->hasMany('App\Models\CostDetail', 'cost_id');
    } 
    public function costType()
    {
        return $this->belongsTo('App\Models\CostType', 'cate_id');
    }
    public function partner()
    {
        return $this->belongsTo('App\Models\Partner', 'partner_id');
    }
    public function bank()
    {
        return $this->belongsTo('App\Models\BankInfo', 'bank_info_id');
    }
    public function ncc()
    {
        return $this->belongsTo('App\Models\NhaCungCap', 'ncc_id');
    }
    public function user()
    {
        return $this->belongsTo('App\User', 'created_user');
    }
}