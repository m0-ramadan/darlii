<?php
namespace App\Models;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable; use Illuminate\Database\Eloquent\SoftDeletes;
class User extends Authenticatable implements MustVerifyEmail {
 use HasFactory,Notifiable,SoftDeletes;
 protected $fillable=['name','email','password','phone','preferred_locale','preferred_currency','preferred_theme','status']; protected $hidden=['password','remember_token'];
 protected function casts():array{return ['email_verified_at'=>'datetime','password'=>'hashed'];}
 public function roles(){return $this->belongsToMany(Role::class,'role_user');}
 public function hasRole(string $role):bool{return $this->roles->contains(fn($r)=>$r->slug===$role);}
 public function hasPermission(string $permission):bool{return $this->roles()->whereHas('permissions',fn($q)=>$q->where('permissions.slug',$permission))->exists();}
 public function agentProfile(){return $this->hasOne(AgentProfile::class);}
}
