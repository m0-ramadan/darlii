<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class Lead extends Model {protected $guarded=[]; public function activities(){return $this->hasMany(LeadActivity::class);} public function property(){return $this->belongsTo(Property::class);} public function project(){return $this->belongsTo(Project::class);}}
