<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class BookingBbc extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'booking_bbc';

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
                            'phone',
                            'use_date',
                            'total_price',
                            'extra_fee',
                            'discount',
                            'type',
                            'tien_coc',
                            'ngay_coc',
                            'status',
                            'con_lai',
                            'notes',
                            'user_id',
                            'hoa_hong_cty',
                            'hoa_hong_sales',
                            'nguoi_thu_coc', // 1 sales , 2 cty
                            'nguoi_thu_tien', // 1 sales , 2 cty, 3 dieu hanh
                            'city_id',
                            'created_user',
                            'updated_user',
                            'per_com',
                            'commision',
                            'bill_no',
                            'branch_id',
                            'sms_thu',
                            'sms_chi',
                            'unc_type',
                            'partner_id',
                            'da_thu',
                            'chup_anh',
                            'xe_4t',
                            'nguoi_tu_van'
                            ];
    public static function getList($params = []){
        $query = self::where('status', 1);
        if( isset($params['cate_id']) && $params['cate_id'] ){
            $query->where('cate_id', $params['cate_id']);
        }
        if( isset($params['parent_id']) && $params['parent_id'] ){
            $query->where('parent_id', $params['parent_id']);
        }
        if( isset($params['is_hot']) && $params['is_hot'] ){
            $query->where('is_hot', $params['is_hot']);
        }
        if( isset($params['except']) && $params['except'] ){
            $query->where('id', '<>',  $params['except']);
        }
        $query->orderBy('id', 'desc');
        if(isset($params['limit']) && $params['limit']){
            return $query->limit($params['limit'])->get();
        }
        if(isset($params['pagination']) && $params['pagination']){
            return $query->paginate($params['pagination']);
        }
    }
    public static function getListTag($id){
        $query = TagObjects::where(['object_id' => $id, 'tag_objects.type' => 2])
            ->join('tag', 'w-tag.id', '=', 'tag_objects.tag_id')
            ->get();
        return $query;
    }
    public function customers()
    {
        return $this->hasMany('App\Models\BookingBbcCustomer', 'booking_id');
    }
    public function user()
    {
        return $this->belongsTo('App\User', 'created_user');
    }
    public function ctv()
    {
        return $this->belongsTo('App\Models\Ctv', 'ctv_id');
    }
    public function location()
    {
        return $this->belongsTo('App\Models\Location', 'location_id');
    }
    public function location2()
    {
        return $this->belongsTo('App\Models\Location', 'location_id_2');
    }
    public function carCate()
    {
        return $this->belongsTo('App\Models\CarCate', 'tour_id');
    }
    public function hdv()
    {
        return $this->belongsTo('App\User', 'hdv_id');
    }
    public function cano()
    {
        return $this->belongsTo('App\Models\Partner', 'cano_id');
    }
     public function updatedUser()
    {
        return $this->belongsTo('App\Models\WAccount', 'updated_user');
    }
    public function cate()
    {
        return $this->belongsTo('App\Models\WArticlesCate', 'cate_id');
    }
    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotels', 'hotel_id');
    }
    public function hotelBook()
    {
        return $this->belongsTo('App\Models\Hotels', 'hotel_book');
    }
    public function driver()
    {
        return $this->belongsTo('App\Models\Drivers', 'driver_id');
    }
    public function details()
    {
        return $this->hasMany('App\Models\BookingBbcDetail', 'booking_id');
    }
    public function tickets()
    {
        return $this->hasMany('App\Models\Tickets', 'booking_id');
    }
    public function payment()
    {
        return $this->hasMany('App\Models\BookingBbcPayment', 'booking_id');
    }
    public function bill()
    {
        return $this->hasMany('App\Models\BookingBbcBill', 'booking_id');
    }
    public function parentCate()
    {
        return $this->belongsTo('App\Models\WCateParent', 'parent_id');
    }
    public function partnerTour()
    {
        return $this->belongsTo('App\Models\Partner', 'partner_id');
    }
    public function partner()
    {
      return $this->belongsTo('App\User', 'partner_id');
    }
    public function tuvan()
    {
        return $this->belongsTo('App\Models\NguoiTuVan', 'nguoi_tu_van');
    }
}
