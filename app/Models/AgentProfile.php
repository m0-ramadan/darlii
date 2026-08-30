<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AgentProfile extends Model {protected $guarded=[]; protected function casts():array{return ['verified_at'=>'datetime'];} public function user(){return $this->belongsTo(User::class);} public function translations(){return $this->hasMany(AgentProfileTranslation::class);} public function translation(?string $locale=null){return $this->translations->firstWhere('locale',$locale?:app()->getLocale())??$this->translations->firstWhere('locale',config('app.fallback_locale'))??$this->translations->first();} public function agency(){return $this->belongsTo(Agency::class);}}
