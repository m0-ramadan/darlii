<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class NewsletterSubscriber extends Model {protected $guarded=[];protected function casts():array{return ['verified_at'=>'datetime','unsubscribed_at'=>'datetime'];}}
