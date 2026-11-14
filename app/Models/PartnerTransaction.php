<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerTransaction extends Model
{
    protected $table = 'partner_transactions';

    const TYPE_THU = 1;     // đối tác thanh toán cho nhà hàng
    const TYPE_CHI_HH = 2;  // nhà hàng chi hoa hồng cho đối tác
    const TYPE_BU_TRU = 3;  // bù trừ phải thu với hoa hồng phải trả

    public static $typeNames = [
        1 => 'Đối tác thanh toán',
        2 => 'Chi hoa hồng',
        3 => 'Bù trừ công nợ',
    ];

    protected $fillable = ['partner_id', 'branch_id', 'date', 'type', 'amount', 'pay_method', 'cost_id',
        'notes', 'status', 'created_user', 'updated_user'];

    public function partner()
    {
        return $this->belongsTo('App\Models\Partner', 'partner_id');
    }
    public function createdUser()
    {
        return $this->belongsTo('App\Models\Account', 'created_user');
    }
}
