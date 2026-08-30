<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class PropertyCategory extends Model {protected $guarded=[]; public function translations(){return $this->hasMany(PropertyCategoryTranslation::class);} public function translation(?string $locale=null){return $this->translations->firstWhere('locale',$locale?:app()->getLocale())??$this->translations->first();}}
