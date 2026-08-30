<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class Payment extends Model {protected $guarded=[];protected function casts():array{return ['amount'=>'decimal:4','payload'=>'array','paid_at'=>'datetime'];}}
