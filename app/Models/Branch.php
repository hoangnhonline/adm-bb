<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Branch extends Model  {
    use \App\Traits\LogsActivity;


	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'branch';	

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
    protected $fillable = ['name', 'status', 'display_order'];

    // Phân quyền: tài khoản bị giới hạn chi nhánh chỉ thấy chi nhánh được gán (danh sách, bộ lọc, dropdown)
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('perm_branch', function ($q) {
            $ids = \App\Services\Perm::branchIds();
            if ($ids !== null) $q->whereIn('branch.id', $ids ?: [0]);
        });
    }
    
}
