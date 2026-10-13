<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Permission extends Model {
 protected $fillable=['name','slug','group','label_ar','sort_order'];
 public function roles(){return $this->belongsToMany(Role::class,'permission_role');}
 public function label():string{return $this->label_ar ?: $this->name;}
}
