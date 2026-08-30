<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Currency extends Model {protected $fillable=['code','name','symbol','exchange_rate','decimal_places','symbol_position','is_default','is_active']; protected function casts():array{return ['exchange_rate'=>'decimal:6','is_default'=>'boolean','is_active'=>'boolean'];}}
