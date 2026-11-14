<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Dòng hàng trong 1 bill chi phí (cost).
 * source: 1 = nhập tay trên form, 2 = bóc tách từ cost.notes (dữ liệu cũ), 3 = đọc từ ảnh hóa đơn
 * need_review: 1 = cần người kiểm tra lại
 */
class CostDetail extends Model  {
    use \App\Traits\LogsActivity;


    protected $table = 'cost_detail';

    public $timestamps = true;

    protected $fillable = ['cost_id', 'ingredient_id', 'quantity', 'unit', 'unit_price', 'amount', 'notes',
                           'need_review', 'source', 'created_user'];

    public function cost()
    {
        return $this->belongsTo('App\Models\Cost', 'cost_id');
    }

    public function ingredient()
    {
        return $this->belongsTo('App\Models\Ingredient', 'ingredient_id');
    }
}
