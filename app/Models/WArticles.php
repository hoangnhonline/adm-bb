<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class WArticles extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'w_articles';

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
    protected $fillable = ['title', 
                            'slug', 
                            'alias', 
                            'cate_id',
                            'parent_id',
                            'site_id',
                            'start_date',
                            'end_date',
                            'crawler_url',
                            'mobile_url',
                            'is_hot',                           
                            'type', 
                            'nguon', 
                            'status', 
                            'display_order', 
                            'description', 
                            'image_url', 
                            'content', 
                            'meta_id', 
                            'created_user', 
                            'updated_user',
                            'created_at'
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
    public function createdUser()
    {
        return $this->belongsTo('App\Models\WAccount', 'created_user');
    }
     public function updatedUser()
    {
        return $this->belongsTo('App\Models\WAccount', 'updated_user');
    }
    public function cate()
    {
        return $this->belongsTo('App\Models\WArticlesCate', 'cate_id');
    }
    public function parentCate()
    {
        return $this->belongsTo('App\Models\WCateParent', 'parent_id');
    }
}
