<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\SoftDeletes;
class Page extends Model {use SoftDeletes;protected $guarded=[];public function translations(){return $this->hasMany(PageTranslation::class);} public function translation(?string $locale=null){return $this->translations->firstWhere('locale',$locale?:app()->getLocale())??$this->translations->first();}}
