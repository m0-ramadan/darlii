<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Location extends Model {protected $guarded=[]; public function parent(){return $this->belongsTo(self::class,'parent_id');} public function children(){return $this->hasMany(self::class,'parent_id');} public function translations(){return $this->hasMany(LocationTranslation::class);} public function translation(?string $locale=null){return $this->translations->firstWhere('locale',$locale?:app()->getLocale())??$this->translations->firstWhere('locale',config('app.fallback_locale'))??$this->translations->first();}}
