<?php

namespace App\Services\Analytics;

use App\Support\BeniSuefCatalog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EgyptGeoResolver
{
    /**
     * المراكز والمدن الرئيسية في محافظات مصر مع إحداثياتها التقريبية
     * لدقة مطابقة المسافات (Proximity Matching).
     */
    protected static array $egyptMarkazes = [
        // بني سويف
        ['governorate' => 'بني سويف', 'markaz' => 'مدينة بني سويف', 'lat' => 29.0661, 'lng' => 31.0994],
        ['governorate' => 'بني سويف', 'markaz' => 'مدينة بني سويف الجديدة', 'lat' => 29.0335, 'lng' => 31.1610],
        ['governorate' => 'بني سويف', 'markaz' => 'مركز ناصر', 'lat' => 29.1810, 'lng' => 31.0630],
        ['governorate' => 'بني سويف', 'markaz' => 'مركز الواسطى', 'lat' => 29.3390, 'lng' => 31.2110],
        ['governorate' => 'بني سويف', 'markaz' => 'مركز إهناسيا', 'lat' => 29.0780, 'lng' => 30.9430],
        ['governorate' => 'بني سويف', 'markaz' => 'مركز ببا', 'lat' => 28.9210, 'lng' => 30.9850],
        ['governorate' => 'بني سويف', 'markaz' => 'مركز سمسطا', 'lat' => 28.9110, 'lng' => 30.8510],
        ['governorate' => 'بني سويف', 'markaz' => 'مركز الفشن', 'lat' => 28.7950, 'lng' => 30.8890],

        // القاهرة
        ['governorate' => 'القاهرة', 'markaz' => 'التجمع الخامس والقاهرة الجديدة', 'lat' => 30.0131, 'lng' => 31.4913],
        ['governorate' => 'القاهرة', 'markaz' => 'مدينة نصر', 'lat' => 30.0536, 'lng' => 31.3392],
        ['governorate' => 'القاهرة', 'markaz' => 'مصر الجديدة', 'lat' => 30.0982, 'lng' => 31.3340],
        ['governorate' => 'القاهرة', 'markaz' => 'المعادي', 'lat' => 29.9602, 'lng' => 31.2568],
        ['governorate' => 'القاهرة', 'markaz' => 'وسط البلد', 'lat' => 30.0444, 'lng' => 31.2357],
        ['governorate' => 'القاهرة', 'markaz' => 'المقطم', 'lat' => 30.0167, 'lng' => 31.3000],
        ['governorate' => 'القاهرة', 'markaz' => 'الشروق وبدر', 'lat' => 30.1450, 'lng' => 31.6260],
        ['governorate' => 'القاهرة', 'markaz' => 'حلوان', 'lat' => 29.8414, 'lng' => 31.3008],

        // الجيزة
        ['governorate' => 'الجيزة', 'markaz' => 'الدقي والمهندسين', 'lat' => 30.0385, 'lng' => 31.2124],
        ['governorate' => 'الجيزة', 'markaz' => 'مدينة 6 أكتوبر', 'lat' => 29.9737, 'lng' => 30.9529],
        ['governorate' => 'الجيزة', 'markaz' => 'الشيخ زايد', 'lat' => 30.0573, 'lng' => 30.9840],
        ['governorate' => 'الجيزة', 'markaz' => 'الهرم وفيصل', 'lat' => 29.9950, 'lng' => 31.1490],
        ['governorate' => 'الجيزة', 'markaz' => 'مركز كرداسة', 'lat' => 30.0322, 'lng' => 31.1118],
        ['governorate' => 'الجيزة', 'markaz' => 'مركز أوسيم', 'lat' => 30.1234, 'lng' => 31.1345],
        ['governorate' => 'الجيزة', 'markaz' => 'مركز الحوامدية', 'lat' => 29.8970, 'lng' => 31.2650],
        ['governorate' => 'الجيزة', 'markaz' => 'مركز البدرشين', 'lat' => 29.8167, 'lng' => 31.2667],
        ['governorate' => 'الجيزة', 'markaz' => 'مركز العياط', 'lat' => 29.6200, 'lng' => 31.2600],
        ['governorate' => 'الجيزة', 'markaz' => 'مركز الصف', 'lat' => 29.5700, 'lng' => 31.3100],

        // الإسكندرية
        ['governorate' => 'الإسكندرية', 'markaz' => 'حي شرق (سموحة وسيدي جابر)', 'lat' => 31.2156, 'lng' => 29.9553],
        ['governorate' => 'الإسكندرية', 'markaz' => 'حي المنتزه', 'lat' => 31.2783, 'lng' => 30.0152],
        ['governorate' => 'الإسكندرية', 'markaz' => 'حي وسط ومحرم بك', 'lat' => 31.1963, 'lng' => 29.9142],
        ['governorate' => 'الإسكندرية', 'markaz' => 'برج العرب', 'lat' => 30.9167, 'lng' => 29.5333],
        ['governorate' => 'الإسكندرية', 'markaz' => 'العجمي', 'lat' => 31.1167, 'lng' => 29.7667],

        // القليوبية
        ['governorate' => 'القليوبية', 'markaz' => 'مركز بنها', 'lat' => 30.4667, 'lng' => 31.1833],
        ['governorate' => 'القليوبية', 'markaz' => 'شبرا الخيمة', 'lat' => 30.1286, 'lng' => 31.2422],
        ['governorate' => 'القليوبية', 'markaz' => 'مركز طوخ', 'lat' => 30.3500, 'lng' => 31.2000],
        ['governorate' => 'القليوبية', 'markaz' => 'مدينة العبور', 'lat' => 30.2200, 'lng' => 31.4800],

        // الشرقية
        ['governorate' => 'الشرقية', 'markaz' => 'مركز الزقازيق', 'lat' => 30.5877, 'lng' => 31.5020],
        ['governorate' => 'الشرقية', 'markaz' => 'مدينة العاشر من رمضان', 'lat' => 30.3000, 'lng' => 31.7500],
        ['governorate' => 'الشرقية', 'markaz' => 'مركز بلبيس', 'lat' => 30.4200, 'lng' => 31.5600],

        // الدقهلية
        ['governorate' => 'الدقهلية', 'markaz' => 'مركز المنصورة', 'lat' => 31.0409, 'lng' => 31.3785],
        ['governorate' => 'الدقهلية', 'markaz' => 'مركز ميت غمر', 'lat' => 30.7200, 'lng' => 31.2600],

        // الغربية
        ['governorate' => 'الغربية', 'markaz' => 'مركز طنطا', 'lat' => 30.7865, 'lng' => 31.0004],
        ['governorate' => 'الغربية', 'markaz' => 'مركز المحلة الكبرى', 'lat' => 30.9700, 'lng' => 31.1667],

        // المنوفية
        ['governorate' => 'المنوفية', 'markaz' => 'مركز شبين الكوم', 'lat' => 30.5522, 'lng' => 31.0100],
        ['governorate' => 'المنوفية', 'markaz' => 'مدينة السادات', 'lat' => 30.3800, 'lng' => 30.5200],

        // الفيوم
        ['governorate' => 'الفيوم', 'markaz' => 'مركز الفيوم', 'lat' => 29.3084, 'lng' => 30.8428],
        ['governorate' => 'الفيوم', 'markaz' => 'مركز سنورس', 'lat' => 29.4100, 'lng' => 30.8700],
        ['governorate' => 'الفيوم', 'markaz' => 'مركز إطسا', 'lat' => 29.2300, 'lng' => 30.7800],

        // المنيا
        ['governorate' => 'المنيا', 'markaz' => 'مركز المنيا', 'lat' => 28.1099, 'lng' => 30.7503],
        ['governorate' => 'المنيا', 'markaz' => 'مركز ملوي', 'lat' => 27.7300, 'lng' => 30.8400],
        ['governorate' => 'المنيا', 'markaz' => 'مركز مغاغة', 'lat' => 28.6500, 'lng' => 30.8400],

        // أسيوط
        ['governorate' => 'أسيوط', 'markaz' => 'مركز أسيوط', 'lat' => 27.1809, 'lng' => 31.1837],
        ['governorate' => 'أسيوط', 'markaz' => 'مركز ديروط', 'lat' => 27.5600, 'lng' => 30.8100],

        // سوهاج
        ['governorate' => 'سوهاج', 'markaz' => 'مركز سوهاج', 'lat' => 26.5591, 'lng' => 31.6957],
        ['governorate' => 'سوهاج', 'markaz' => 'مركز طهطا', 'lat' => 26.7700, 'lng' => 31.5000],

        // قنا والأقصر وأسوان
        ['governorate' => 'قنا', 'markaz' => 'مركز قنا', 'lat' => 26.1551, 'lng' => 32.7160],
        ['governorate' => 'الأقصر', 'markaz' => 'مدينة الأقصر', 'lat' => 25.6872, 'lng' => 32.6396],
        ['governorate' => 'أسوان', 'markaz' => 'مركز أسوان', 'lat' => 24.0889, 'lng' => 32.8998],
    ];

    /**
     * تحديد المركز والمحافظة بدقة عند توفر إحداثيات GPS.
     */
    public function resolveFromCoordinates(float $lat, float $lng): array
    {
        // 1. محاولة استخدام Google Maps Reverse Geocoding إن توفر المفتاح
        $googleResult = $this->reverseGeocodeWithGoogle($lat, $lng);
        if ($googleResult !== null) {
            $googleResult['governorate'] = $this->cleanText($googleResult['governorate'] ?? null);
            $googleResult['city_or_markaz'] = $this->cleanText($googleResult['city_or_markaz'] ?? null);
            $googleResult['district'] = $this->cleanText($googleResult['district'] ?? null);
            $googleResult['formatted_address'] = $this->cleanText($googleResult['formatted_address'] ?? null);
            return $googleResult;
        }

        // 2. البحث عن أقرب مركز ومحافظة مصرية باستخدام خوارزمية Haversine
        $closest = null;
        $minDistance = INF;

        foreach (self::$egyptMarkazes as $item) {
            $dist = $this->haversineDistance($lat, $lng, $item['lat'], $item['lng']);
            if ($dist < $minDistance) {
                $minDistance = $dist;
                $closest = $item;
            }
        }

        if ($closest) {
            return [
                'country_code' => 'EG',
                'country_name' => 'مصر',
                'governorate' => $closest['governorate'],
                'city_or_markaz' => $closest['markaz'],
                'district' => null,
                'formatted_address' => $closest['governorate'] . ' - ' . $closest['markaz'],
                'latitude' => $lat,
                'longitude' => $lng,
                'location_source' => 'gps',
            ];
        }

        return [
            'country_code' => 'EG',
            'country_name' => 'مصر',
            'governorate' => 'بني سويف',
            'city_or_markaz' => 'مدينة بني سويف',
            'district' => null,
            'formatted_address' => 'بني سويف - مصر',
            'latitude' => $lat,
            'longitude' => $lng,
            'location_source' => 'gps',
        ];
    }

    /**
     * تحديد المركز والمحافظة التقريبية بناءً على عنوان الـ IP.
     */
    public function resolveFromIp(string $ip): array
    {
        // التحقق مما إذا كان IP محلياً (Local / Private network)
        if ($this->isLocalOrPrivateIp($ip)) {
            return [
                'country_code' => 'EG',
                'country_name' => 'مصر',
                'governorate' => 'بني سويف',
                'city_or_markaz' => 'مدينة بني سويف',
                'district' => 'موقع محلي تجريبي',
                'formatted_address' => 'بني سويف - مدينة بني سويف (شبكة داخلية)',
                'latitude' => 29.0661,
                'longitude' => 31.0994,
                'location_source' => 'ip',
            ];
        }

        $cacheKey = 'geo_ip_' . md5($ip);
        return Cache::remember($cacheKey, 86400, function () use ($ip) {
            try {
                $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}?lang=ar&fields=status,country,countryCode,regionName,city,district,lat,lon,query");
                if ($response->successful()) {
                    $data = $response->json();
                    if (($data['status'] ?? '') === 'success') {
                        $lat = (float) ($data['lat'] ?? 29.0661);
                        $lon = (float) ($data['lon'] ?? 31.0994);

                        // مطابقة المحافظة والمركز المصري بدقة من الإحداثيات
                        $matched = $this->resolveFromCoordinates($lat, $lon);

                        return [
                            'country_code' => $data['countryCode'] ?? 'EG',
                            'country_name' => $data['country'] ?? 'مصر',
                            'governorate' => $data['regionName'] ?: $matched['governorate'],
                            'city_or_markaz' => $matched['city_or_markaz'] ?: ($data['city'] ?? null),
                            'district' => $data['district'] ?? null,
                            'formatted_address' => ($data['regionName'] ?? '') . ' - ' . ($data['city'] ?? ''),
                            'latitude' => $lat,
                            'longitude' => $lon,
                            'location_source' => 'ip',
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::debug("IP Geolocation failed for {$ip}: " . $e->getMessage());
            }

            return [
                'country_code' => 'EG',
                'country_name' => 'مصر',
                'governorate' => 'بني سويف',
                'city_or_markaz' => 'مدينة بني سويف',
                'district' => null,
                'formatted_address' => 'مصر - بني سويف',
                'latitude' => 29.0661,
                'longitude' => 31.0994,
                'location_source' => 'ip',
            ];
        });
    }

    /**
     * استعلام Google Geocoding API للعناوين الإدارية الدقيقة في مصر.
     */
    protected function reverseGeocodeWithGoogle(float $lat, float $lng): ?array
    {
        $apiKey = env('GOOGLE_MAPS_API_KEY') ?: config('services.google.maps_api_key');
        if (!$apiKey) {
            return null;
        }

        $cacheKey = "geo_coords_" . round($lat, 4) . "_" . round($lng, 4);

        return Cache::remember($cacheKey, 86400, function () use ($lat, $lng, $apiKey) {
            try {
                $response = Http::timeout(4)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                    'latlng' => "{$lat},{$lng}",
                    'key' => $apiKey,
                    'language' => 'ar',
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    if (($json['status'] ?? '') === 'OK' && !empty($json['results'])) {
                        $res = $json['results'][0];
                        $components = $res['address_components'] ?? [];

                        $gov = null;
                        $markaz = null;
                        $district = null;
                        $country = 'مصر';
                        $countryCode = 'EG';

                        foreach ($components as $c) {
                            $types = $c['types'] ?? [];
                            if (in_array('country', $types)) {
                                $country = $this->cleanText($c['long_name']);
                                $countryCode = $this->cleanText($c['short_name']);
                            }
                            if (in_array('administrative_area_level_1', $types)) {
                                $gov = $this->cleanText(str_replace(['محافظة ', ' Governorate'], '', $c['long_name']));
                            }
                            if (in_array('administrative_area_level_2', $types) || in_array('locality', $types)) {
                                $markaz = $this->cleanText($c['long_name']);
                            }
                            if (in_array('sublocality', $types) || in_array('neighborhood', $types)) {
                                $district = $this->cleanText($c['long_name']);
                            }
                        }

                        return [
                            'country_code' => $countryCode,
                            'country_name' => $country,
                            'governorate' => $gov ?: 'بني سويف',
                            'city_or_markaz' => $markaz ?: 'مدينة بني سويف',
                            'district' => $district,
                            'formatted_address' => $this->cleanText($res['formatted_address'] ?? null),
                            'latitude' => $lat,
                            'longitude' => $lng,
                            'location_source' => 'gps',
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::debug("Google reverse geocode failed: " . $e->getMessage());
            }

            return null;
        });
    }

    /**
     * تنظيف العلامات الخفية وتوجيه النصوص العربية.
     */
    protected function cleanText(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        $cleaned = preg_replace('/[\x{200E}\x{200F}\x{202A}-\x{202E}]/u', '', $text);
        return trim((string) $cleaned);
    }

    /**
     * حساب المسافة بالكيلومترات بين نقطتين جغرافيتين (Haversine formula).
     */
    protected function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    protected function isLocalOrPrivateIp(string $ip): bool
    {
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost'], true)) {
            return true;
        }

        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
}
