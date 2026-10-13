<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Role extends Model {
 protected $fillable=['name','slug','label_ar','description','level','is_system'];
 protected function casts():array{return ['is_system'=>'boolean','level'=>'integer'];}
 public function permissions(){return $this->belongsToMany(Permission::class,'permission_role');}
 public function users(){return $this->belongsToMany(User::class,'role_user');}
 public function label():string{return $this->label_ar ?: $this->name;}
}
