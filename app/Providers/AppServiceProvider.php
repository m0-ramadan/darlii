<?php
namespace App\Providers;
use App\Models\Property;
use App\Models\User;
use App\Policies\PropertyPolicy;
use App\Services\Settings\SettingsRepository;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider {
 public function register():void{$this->app->singleton(SettingsRepository::class);}
 public function boot():void{
  // Use the custom darli pagination view everywhere — one clean bar, no duplicates
  Paginator::defaultView('vendor.pagination.darli');
  Paginator::defaultSimpleView('vendor.pagination.darli');
  $settings=$this->app->make(SettingsRepository::class);
  $supported=[];
  if($settings->get('general.language_ar_enabled',true))$supported[]='ar';
  if($settings->get('general.language_en_enabled',false))$supported[]='en';
  if($supported===[] || app()->runningUnitTests())$supported=['ar','en'];
  config([
   'locales.supported'=>$supported,
   'locales.names'=>array_intersect_key(config('locales.names',[]),array_flip($supported)),
   'locales.default'=>in_array(config('locales.default','ar'),$supported,true)?config('locales.default','ar'):$supported[0],
  ]);
  \Illuminate\Support\Facades\URL::defaults(['locale' => app()->getLocale() ?: config('locales.default')]);
  Gate::policy(Property::class,PropertyPolicy::class);
  // The CMS checks permissions by slug: @can('property.approve'), etc.
  // Admins implicitly pass every check (see User::hasPermission()).
  Gate::before(function(User $user,string $ability){
   if(!str_contains($ability,'.'))return null;
   return $user->hasPermission($ability)?true:null;
  });
 }
}
