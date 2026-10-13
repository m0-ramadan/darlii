<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Setting extends Model {
 protected $fillable=['group','key','value','type','label_ar','help_ar','is_public','sort_order'];
 protected function casts():array{return ['is_public'=>'boolean'];}
 public function scopeGroup($query,string $group){return $query->where('group',$group);}
}
