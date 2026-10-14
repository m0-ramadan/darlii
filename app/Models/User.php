<?php
namespace App\Models;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable implements MustVerifyEmail {
 use HasFactory,Notifiable,SoftDeletes;

 protected $fillable=[
  'name','email','password','phone','job_title','avatar_path','notes','status',
  'preferred_locale','preferred_currency','preferred_theme',
  'manager_id','last_login_at','last_login_ip','sort_order',
 ];
 protected $hidden=['password','remember_token'];
 protected function casts():array{return ['email_verified_at'=>'datetime','last_login_at'=>'datetime','password'=>'hashed'];}
 protected $appends=['avatar_url'];

 // ---------------------------------------------------------------- relations
 public function roles(){return $this->belongsToMany(Role::class,'role_user');}
 public function agentProfile(){return $this->hasOne(AgentProfile::class);}
 public function agencies(){return $this->belongsToMany(Agency::class,'agency_user');}
 public function getAgencyAttribute(){return $this->agencies()->first() ?? $this->agentProfile?->agency;}
 public function isPending():bool{return $this->status === 'pending';}
 public function isCompany():bool{return $this->hasRole('agency-manager') || $this->agencies()->exists();}
 public function isBroker():bool{return $this->hasRole('agent') || $this->agentProfile()->exists();}
 public function manager(){return $this->belongsTo(self::class,'manager_id');}
 public function subordinates(){return $this->hasMany(self::class,'manager_id');}
 public function properties(){return $this->hasMany(Property::class,'owner_id');}
 public function createdProperties(){return $this->hasMany(Property::class,'created_by');}
 public function managedProperties(){return $this->hasMany(Property::class,'managed_by');}
 public function assignedLeads(){return $this->hasMany(Lead::class,'assigned_to');}
 public function auditLogs(){return $this->hasMany(AuditLog::class);}

 // ---------------------------------------------------------------- accessors
 public function getAvatarUrlAttribute():string
 {
  if ($this->avatar_path) {
   if (str_starts_with($this->avatar_path, 'http')) {
    return $this->avatar_path;
   }
   $trimmed = ltrim($this->avatar_path, '/');
   return str_starts_with($trimmed, 'storage/') ? asset($trimmed) : asset('storage/' . $trimmed);
  }

  return 'https://ui-avatars.com/api/?background=0F3D2E&color=fff&name=' . urlencode($this->name ?: 'User');
 }

 public function getPrimaryRoleAttribute():?Role
 {
  return $this->roles->sortByDesc('level')->first();
 }

 public function getPrimaryRoleSlugAttribute():?string
 {
  return $this->primary_role?->slug;
 }

 public function roleLabels():string
 {
  return $this->roles->map(fn (Role $r) => $r->label_ar ?: $r->name)->join('، ') ?: '—';
 }

 // ---------------------------------------------------------------- permission
 public function hasRole(string $role):bool
 {
  return $this->roles->contains(fn ($r) => $r->slug === $role);
 }

 /** @param list<string> $roles */
 public function hasAnyRole(array $roles):bool
 {
  return $this->roles->whereIn('slug', $roles)->isNotEmpty();
 }

 public function isSuperAdmin():bool{return $this->hasRole('super-admin');}
 public function isAdmin():bool{return $this->hasAnyRole(['super-admin','admin']);}
 public function isManager():bool{return $this->hasAnyRole(['manager','property-manager','agency-manager']);}
 public function isAgent():bool{return $this->hasRole('agent');}

 /** Admin + super-admin bypass every permission check. */
 public function hasPermission(string $permission):bool
 {
  if ($this->isAdmin()) {
   return true;
  }

  return $this->relationLoaded('roles')
   ? $this->roles->contains(fn (Role $r) => $r->permissions->contains('slug', $permission))
   : $this->roles()->whereHas('permissions', fn ($q) => $q->where('permissions.slug', $permission))->exists();
 }

 /** @param list<string> $permissions */
 public function hasAnyPermission(array $permissions):bool
 {
  return collect($permissions)->contains(fn (string $p) => $this->hasPermission($p));
 }

 public function canAccessPanel():bool
 {
  return $this->status === 'active'
   && $this->hasAnyRole(\App\Support\PermissionCatalog::panelRoles());
 }

 /**
  * Everyone this user may manage. Admins see the whole tree, a manager sees
  * the agents attached to them, and an agent only sees themselves.
  */
 public function visibleUserIds():Collection
 {
  if ($this->isAdmin()) {
   return static::query()->pluck('id');
  }

  if ($this->isManager()) {
   return static::query()->where('manager_id', $this->id)->pluck('id')->push($this->id);
  }

  return collect([$this->id]);
 }

 /** Scope applied to every property query in the CMS. */
 public function canSeeAllProperties():bool
 {
  return $this->isAdmin() || $this->hasPermission('property.update-any') || $this->hasPermission('lead.view-all');
 }

 public function canSeeAllLeads():bool
 {
  return $this->isAdmin() || $this->hasPermission('lead.view-all');
 }
}
