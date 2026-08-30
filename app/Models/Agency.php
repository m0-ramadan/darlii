<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\SoftDeletes;
class Agency extends Model {use SoftDeletes; protected $guarded=[]; public function users(){return $this->belongsToMany(User::class,'agency_user');}}
