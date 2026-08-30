<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; class ProjectUnit extends Model {protected $guarded=[];protected function casts():array{return ['price'=>'decimal:4'];}}
