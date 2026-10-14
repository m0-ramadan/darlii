<?php

namespace App\Services\Analytics;

use App\Models\SiteVisitor;
use App\Models\SiteVisitorHit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VisitorTrackerService
{
    public function __construct(
        protected EgyptGeoResolver $geoResolver
    ) {}

    /**
     * تسجيل زيارة وتفاصيل الزائر بالكامل.
     */
    public function track(Request $request, array $payload = []): SiteVisitor
    {
        $visitorId = $payload['visitor_id'] ?? $request->cookie('darli_visitor_id');
        if (empty($visitorId)) {
            $visitorId = (string) Str::uuid();
        }

        $ip = $this->getClientIp($request);
        $userAgent = (string) ($payload['user_agent'] ?? $request->userAgent());

        $deviceInfo = $this->parseUserAgent($userAgent, $payload);

        $lat = isset($payload['latitude']) ? (float) $payload['latitude'] : null;
        $lng = isset($payload['longitude']) ? (float) $payload['longitude'] : null;

        // تحديد الموقع الجغرافي والمركز والمحافظة
        if ($lat !== null && $lng !== null && ($lat != 0 || $lng != 0)) {
            $geo = $this->geoResolver->resolveFromCoordinates($lat, $lng);
            $geo['accuracy_meters'] = isset($payload['accuracy']) ? (int) $payload['accuracy'] : null;
        } else {
            $geo = $this->geoResolver->resolveFromIp($ip);
        }

        $visitor = SiteVisitor::where('visitor_id', $visitorId)->first();

        $userId = auth()->id() ?: ($payload['user_id'] ?? null);
        $currentUrl = $payload['url'] ?? $request->fullUrl();
        $referrer = $payload['referrer'] ?? $request->header('referer');

        if ($visitor) {
            $updateData = [
                'user_id' => $userId ?: $visitor->user_id,
                'ip_address' => $ip,
                'last_page' => substr($currentUrl, 0, 500),
                'visits_count' => $visitor->visits_count + 1,
                'page_views_count' => $visitor->page_views_count + 1,
                'last_active_at' => now(),
            ];

            // تحديث الموقع إذا كان المصدر الحالي GPS أو إذا كان الموقع السابق غير محدد
            if (($geo['location_source'] ?? '') === 'gps' || empty($visitor->governorate)) {
                $updateData = array_merge($updateData, [
                    'governorate' => $geo['governorate'] ?? $visitor->governorate,
                    'city_or_markaz' => $geo['city_or_markaz'] ?? $visitor->city_or_markaz,
                    'district' => $geo['district'] ?? $visitor->district,
                    'formatted_address' => $geo['formatted_address'] ?? $visitor->formatted_address,
                    'latitude' => $geo['latitude'] ?? $visitor->latitude,
                    'longitude' => $geo['longitude'] ?? $visitor->longitude,
                    'location_source' => $geo['location_source'] ?? $visitor->location_source,
                    'accuracy_meters' => $geo['accuracy_meters'] ?? $visitor->accuracy_meters,
                ]);
            }

            if (!empty($payload['screen_resolution'])) {
                $updateData['screen_resolution'] = $payload['screen_resolution'];
            }

            $visitor->update($updateData);
        } else {
            $visitor = SiteVisitor::create([
                'visitor_id' => $visitorId,
                'user_id' => $userId,
                'ip_address' => $ip,
                'country_code' => $geo['country_code'] ?? 'EG',
                'country_name' => $geo['country_name'] ?? 'مصر',
                'governorate' => $geo['governorate'] ?? null,
                'city_or_markaz' => $geo['city_or_markaz'] ?? null,
                'district' => $geo['district'] ?? null,
                'formatted_address' => $geo['formatted_address'] ?? null,
                'latitude' => $geo['latitude'] ?? null,
                'longitude' => $geo['longitude'] ?? null,
                'location_source' => $geo['location_source'] ?? 'ip',
                'accuracy_meters' => $geo['accuracy_meters'] ?? null,
                'device_type' => $deviceInfo['device_type'],
                'device_brand' => $deviceInfo['device_brand'],
                'device_model' => $deviceInfo['device_model'],
                'os' => $deviceInfo['os'],
                'browser' => $deviceInfo['browser'],
                'browser_language' => substr((string) ($payload['language'] ?? $request->getPreferredLanguage()), 0, 20),
                'screen_resolution' => $payload['screen_resolution'] ?? null,
                'user_agent' => substr($userAgent, 0, 1000),
                'landing_page' => substr($currentUrl, 0, 500),
                'last_page' => substr($currentUrl, 0, 500),
                'referrer' => substr((string) $referrer, 0, 500),
                'utm_source' => $request->query('utm_source'),
                'utm_medium' => $request->query('utm_medium'),
                'utm_campaign' => $request->query('utm_campaign'),
                'visits_count' => 1,
                'page_views_count' => 1,
                'first_visited_at' => now(),
                'last_active_at' => now(),
            ]);
        }

        // تسجيل الضغطة / الصفحة الفردية
        if ($visitor && !empty($currentUrl)) {
            SiteVisitorHit::create([
                'visitor_id' => $visitor->id,
                'url' => substr($currentUrl, 0, 500),
                'page_title' => substr((string) ($payload['page_title'] ?? ''), 0, 255),
                'referrer' => substr((string) $referrer, 0, 500),
                'ip_address' => $ip,
            ]);
        }

        return $visitor;
    }

    /**
     * تحديث الموقع الجغرافي الدقيق عند حصول المتصفح على إذن وموقع GPS.
     */
    public function updateGpsLocation(string $visitorId, float $lat, float $lng, ?int $accuracy = null): ?SiteVisitor
    {
        $visitor = SiteVisitor::where('visitor_id', $visitorId)->first();
        if (!$visitor) {
            return null;
        }

        $geo = $this->geoResolver->resolveFromCoordinates($lat, $lng);

        $visitor->update([
            'governorate' => $geo['governorate'] ?? $visitor->governorate,
            'city_or_markaz' => $geo['city_or_markaz'] ?? $visitor->city_or_markaz,
            'district' => $geo['district'] ?? $visitor->district,
            'formatted_address' => $geo['formatted_address'] ?? $visitor->formatted_address,
            'latitude' => $lat,
            'longitude' => $lng,
            'location_source' => 'gps',
            'accuracy_meters' => $accuracy,
            'last_active_at' => now(),
        ]);

        return $visitor;
    }

    /**
     * استخراج وتصنيف تفاصيل الجهاز والنظام والمتصفح من الـ User-Agent.
     */
    public function parseUserAgent(string $ua, array $payload = []): array
    {
        $deviceType = 'desktop';
        $brand = null;
        $model = null;
        $os = 'غير محدد';
        $browser = 'غير محدد';

        // 1. نظام التشغيل
        if (preg_match('/windows nt 10/i', $ua)) {
            $os = 'Windows 10/11';
        } elseif (preg_match('/windows nt 6\.3/i', $ua)) {
            $os = 'Windows 8.1';
        } elseif (preg_match('/windows nt 6\.1/i', $ua)) {
            $os = 'Windows 7';
        } elseif (preg_match('/android (\d+(\.\d+)?)/i', $ua, $matches)) {
            $os = 'Android ' . $matches[1];
            $deviceType = 'mobile';
        } elseif (preg_match('/iphone os (\d+(_\d+)?)/i', $ua, $matches)) {
            $os = 'iOS ' . str_replace('_', '.', $matches[1]);
            $deviceType = 'mobile';
            $brand = 'Apple';
            $model = 'iPhone';
        } elseif (preg_match('/ipad; cpu os (\d+(_\d+)?)/i', $ua, $matches)) {
            $os = 'iPadOS ' . str_replace('_', '.', $matches[1]);
            $deviceType = 'tablet';
            $brand = 'Apple';
            $model = 'iPad';
        } elseif (preg_match('/macintosh|mac os x/i', $ua)) {
            $os = 'macOS';
        } elseif (preg_match('/linux/i', $ua)) {
            $os = 'Linux';
        }

        // 2. نوع الجهاز
        if (preg_match('/tablet|ipad|playbook|silk/i', $ua)) {
            $deviceType = 'tablet';
        } elseif (preg_match('/mobile|android|iphone|ipod|blackberry|opera mini|iemobile/i', $ua)) {
            $deviceType = 'mobile';
        }

        // 3. المتصفح
        if (preg_match('/edg\/([0-9\.]+)/i', $ua)) {
            $browser = 'Microsoft Edge';
        } elseif (preg_match('/samsungbrowser\/([0-9\.]+)/i', $ua)) {
            $browser = 'Samsung Internet';
        } elseif (preg_match('/chrome\/([0-9\.]+)/i', $ua)) {
            $browser = 'Google Chrome';
        } elseif (preg_match('/firefox\/([0-9\.]+)/i', $ua)) {
            $browser = 'Mozilla Firefox';
        } elseif (preg_match('/safari\/([0-9\.]+)/i', $ua) && !preg_match('/chrome/i', $ua)) {
            $browser = 'Apple Safari';
        } elseif (preg_match('/opr\/|opera\//i', $ua)) {
            $browser = 'Opera';
        }

        // 4. الماركة والموديل
        if (!$brand && preg_match('/samsung|sm-[a-z0-9]+/i', $ua, $m)) {
            $brand = 'Samsung';
            $model = strtoupper($m[0]);
        } elseif (!$brand && preg_match('/xiaomi|redmi|poco/i', $ua, $m)) {
            $brand = 'Xiaomi';
            $model = ucfirst($m[0]);
        } elseif (!$brand && preg_match('/huawei|honor/i', $ua, $m)) {
            $brand = 'Huawei';
        } elseif (!$brand && preg_match('/oppo/i', $ua)) {
            $brand = 'OPPO';
        } elseif (!$brand && preg_match('/vivo/i', $ua)) {
            $brand = 'vivo';
        }

        return [
            'device_type' => $deviceType,
            'device_brand' => $brand,
            'device_model' => $model,
            'os' => $os,
            'browser' => $browser,
        ];
    }

    /**
     * استخراج عنوان الـ IP الحقيقي للزائر حتى خلف البروكسي والكلودفلير.
     */
    protected function getClientIp(Request $request): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_REAL_IP',
            'HTTP_X_FORWARDED_FOR',
        ];

        foreach ($headers as $header) {
            $val = $request->server($header);
            if (!empty($val)) {
                $parts = explode(',', $val);
                return trim($parts[0]);
            }
        }

        return (string) $request->ip() ?: '127.0.0.1';
    }
}

