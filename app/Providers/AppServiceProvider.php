<?php
namespace App\Providers;
use App\Models\Property;
use App\Models\User;
use App\Policies\PropertyPolicy;
use App\Services\Settings\SettingsRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider {
 public function register():void{$this->app->singleton(SettingsRepository::class);}
 public function boot():void{
  // Use the custom darli pagination view everywhere — one clean bar, no duplicates
  Paginator::defaultView('vendor.pagination.darli');
  Paginator::defaultSimpleView('vendor.pagination.darli');
  RateLimiter::for('registration', function (Request $request) {
   $identity = $request->input('company_email')
       ?: $request->input('email')
       ?: $request->input('phone')
       ?: 'guest';

   return Limit::perMinute(10)
       ->by(strtolower(trim((string) $identity)).'|'.$request->ip())
       ->response(function (Request $request, array $headers) {
        $retryAfter = (int) ($headers['Retry-After'] ?? 60);
        $message = app()->getLocale() === 'ar'
            ? 'تم إرسال محاولات كثيرة. يرجى الانتظار '.$retryAfter.' ثانية ثم المحاولة مرة أخرى.'
            : 'Too many registration attempts. Please wait '.$retryAfter.' seconds and try again.';

        return response()->json(['message' => $message], 429, $headers);
       });
  });
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
  $this->app->extend('url', function ($url, $app) {
      return new \App\Routing\CustomUrlGenerator(
          $app['router']->getRoutes(),
          $app['request'],
          $app['config']['app.asset_url']
      );
  });
  Gate::policy(Property::class,PropertyPolicy::class);
  // The CMS checks permissions by slug: @can('property.approve'), etc.
  // Admins implicitly pass every check (see User::hasPermission()).
  Gate::before(function(User $user,string $ability){
   if(!str_contains($ability,'.'))return null;
   return $user->hasPermission($ability)?true:null;
  });
 }
}
