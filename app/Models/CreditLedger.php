<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class CreditLedger extends Model {protected $guarded=[];protected function casts():array{return ['amount'=>'integer','balance_after'=>'integer','metadata'=>'array'];}}
