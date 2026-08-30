<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class PropertyTranslation extends Model {protected $guarded=[]; public $timestamps=false; public function property(){return $this->belongsTo(Property::class);}}
