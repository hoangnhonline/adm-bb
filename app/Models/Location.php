<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Location extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'location';	

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
    protected $fillable = ['name', 'slug', 'address', 'subcharge', 'latitude', 'longitude', 'pickup_time', 'notes', 'area_id', 'distance', 'is_ben', 'created_user'];

    public function maxDate($location_id){
       // dd($location_id);
        $rs = Booking::where('location_id', $location_id)->orderBy('use_date', 'DESC')->first();
     //   dd($rs);
        if($rs){
            $r = $rs->toArray();
           // dd($rs);
            return $r['use_date'];
        }else{
            return '';
        }
        
    }
    
}
