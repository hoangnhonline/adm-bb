<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Danh mục nguyên liệu / mặt hàng nhập.
 * aliases: tên gọi khác (Việt/Anh), phân cách dấu phẩy – dùng cho ô tìm kiếm trên form.
 */
class Ingredient extends Model  {
    use \App\Traits\LogsActivity;


    protected $table = 'ingredient';

    public $timestamps = true;

    protected $fillable = ['name', 'name_en', 'aliases', 'cost_type_id', 'unit', 'display_order', 'status'];

    public function costType()
    {
        return $this->belongsTo('App\Models\CostType', 'cost_type_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
