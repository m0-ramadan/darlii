<?php
namespace App\Models;
use App\Modules\Property\Domain\Enums\PropertyStatus;
use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\SoftDeletes;
class Property extends Model {use SoftDeletes; protected $guarded=[];
 protected function casts():array{return ['status'=>PropertyStatus::class,'price'=>'decimal:4','is_featured'=>'boolean','published_at'=>'datetime','expires_at'=>'datetime','featured_until'=>'datetime'];}
 public function translations(){return $this->hasMany(PropertyTranslation::class);} public function translation(?string $locale=null){$locale=$locale?:app()->getLocale();return $this->translations->firstWhere('locale',$locale)??$this->translations->firstWhere('locale',config('app.fallback_locale'))??$this->translations->first();}
 public function amenities(){return $this->belongsToMany(Amenity::class,'property_amenity');} public function currency(){return $this->belongsTo(Currency::class);} public function category(){return $this->belongsTo(PropertyCategory::class,'category_id');} public function owner(){return $this->belongsTo(User::class,'owner_id');} public function location(){return $this->belongsTo(Location::class);} public function project(){return $this->belongsTo(Project::class);} public function media(){return $this->hasMany(PropertyMedia::class)->orderBy('sort_order');} public function statusHistory(){return $this->hasMany(PropertyStatusHistory::class);}
}
