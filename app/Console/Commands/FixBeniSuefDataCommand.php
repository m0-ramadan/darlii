<?php

namespace App\Console\Commands;

use App\Models\Amenity;
use App\Models\Career;
use App\Models\Currency;
use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyCategory;
use App\Models\PropertyMedia;
use App\Models\PropertyType;
use App\Models\Project;
use App\Support\BeniSuefCatalog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * يعيد بناء كل بيانات العقارات والمشروعات على محافظة بني سويف:
 * إحداثيات حقيقية داخل نطاق المحافظة، أوصاف عربية وإنجليزية منطقية،
 * أسعار بالجنيه المصري بحسب سعر المتر في كل مدينة، وبيانات مترابطة.
 *
 * Usage:
 *   php artisan darlii:fix-benisuef-data --dry-run
 *   php artisan darlii:fix-benisuef-data
 */
class FixBeniSuefDataCommand extends Command
{
    protected $signature = 'darlii:fix-benisuef-data
        {--dry-run : اعرض ما سيتم تغييره بدون حفظ}
        {--properties : العقارات فقط}
        {--projects : المشروعات فقط}
        {--locations : المواقع فقط}
        {--amenities : المرافق فقط}';

    protected $description = 'تصحيح بيانات العقارات لتكون داخل محافظة بني سويف ببيانات عربية وإنجليزية منطقية';

    /** @var array<string, int> */
    private array $locationIds = [];

    private bool $dryRun = false;

    /** Images that actually exist under public/legacy/storage/properties. */
    private array $availableImages = [];

    public function handle(): int
    {
        $this->dryRun = (bool) $this->option('dry-run');
        $only = array_filter([
            'properties' => $this->option('properties'),
            'projects' => $this->option('projects'),
            'locations' => $this->option('locations'),
            'amenities' => $this->option('amenities'),
        ]);
        $runAll = $only === [];

        if ($this->dryRun) {
            $this->warn('وضع المعاينة (dry-run): لن يتم حفظ أي تغيير.');
        }

        $this->availableImages = $this->discoverImages();

        if ($runAll || isset($only['locations'])) {
            $this->fixLocations();
        }

        if ($runAll || isset($only['amenities'])) {
            $this->fixAmenities();
            $this->fixCategories();
            $this->fixPropertyTypes();
        }

        // Locations must exist before properties/projects are relinked.
        $this->loadLocationIds();

        if ($runAll || isset($only['projects'])) {
            $this->fixProjects();
        }

        if ($runAll || isset($only['properties'])) {
            $this->fixProperties();
        }

        if ($runAll) {
            $this->fixCareers();
        }

        $this->fixTranslationLocales();

        $this->newLine();
        $this->info('تم الانتهاء. ملخص التحقق:');
        $this->verify();

        return self::SUCCESS;
    }

    // ------------------------------------------------------------------ مواقع
    private function fixLocations(): void
    {
        $this->info('── المواقع والمحافظات ──');

        $governorate = Location::query()->firstOrCreate(
            ['parent_id' => null, 'type' => 'governorate'],
            ['latitude' => BeniSuefCatalog::GOVERNORATE['lat'], 'longitude' => BeniSuefCatalog::GOVERNORATE['lng'], 'country_code' => 'EG', 'status' => true]
        );

        // Find the existing Beni Suef governorate if there is one.
        $beniSuef = Location::query()
            ->where('type', 'governorate')
            ->whereHas('translations', fn ($q) => $q->whereIn('name', ['بني سويف', 'Beni Suef']))
            ->first() ?: $governorate;

        $beniSuef->update([
            'latitude' => BeniSuefCatalog::GOVERNORATE['lat'],
            'longitude' => BeniSuefCatalog::GOVERNORATE['lng'],
            'country_code' => 'EG',
            'status' => true,
        ]);
        $this->upsertLocationTranslation($beniSuef, 'ar', BeniSuefCatalog::GOVERNORATE['ar'], 'beni-suef');
        $this->upsertLocationTranslation($beniSuef, 'en', BeniSuefCatalog::GOVERNORATE['en'], 'beni-suef');
        $this->locationIds['beni-suef'] = $beniSuef->id;
        $this->line("  محافظة: {$beniSuef->translation('ar')?->name} (id={$beniSuef->id})");

        foreach (BeniSuefCatalog::allLocations() as $row) {
            if ($row['key'] === 'beni-suef') {
                continue;
            }

            $parentId = $this->locationIds[$row['parent']] ?? $beniSuef->id;

            // Match on an existing translation slug first (idempotent re-runs),
            // otherwise always create a new row — matching on parent_id+type
            // alone would wrongly reuse a legacy governorate row.
            $location = Location::query()
                ->whereHas('translations', fn ($q) => $q->where('slug', $row['key']))
                ->first();

            if (! $location) {
                $location = new Location(['parent_id' => $parentId, 'type' => $row['type']]);
            }

            $location->fill([
                'parent_id' => $parentId,
                'type' => $row['type'],
                'latitude' => $row['lat'],
                'longitude' => $row['lng'],
                'country_code' => 'EG',
                'status' => true,
            ]);

            if (! $this->dryRun) {
                $location->save();
            }

            $slug = $row['key'];
            $this->upsertLocationTranslation($location, 'ar', $row['ar'], $slug, true);
            $this->upsertLocationTranslation($location, 'en', $row['en'], $slug, true);

            if ($location->exists) {
                $this->locationIds[$row['key']] = $location->id;
            }

            $this->line("  + {$row['type']}: {$row['ar']} / {$row['en']} ({$row['lat']}, {$row['lng']})");
        }
    }

    private function upsertLocationTranslation(Location $location, string $locale, string $name, string $slug, bool $needsId = false): void
    {
        if ($this->dryRun || ($needsId && ! $location->exists)) {
            return;
        }

        $location->translations()->updateOrCreate(['locale' => $locale], ['name' => $name, 'slug' => $slug]);
    }

    private function loadLocationIds(): void
    {
        $this->locationIds = [];

        foreach (BeniSuefCatalog::allLocations() as $row) {
            $location = Location::query()
                ->where('type', $row['type'])
                ->whereHas('translations', fn ($q) => $q->where('slug', $row['key'])->orWhere('name', $row['en']))
                ->first();

            if ($location) {
                $this->locationIds[$row['key']] = $location->id;
            }
        }
    }

    // ------------------------------------------------------------------ مرافق
    private function fixAmenities(): void
    {
        $this->info('── المرافق ──');

        $labels = [
            'wifi' => ['واي فاي', 'Wi-Fi'],
            'parking' => ['موقف سيارات', 'Parking'],
            'swimming-pool' => ['حمام سباحة', 'Swimming pool'],
            'balcony' => ['بلكونة', 'Balcony'],
            'garden' => ['حديقة', 'Garden'],
            'security' => ['أمن وحراسة 24 ساعة', '24/7 security'],
            'fitness-center' => ['صالة ألعاب رياضية', 'Fitness centre'],
            'air-conditioning' => ['تكييف', 'Air conditioning'],
            'central-heating' => ['تدفئة مركزية', 'Central heating'],
            'laundry-room' => ['غرفة غسيل', 'Laundry room'],
            'pets-allowed' => ['يُسمح بالحيوانات الأليفة', 'Pets allowed'],
            'spa-massage' => ['سبا ومساج', 'Spa & massage'],
        ];

        foreach (Amenity::query()->with('translations')->get() as $amenity) {
            $pair = $labels[$amenity->key] ?? [$amenity->key, Str::headline($amenity->key)];

            if ($this->dryRun) {
                $this->line("  ~ {$amenity->key} → {$pair[0]} / {$pair[1]}");

                continue;
            }

            $amenity->translations()->updateOrCreate(['locale' => 'ar'], ['name' => $pair[0]]);
            $amenity->translations()->updateOrCreate(['locale' => 'en'], ['name' => $pair[1]]);
            $amenity->translations()->whereNotIn('locale', ['ar', 'en'])->delete();
        }

        $this->line('  تم تحديث ' . Amenity::count() . ' مرفق بالعربية والإنجليزية.');
    }

    /** Arabic + English names for the property categories. */
    private function fixCategories(): void
    {
        $labels = [
            'apartment' => ['شقق', 'Apartments'],
            'house' => ['بيوت', 'Houses'],
            'villa' => ['فيلات', 'Villas'],
            'condo' => ['وحدات سكنية', 'Condos'],
            'land' => ['أراضي', 'Land'],
            'commercial' => ['تجاري وإداري', 'Commercial & Offices'],
        ];

        $this->info('── التصنيفات ──');

        foreach (PropertyCategory::query()->with('translations')->get() as $category) {
            $pair = $labels[$category->key] ?? [$category->key, Str::headline($category->key)];

            if ($this->dryRun) {
                $this->line("  ~ {$category->key} → {$pair[0]} / {$pair[1]}");

                continue;
            }

            $category->translations()->updateOrCreate(['locale' => 'ar'], ['name' => $pair[0], 'slug' => $category->key . '-ar']);
            $category->translations()->updateOrCreate(['locale' => 'en'], ['name' => $pair[1], 'slug' => $category->key]);
        }

        $this->line('  تم تحديث ' . PropertyCategory::count() . ' تصنيف.');
    }

    /** Arabic + English names for the residential/commercial/land types. */
    private function fixPropertyTypes(): void
    {
        $labels = [
            'residential' => ['سكني', 'Residential'],
            'commercial' => ['تجاري', 'Commercial'],
            'land' => ['أراضي', 'Land'],
        ];

        $this->info('── أنواع العقارات ──');

        foreach (PropertyType::query()->with('translations')->get() as $type) {
            $pair = $labels[$type->key] ?? [$type->key, Str::headline($type->key)];

            if ($this->dryRun) {
                $this->line("  ~ {$type->key} → {$pair[0]} / {$pair[1]}");

                continue;
            }

            $type->translations()->updateOrCreate(['locale' => 'ar'], ['name' => $pair[0]]);
            $type->translations()->updateOrCreate(['locale' => 'en'], ['name' => $pair[1]]);
            $type->translations()->whereNotIn('locale', ['ar', 'en'])->delete();
        }

        $this->line('  تم تحديث ' . PropertyType::count() . ' نوع.');
    }

    // --------------------------------------------------------------- مشروعات
    private function fixProjects(): void
    {
        $this->info('── المشروعات ──');

        $cityKeys = array_keys(BeniSuefCatalog::citiesByKey());
        $compounds = BeniSuefCatalog::compounds();

        foreach (Project::query()->with('translations')->orderBy('id')->get() as $index => $project) {
            $cityKey = $cityKeys[$index % count($cityKeys)];
            $city = BeniSuefCatalog::citiesByKey()[$cityKey];
            $compound = $compounds[$index % count($compounds)];

            [$lat, $lng] = BeniSuefCatalog::jitter($city['lat'], $city['lng'], $index + 3);
            $blocks = 4 + ($index % 9);
            $floors = 4 + ($index % 6);
            $flats = $blocks * $floors * (4 + ($index % 3));

            $arName = $compound['ar'];
            $enName = $compound['en'];

            $arDesc = $this->projectDescriptionAr($arName, $city['ar'], $blocks, $floors, $flats, $index);
            $enDesc = $this->projectDescriptionEn($enName, $city['en'], $blocks, $floors, $flats, $index);

            if ($this->dryRun) {
                $this->line("  ~ #{$project->id} {$project->reference_code} → {$arName} / {$enName} @ {$city['ar']} ({$lat}, {$lng})");

                continue;
            }

            $project->update([
                'location_id' => $this->locationIds[$cityKey] ?? $project->location_id,
                'latitude' => $lat,
                'longitude' => $lng,
                'investor_name' => $this->investorName($index),
                'blocks_count' => $blocks,
                'floors_count' => $floors,
                'flats_count' => $flats,
                'open_sale_date' => now()->subMonths(($index % 10) + 1)->startOfMonth(),
                'completion_date' => now()->addMonths(14 + ($index % 12))->startOfMonth(),
                'status' => 'published',
                'published_at' => $project->published_at ?: now()->subDays(40 - $index),
            ]);

            $project->translations()->updateOrCreate(['locale' => 'ar'], [
                'name' => $arName,
                'slug' => 'mashrou-' . $project->id . '-ar',
                'short_description' => 'مشروع سكني متكامل في ' . $city['ar'] . ' بمساحات متنوعة وخطط سداد مرنة.',
                'description' => $arDesc,
                'seo_title' => $arName . ' — ' . $city['ar'],
                'seo_description' => Str::limit($arDesc, 155),
            ]);

            $project->translations()->updateOrCreate(['locale' => 'en'], [
                'name' => $enName,
                'slug' => $project->translation('en')?->slug ?: 'project-' . $project->id,
                'short_description' => 'An integrated residential development in ' . $city['en'] . ' with varied unit sizes and flexible payment plans.',
                'description' => $enDesc,
                'seo_title' => $enName . ' — ' . $city['en'],
                'seo_description' => Str::limit($enDesc, 155),
            ]);

            $project->translations()->whereNotIn('locale', ['ar', 'en'])->delete();

            // Landmark the project inside the governorate.
            DB::table('project_nearby_places')->where('project_id', $project->id)->delete();
            foreach (array_slice(BeniSuefCatalog::landmarkNamesAr(), $index % 4, 3) as $order => $landmark) {
                DB::table('project_nearby_places')->insert([
                    'project_id' => $project->id,
                    'type' => $landmark,
                    'distance' => round(1.2 + ($order * 0.9) + ($index % 5) * 0.3, 2),
                    'distance_unit' => 'km',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->line('  تم تحديث ' . Project::count() . ' مشروع.');
    }

    // ---------------------------------------------------------------- عقارات
    private function fixProperties(): void
    {
        $this->info('── العقارات ──');

        $cityKeys = array_keys(BeniSuefCatalog::citiesByKey());
        $categoryMap = PropertyCategory::query()->get()->keyBy('key');
        $typeMap = PropertyType::query()->get()->keyBy('key');
        $egpId = Currency::query()->where('code', 'EGP')->value('id');
        $amenityIds = Amenity::query()->pluck('id')->all();

        $streets = BeniSuefCatalog::streets();
        $compounds = BeniSuefCatalog::compounds();
        $finishes = BeniSuefCatalog::finishes();
        $landmarks = BeniSuefCatalog::landmarks();
        $buildYears = BeniSuefCatalog::buildYears();

        $kinds = ['apartment', 'apartment', 'apartment', 'apartment', 'villa', 'commercial', 'land'];

        foreach (Property::query()->with('translations')->orderBy('id')->get() as $index => $property) {
            $cityKey = $cityKeys[$index % count($cityKeys)];
            $city = BeniSuefCatalog::citiesByKey()[$cityKey];
            $kind = $kinds[$index % count($kinds)];
            $forRent = $index % 5 === 4; // roughly one in five listings is a rental

            // ------------------------------------------------------- المساحة
            $area = match ($kind) {
                'villa' => 280 + (($index * 37) % 240),
                'commercial' => 55 + (($index * 29) % 200),
                'land' => 320 + (($index * 53) % 900),
                default => 88 + (($index * 23) % 150),
            };
            $area = round($area, 2);

            // ------------------------------------------------ الغرف والحمامات
            $bedrooms = match ($kind) {
                'commercial' => null,
                'land' => null,
                'villa' => 4 + ($index % 3),
                default => (int) max(1, min(5, round($area / 55))),
            };
            $bathrooms = match ($kind) {
                'commercial' => 1 + ($index % 2),
                'land' => null,
                default => max(1, (int) ceil(($bedrooms ?? 3) / 2)),
            };
            $parking = $kind === 'land' ? null : ($index % 3 === 0 ? 2 : 1);

            // ----------------------------------------------------------- السعر
            $perMetre = BeniSuefCatalog::pricePerMetre($cityKey, $kind, false);
            $perMetre = (int) round($perMetre * (0.94 + (($index % 9) * 0.015)));

            if ($forRent) {
                // للإيجار: السعر المخزَّن هو الإيجار الشهري بالجنيه.
                $price = BeniSuefCatalog::monthlyRent($cityKey, $kind, $bedrooms, $index) ?? 5000;
            } else {
                $price = (int) round(($area * $perMetre) / 1000) * 1000;
            }

            // حماية من أي قيمة غير منطقية.
            if ($price <= 0) {
                $price = $forRent ? 5000 : (int) round($area * 12000);
            }
            if ($forRent && $price > 400000) {
                $price = 400000;
            }
            if (! $forRent && $price < 300000) {
                $price = 300000;
            }

            // ------------------------------------------------------ الإحداثيات
            [$lat, $lng] = BeniSuefCatalog::jitter($city['lat'], $city['lng'], $index + 11);

            // --------------------------------------------------------- المحتوى
            $finish = $finishes[$index % count($finishes)];
            $compound = $compounds[$index % count($compounds)];
            $streetRow = $streets[$index % count($streets)];
            $street = $streetRow['ar'];
            $streetEn = $streetRow['en'];
            // The catalogue is bilingual: keep Arabic landmarks out of English copy.
            $landmarkRow = $landmarks[$index % count($landmarks)];
            $landmark = $landmarkRow['ar'];
            $landmarkEn = $landmarkRow['en'];
            $year = $kind === 'land' ? null : $buildYears[$index % count($buildYears)];
            $floor = in_array($kind, ['apartment', 'commercial'], true) ? 1 + ($index % 7) : ($kind === 'villa' ? 0 : null);
            $floorsCount = $kind === 'villa' ? 2 + ($index % 2) : ($kind === 'commercial' ? 1 + ($index % 3) : null);
            $districtName = $this->districtNameFor($cityKey, $index);

            $arName = $this->propertyNameAr($kind, $bedrooms, $districtName, $forRent, $index);
            $enName = $this->propertyNameEn($kind, $bedrooms, $city['en'], $forRent, $index);

            // Avoid "بني سويف الغربية، بني سويف الغربية" duplication when the
            // district and the city share the same name.
            $addressAr = $districtName === $city['ar']
                ? "{$compound['ar']}، {$street}، {$city['ar']}، محافظة بني سويف"
                : "{$compound['ar']}، {$street}، {$districtName}، {$city['ar']}، محافظة بني سويف";
            $addressEn = "{$compound['en']}, {$streetEn}, {$city['en']}, Beni Suef Governorate, Egypt";

            $arDesc = $this->propertyDescriptionAr($arName, $kind, $area, $bedrooms, $bathrooms, $finish, $city['ar'], $districtName, $landmark, $forRent, $price, $index);
            $enDesc = $this->propertyDescriptionEn($enName, $kind, $area, $bedrooms, $bathrooms, $finish['en'], $city['en'], $landmarkEn, $forRent, $price, $index);

            if ($this->dryRun) {
                $this->line(sprintf(
                    '  ~ #%d %s → %s | %s | %.0f م² | %s ج.م | %s, %s',
                    $property->id, $property->reference_code, $arName, $kind, $area, number_format($price), $lat, $lng
                ));

                continue;
            }

            $property->update([
                'currency_id' => $egpId ?: $property->currency_id,
                'purpose' => $forRent ? 'rent' : 'sale',
                'location_id' => $this->locationIds[$cityKey] ?? $property->location_id,
                'category_id' => $categoryMap[$kind === 'villa' ? 'villa' : ($kind === 'land' ? 'land' : ($kind === 'commercial' ? 'commercial' : 'apartment'))]?->id ?? $property->category_id,
                'property_type_id' => $typeMap[$kind === 'land' ? 'land' : ($kind === 'commercial' ? 'commercial' : 'residential')]?->id ?? $property->property_type_id,
                'price' => $price,
                'area' => $area,
                'land_area' => $kind === 'land' ? $area : null,
                'bedrooms' => $bedrooms,
                'bathrooms' => $bathrooms,
                'parking_spaces' => $parking,
                'year_built' => $year,
                'floor_number' => $floor,
                'floors_count' => $floorsCount,
                'latitude' => $lat,
                'longitude' => $lng,
                'expires_at' => now()->addDays((int) setting('properties.expiry_days', 90)),
            ]);

            // -------------------------------------------------------- الترجمات
            $property->translations()->updateOrCreate(['locale' => 'ar'], [
                'name' => $arName,
                'slug' => 'aqar-' . $property->id . '-ar',
                'short_description' => $this->shortAr($kind, $area, $districtName, $city['ar'], $forRent),
                'description' => $arDesc,
                'address_text' => $addressAr,
                'seo_title' => Str::limit($arName . ' — ' . $city['ar'], 60, ''),
                'seo_description' => Str::limit($arDesc, 155),
            ]);

            $existingEnSlug = $property->translation('en')?->slug;
            $property->translations()->updateOrCreate(['locale' => 'en'], [
                'name' => $enName,
                'slug' => $existingEnSlug ?: 'property-' . $property->id,
                'short_description' => $this->shortEn($kind, $area, $city['en'], $forRent),
                'description' => $enDesc,
                'address_text' => $addressEn,
                'seo_title' => Str::limit($enName . ' — ' . $city['en'], 60, ''),
                'seo_description' => Str::limit($enDesc, 155),
            ]);

            $property->translations()->whereNotIn('locale', ['ar', 'en'])->delete();

            // ---------------------------------------------------------- المرافق
            if ($amenityIds !== []) {
                shuffle($amenityIds);
                $property->amenities()->sync(array_slice($amenityIds, 0, 5 + ($index % 4)));
            }

            // --------------------------------------------------------- المعالم
            DB::table('property_nearby_places')->where('property_id', $property->id)->delete();
            foreach (array_slice(BeniSuefCatalog::landmarkNamesAr(), $index % 5, 3) as $order => $nearby) {
                DB::table('property_nearby_places')->insert([
                    'property_id' => $property->id,
                    'type' => $nearby,
                    'distance' => round(0.4 + ($order * 0.7) + ($index % 6) * 0.2, 2),
                    'distance_unit' => 'km',
                    'travel_time_minutes' => (int) round((0.4 + $order * 0.7) * 3 + 4),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->fixMediaPaths($property, $index);
        }

        $this->line('  تم تحديث ' . Property::count() . ' عقار.');
    }

    /** Repoints legacy media rows to image files that really exist. */
    private function fixMediaPaths(Property $property, int $index): void
    {
        if ($this->availableImages === []) {
            return;
        }

        $media = PropertyMedia::query()->where('property_id', $property->id)->orderBy('sort_order')->get();

        foreach ($media as $position => $row) {
            $file = $this->availableImages[($index + $position) % count($this->availableImages)];
            $row->update(['disk' => 'legacy', 'path' => 'storage/properties/' . $file]);
        }
    }

    // ------------------------------------------------------------- الوظائف
    /**
     * Replaces the generic tech-job listings with real vacancies for a real
     * estate company in Beni Suef, with proper EGP salary bands and Arabic +
     * English descriptions.
     */
    private function fixCareers(): void
    {
        $this->info('── الوظائف ──');

        $egpId = Currency::query()->where('code', 'EGP')->value('id');
        $deadline = now()->addMonths(2)->startOfDay();

        $vacancies = [
            [
                'department' => 'المبيعات',
                'experience_level' => 'خبرة سنة إلى 3 سنوات',
                'salary_min' => 9000, 'salary_max' => 22000,
                'ar_title' => 'سمسار عقارات',
                'en_title' => 'Real Estate Agent',
                'ar_summary' => 'مطلوب سمسار عقارات للعمل داخل محافظة بني سويف على تسويق الوحدات السكنية والتجارية وإتمام التعاقدات.',
                'en_summary' => 'We are hiring a real estate agent to market residential and commercial units across Beni Suef and close deals with clients.',
                'ar_desc' => "المسؤوليات:\n• تسويق الوحدات المعروضة على العملاء وإجراء المعاينات الميدانية.\n• استقبال استفسارات العملاء ومتابعتها حتى إتمام التعاقد.\n• إعداد عروض الأسعار ومقارنتها بأسعار السوق في المحافظة.\n• تحديث بيانات الوحدات على المنصة بشكل يومي.\n\nالمتطلبات:\n• خبرة سنة على الأقل في مجال العقارات أو المبيعات الميدانية.\n• حسن المظهر والقدرة على التفاوض وإقناع العميل.\n• إجادة استخدام الحاسب والمنصة الإلكترونية.\n• رخصة قيادة أو إمكانية التنقل داخل المحافظة.\n\nالمزايا: راتب ثابت يبدأ من 9,000 جنيه، بالإضافة إلى عمولة على كل تعاقد، وتدريب على أنظمة المنصة.",
                'en_desc' => "Responsibilities:\n• Market listed units to clients and run on-site viewings.\n• Handle incoming enquiries and follow up until the contract is signed.\n• Prepare quotations and compare them with local market prices.\n• Keep unit data on the platform up to date.\n\nRequirements:\n• At least one year of experience in real estate or field sales.\n• Strong negotiation and presentation skills.\n• Comfortable using a computer and the online platform.\n• A driving licence, or the ability to move around the governorate.\n\nBenefits: a fixed salary starting from 9,000 EGP plus commission on every contract, and full platform training.",
            ],
            [
                'department' => 'المبيعات',
                'experience_level' => 'حديث التخرج',
                'salary_min' => 7000, 'salary_max' => 15000,
                'ar_title' => 'مندوب مبيعات عقارية',
                'en_title' => 'Property Sales Representative',
                'ar_summary' => 'فرصة لخريجي التجارة والحقوق للعمل كمندوب مبيعات عقارية في بني سويف مع تدريب كامل.',
                'en_summary' => 'An opportunity for business and law graduates to join as a property sales representative in Beni Suef with full training.',
                'ar_desc' => "المسؤوليات:\n• التواصل مع العملاء المحتملين الواردة من المنصة والرد على استفساراتهم.\n• ترتيب المواعيد والمعاينات مع السماسرة المسؤولين.\n• تسجيل بيانات العملاء بدقة في نظام إدارة العملاء.\n\nالمتطلبات:\n• مؤهل عالٍ (تجارة أو حقوق أو ما يعادله).\n• مهارات تواصل جيدة ولغة عربية سليمة.\n• الالتزام واجتياز فترة التدريب.\n\nالمزايا: تدريب مدفوع لمدة شهر، راتب يبدأ من 7,000 جنيه، وعمولة شهرية مفتوحة.",
                'en_desc' => "Responsibilities:\n• Contact leads coming from the platform and answer their enquiries.\n• Schedule viewings together with the responsible agents.\n• Record client data accurately in the CRM.\n\nRequirements:\n• A university degree (business, law or equivalent).\n• Good communication skills and fluent Arabic.\n• Commitment and willingness to complete the training period.\n\nBenefits: a paid one-month training programme, a salary from 7,000 EGP, and uncapped monthly commission.",
            ],
            [
                'department' => 'التسويق',
                'experience_level' => 'خبرة 3 إلى 5 سنوات',
                'salary_min' => 15000, 'salary_max' => 30000,
                'ar_title' => 'مدير تسويق عقاري',
                'en_title' => 'Real Estate Marketing Manager',
                'ar_summary' => 'مطلوب مدير تسويق لإدارة الحملات الرقمية للمشروعات العقارية الجديدة في بني سويف.',
                'en_summary' => 'We are looking for a marketing manager to lead digital campaigns for new developments in Beni Suef.',
                'ar_desc' => "المسؤوليات:\n• إعداد خطة تسويق سنوية للمشروعات الجديدة ومتابعة تنفيذها.\n• إدارة حملات فيسبوك وجوجل وإنستجرام وتحسين تكلفة العميل المحتمل.\n• إنتاج المحتوى والتصوير الميداني للوحدات بالتعاون مع فريق التصميم.\n• تحليل نتائج الحملات وتقديم تقرير شهري للإدارة.\n\nالمتطلبات:\n• خبرة 3 سنوات على الأقل، ويُفضّل في القطاع العقاري.\n• إجادة إدارة الميزانيات الإعلانية وأدوات التحليل.\n• مهارات قيادة فريق من 3 أفراد أو أكثر.\n\nالمزايا: راتب من 15,000 إلى 30,000 جنيه حسب الخبرة، وحافز ربع سنوي مرتبط بالأداء.",
                'en_desc' => "Responsibilities:\n• Build the annual marketing plan for new developments and follow it through.\n• Run Facebook, Google and Instagram campaigns and keep the cost per lead under control.\n• Produce content and on-site photography for units together with the design team.\n• Analyse campaign results and submit a monthly report to management.\n\nRequirements:\n• At least three years of experience, preferably in real estate.\n• Strong command of advertising budgets and analytics tools.\n• Experience leading a team of three or more.\n\nBenefits: a salary of 15,000 to 30,000 EGP depending on experience, plus a quarterly performance bonus.",
            ],
            [
                'department' => 'الحسابات',
                'experience_level' => 'خبرة 2 إلى 4 سنوات',
                'salary_min' => 10000, 'salary_max' => 20000,
                'ar_title' => 'محاسب',
                'en_title' => 'Accountant',
                'ar_summary' => 'مطلوب محاسب لمتابعة حسابات التعاقدات والتحصيلات الشهرية الخاصة بالوحدات العقارية.',
                'en_summary' => 'We are hiring an accountant to handle contract accounting and monthly collections for property units.',
                'ar_desc' => "المسؤوليات:\n• تسجيل فواتير العملاء وأقساط التعاقدات ومتابعة التحصيل.\n• إعداد تقارير شهرية بالإيرادات والمتأخرات.\n• متابعة حسابات الملاك والسماسرة وعمولاتهم.\n• التعامل مع مصلحة الضرائب والمستندات الرسمية.\n\nالمتطلبات:\n• بكالوريوس تجارة قسم محاسبة.\n• خبرة سنتين على الأقل، ويُفضّل من عمل في شركة عقارات.\n• إجادة برامج المحاسبة وإكسل.\n\nالمزايا: راتب من 10,000 إلى 20,000 جنيه، وتأمين اجتماعي وطبي.",
                'en_desc' => "Responsibilities:\n• Record client invoices and contract installments and follow up on collections.\n• Prepare monthly revenue and arrears reports.\n• Track owners' and agents' accounts and their commissions.\n• Handle tax authority paperwork and official documents.\n\nRequirements:\n• A degree in accounting.\n• At least two years of experience, ideally with a real estate company.\n• Confident with accounting software and Excel.\n\nBenefits: a salary of 10,000 to 20,000 EGP, with social and medical insurance.",
            ],
            [
                'department' => 'خدمة العملاء',
                'experience_level' => 'خبرة سنة إلى 3 سنوات',
                'salary_min' => 8000, 'salary_max' => 14000,
                'ar_title' => 'مسؤول خدمة عملاء',
                'en_title' => 'Customer Service Officer',
                'ar_summary' => 'مطلوب مسؤول خدمة عملاء للرد على استفسارات العملاء ومتابعة طلبات المعاينة بعد البيع.',
                'en_summary' => 'We are looking for a customer service officer to answer client enquiries and follow up on viewings and after-sales requests.',
                'ar_desc' => "المسؤوليات:\n• الرد على مكالمات ورسائل العملاء وتوجيههم للقسم المختص.\n• متابعة طلبات المعاينة وتأكيد المواعيد.\n• متابعة شكاوى العملاء وحلها في أسرع وقت.\n• إعداد تقرير أسبوعي بمستوى رضا العملاء.\n\nالمتطلبات:\n• صوت واضح وأسلوب لبق في التعامل.\n• خبرة سنة على الأقل في خدمة العملاء.\n• إجادة استخدام الحاسب والواتساب للأعمال.\n\nالمزايا: راتب من 8,000 إلى 14,000 جنيه وحوافز شهرية.",
                'en_desc' => "Responsibilities:\n• Answer client calls and messages and route them to the right department.\n• Follow up viewing requests and confirm appointments.\n• Track client complaints and resolve them quickly.\n• Prepare a weekly customer satisfaction report.\n\nRequirements:\n• A clear telephone manner and a polite, professional attitude.\n• At least one year of customer service experience.\n• Confident with computers and WhatsApp Business.\n\nBenefits: a salary of 8,000 to 14,000 EGP plus monthly incentives.",
            ],
            [
                'department' => 'الهندسة',
                'experience_level' => 'خبرة 3 إلى 6 سنوات',
                'salary_min' => 12000, 'salary_max' => 24000,
                'ar_title' => 'مهندس مدني للمعاينة',
                'en_title' => 'Civil Engineer — Property Inspection',
                'ar_summary' => 'مطلوب مهندس مدني لمعاينة الوحدات وتقييم حالتها الفنية وإعداد تقارير فنية للعملاء.',
                'en_summary' => 'We are hiring a civil engineer to inspect units, assess their technical condition and prepare reports for clients.',
                'ar_desc' => "المسؤوليات:\n• معاينة الوحدات السكنية والتجارية وتقييم الحالة الإنشائية والتشطيبات.\n• قياس المساحات والتحقق من مطابقتها للمستندات.\n• إعداد تقرير فني مصور لكل وحدة قبل العرض على العميل.\n• متابعة أعمال الصيانة والتشطيبات مع المقاولين.\n\nالمتطلبات:\n• بكالوريوس هندسة مدنية.\n• خبرة 3 سنوات على الأقل في التنفيذ أو الإشراف.\n• إجادة أوتوكاد وقراءة الرسومات الهندسية.\n• رخصة قيادة للتنقل بين المواقع داخل المحافظة.\n\nالمزايا: راتب من 12,000 إلى 24,000 جنيه، وسيارة عمل، وتأمين طبي.",
                'en_desc' => "Responsibilities:\n• Inspect residential and commercial units and assess structural and finishing condition.\n• Measure areas and verify them against the paperwork.\n• Produce an illustrated technical report for each unit before it is shown to a client.\n• Supervise maintenance and finishing works with contractors.\n\nRequirements:\n• A degree in civil engineering.\n• At least three years of execution or supervision experience.\n• Confident with AutoCAD and reading engineering drawings.\n• A driving licence to move between sites in the governorate.\n\nBenefits: a salary of 12,000 to 24,000 EGP, a company car and medical insurance.",
            ],
        ];

        foreach (Career::query()->orderBy('id')->get() as $position => $career) {
            $vacancy = $vacancies[$position % count($vacancies)];

            if ($this->dryRun) {
                $this->line("  ~ #{$career->id} → {$vacancy['ar_title']} / {$vacancy['en_title']} ({$vacancy['salary_min']}-{$vacancy['salary_max']} ج.م)");

                continue;
            }

            $career->update([
                'department' => $vacancy['department'],
                'location' => 'بني سويف',
                'employment_type' => 'دوام كامل',
                'experience_level' => $vacancy['experience_level'],
                'salary_min' => $vacancy['salary_min'],
                'salary_max' => $vacancy['salary_max'],
                'currency_id' => $egpId,
                'deadline' => $deadline,
                'status' => 'published',
            ]);

            $existingEnSlug = $career->translation('en')?->slug;

            $career->translations()->updateOrCreate(['locale' => 'ar'], [
                'title' => $vacancy['ar_title'],
                'slug' => 'wazifa-' . $career->id . '-ar',
                'summary' => $vacancy['ar_summary'],
                'description' => $vacancy['ar_desc'],
                'seo_title' => Str::limit($vacancy['ar_title'] . ' — بني سويف', 60, ''),
                'seo_description' => Str::limit($vacancy['ar_summary'], 155),
            ]);

            $career->translations()->updateOrCreate(['locale' => 'en'], [
                'title' => $vacancy['en_title'],
                'slug' => $existingEnSlug ?: 'career-' . $career->id,
                'summary' => $vacancy['en_summary'],
                'description' => $vacancy['en_desc'],
                'seo_title' => Str::limit($vacancy['en_title'] . ' — Beni Suef', 60, ''),
                'seo_description' => Str::limit($vacancy['en_summary'], 155),
            ]);

            $career->translations()->whereNotIn('locale', ['ar', 'en'])->delete();
        }

        $this->line('  تم تحديث ' . Career::count() . ' وظيفة.');
    }

    /** Removes translation rows for locales the platform does not serve. */
    private function fixTranslationLocales(): void
    {
        if ($this->dryRun) {
            return;
        }

        $this->info('── لغات غير مدعومة ──');

        $tables = [
            'property_translations' => 'property_id',
            'project_translations' => 'project_id',
            'location_translations' => 'location_id',
            'amenity_translations' => 'amenity_id',
            'property_category_translations' => 'property_category_id',
            'testimonial_translations' => 'testimonial_id',
        ];

        foreach ($tables as $table => $column) {
            $deleted = DB::table($table)->whereNotIn('locale', ['ar', 'en'])->delete();
            if ($deleted > 0) {
                $this->line("  حذف {$deleted} صف من {$table} (لغات غير مدعومة).");
            }
        }
    }

    // ---------------------------------------------------------------- تحقق
    private function verify(): void
    {
        $outOfBounds = Property::query()
            ->whereNotNull('latitude')
            ->get(['id', 'latitude', 'longitude'])
            ->filter(fn ($p) => ! BeniSuefCatalog::containsPoint((float) $p->latitude, (float) $p->longitude));

        $nested = str_contains(DB::table('property_translations')->where('locale', 'ar')->value('slug') ?? '', 'ar');
        $arabicOk = DB::table('property_translations')->where('locale', 'ar')
            ->get(['description'])
            ->filter(fn ($row) => $this->latinRatio((string) $row->description) > 0.25)
            ->count();

        $this->line('  العقارات: ' . Property::count()
            . ' | داخل بني سويف: ' . (Property::count() - $outOfBounds->count())
            . ' | خارج النطاق: ' . $outOfBounds->count());
        $this->line('  المشروعات: ' . Project::count()
            . ' | داخل بني سويف: ' . Project::query()->whereNotNull('latitude')->get(['latitude', 'longitude'])
                ->filter(fn ($p) => BeniSuefCatalog::containsPoint((float) $p->latitude, (float) $p->longitude))->count());
        $this->line('  ترجمات عربية فيها نص إنجليزي كثير: ' . $arabicOk);
        $this->line('  مواقع بني سويف: ' . Location::count());
        $this->line('  عقارات بعملة غير الجنيه: ' . Property::query()->where('currency_id', '!=', Currency::query()->where('code', 'EGP')->value('id'))->count());

        if ($outOfBounds->isNotEmpty()) {
            $this->warn('  إحداثيات خارج النطاق: ' . $outOfBounds->pluck('id')->join(', '));
        }

        if ($nested === false) {
            // informational only
        }
    }

    private function latinRatio(string $value): float
    {
        $letters = preg_replace('/[^\p{L}]/u', '', $value) ?: '';
        if ($letters === '') {
            return 0.0;
        }

        $latin = preg_replace('/[^\x{0041}-\x{007A}]/u', '', $letters) ?: '';

        return mb_strlen($latin) / max(1, mb_strlen($letters));
    }

    /** @return list<string> */
    private function discoverImages(): array
    {
        $dir = public_path('legacy/storage/properties');
        if (! is_dir($dir)) {
            return [];
        }

        $files = array_values(array_filter(
            scandir($dir) ?: [],
            fn (string $file) => (bool) preg_match('/\.(jpe?g|png|webp)$/i', $file)
        ));

        sort($files);

        return $files;
    }

    // ------------------------------------------------------------ نصوص عربية
    private function districtNameFor(string $cityKey, int $index): string
    {
        $districts = BeniSuefCatalog::districtsByParent()[$cityKey] ?? [];

        if ($districts === []) {
            $city = BeniSuefCatalog::citiesByKey()[$cityKey];

            return $city['ar'];
        }

        return $districts[$index % count($districts)]['ar'];
    }

    private function propertyNameAr(string $kind, ?int $bedrooms, string $district, bool $forRent, int $index): string
    {
        $prefix = match ($kind) {
            'villa' => 'فيلا مستقلة',
            'commercial' => 'وحدة تجارية',
            'land' => 'قطعة أرض',
            default => $bedrooms ? "شقة {$bedrooms} غرف" : 'شقة',
        };

        $suffix = match (true) {
            $kind === 'land' => 'بموقع مميز',
            $forRent => 'للإيجار',
            $index % 4 === 0 => 'بفيو مفتوح',
            $index % 4 === 1 => 'قريبة من الخدمات',
            $index % 4 === 2 => 'بأفضل سعر في المنطقة',
            default => 'استلام فوري',
        };

        return "{$prefix} – {$district} {$suffix}";
    }

    private function propertyNameEn(string $kind, ?int $bedrooms, string $city, bool $forRent, int $index): string
    {
        $prefix = match ($kind) {
            'villa' => 'Standalone Villa',
            'commercial' => 'Commercial Unit',
            'land' => 'Land Plot',
            default => $bedrooms ? "{$bedrooms}-Bedroom Apartment" : 'Apartment',
        };

        $suffix = $forRent ? 'for Rent' : ($index % 2 === 0 ? 'for Sale' : 'with Open View');

        return "{$prefix} in {$city} — {$suffix}";
    }

    private function shortAr(string $kind, float $area, string $district, string $city, bool $forRent): string
    {
        $purpose = $forRent ? 'للإيجار' : 'للبيع';
        $type = match ($kind) {
            'villa' => 'فيلا مستقلة',
            'commercial' => 'وحدة تجارية',
            'land' => 'قطعة أرض',
            default => 'شقة',
        };

        return "{$type} {$purpose} بمساحة " . number_format($area) . " م² في {$district}، {$city} — موقع قريب من الخدمات والمواصلات.";
    }

    private function shortEn(string $kind, float $area, string $city, bool $forRent): string
    {
        $purpose = $forRent ? 'for rent' : 'for sale';
        $type = ucfirst($kind === 'land' ? 'land plot' : $kind);

        return "{$type} {$purpose} spanning " . number_format($area) . " m² in {$city}, close to schools, shops and public transport.";
    }

    private function propertyDescriptionAr(
        string $name,
        string $kind,
        float $area,
        ?int $bedrooms,
        ?int $bathrooms,
        array $finish,
        string $city,
        string $district,
        string $landmark,
        bool $forRent,
        int $price,
        int $index
    ): string {
        $rooms = match ($kind) {
            'commercial' => 'مساحة مفتوحة مناسبة للمكاتب أو المحلات',
            'land' => 'أرض فضاء جاهزة للبناء',
            default => ($bedrooms ? "{$bedrooms} غرف نوم" : 'غرفة واحدة') . ($bathrooms ? " و{$bathrooms} حمام" : ''),
        };

        $purposeLine = $forRent
            ? 'متاحة للإيجار بقيمة ' . number_format($price) . ' جنيه شهرياً، ويُطلب تأمين شهر وإيجار شهر مقدم.'
            : 'معروضة للبيع بسعر ' . number_format($price) . ' جنيه مصري، وسعر المتر في المنطقة يتناسب مع الموقع والخدمات المتاحة.';

        $lifestyle = [
            "الوحدة مناسبة جداً للأسرة المصرية لأنها تجمع بين الهدوء وقربها من {$landmark}.",
            "المكان مختار بعناية في {$district} بحيث تكون المدارس والمواصلات في متناول اليد.",
            "تتميز المنطقة بالهدوء النسبي ووجود كل الخدمات اليومية على بعد دقائق.",
            "الموقع يتيح سهولة الوصول إلى {$landmark} خلال دقائق بالسيارة.",
        ][$index % 4];

        $investment = [
            'الوحدة فرصة استثمارية جيدة لأن أسعار بني سويف في نمو مستمر مع الطلب المتزايد على السكن.',
            'مناسب للسكن أو لإعادة الإيجار، والعائد المتوقع مجزٍ مقارنة بأسعار الشراء الحالية.',
            'الأسعار في هذا النطاق من الأقل في المحافظة مقابل نفس مستوى التشطيب والخدمات.',
            'الطلب على هذه المنطقة مرتفع بسبب قربها من المشروعات الجديدة والخدمات.',
        ][$index % 4];

        $payment = $forRent
            ? 'يتم التعاقد بعقد إيجار سنوي موثق ويمكن الاتفاق على مدة أقصر حسب رغبة الطرفين.'
            : 'يُقبل التقسيط على دفعات مع مقدم يبدأ من 20%، ويمكن مناقشة التفاصيل خلال المعاينة.';

        $locationLine = $district === $city
            ? "{$name} — وحدة عقارية في مدينة {$city} داخل نطاق محافظة بني سويف."
            : "{$name} — وحدة عقارية في {$district} بمدينة {$city} داخل نطاق محافظة بني سويف.";

        return implode("\n\n", [
            $locationLine,
            "تفاصيل الوحدة: {$rooms}، بمساحة إجمالية " . number_format($area) . " متر مربع، و{$finish['ar']}. الوحدة بها عدادات كهرباء ومياه مستقلة ومدخل مستقل.",
            $lifestyle,
            "الخدمات المحيطة: قريبة من {$landmark}، ومسجد، وصيدلية، ومخبز، وسوبر ماركت، وموقف مواصلات، مما يجعل الحياة اليومية مريحة بدون تنقل طويل.",
            $purposeLine,
            $investment,
            $payment,
            'للمعاينة أو الاستفسار عن التفاصيل والأوراق المطلوبة، تواصل مع فريق دارلي ويمكن ترتيب زيارة في الوقت الذي يناسبك.',
        ]);
    }

    private function propertyDescriptionEn(
        string $name,
        string $kind,
        float $area,
        ?int $bedrooms,
        ?int $bathrooms,
        string $finish,
        string $city,
        string $landmark,
        bool $forRent,
        int $price,
        int $index
    ): string {
        $rooms = match ($kind) {
            'commercial' => 'an open-plan layout suitable for offices or retail',
            'land' => 'a vacant plot ready for construction',
            default => ($bedrooms ? "{$bedrooms} bedrooms" : 'one bedroom') . ($bathrooms ? " and {$bathrooms} bathrooms" : ''),
        };

        $purposeLine = $forRent
            ? 'Offered for rent at ' . number_format($price) . ' EGP per month, with one month deposit and one month in advance.'
            : 'Offered for sale at ' . number_format($price) . ' EGP. The price per square metre reflects the location and the services available nearby.';

        $lifestyle = [
            "The unit suits a family looking for a quiet street while staying a short drive from {$landmark}.",
            "It sits in a well-established part of {$city} where schools, clinics and public transport are all close by.",
            "The neighbourhood is calm and fully served by day-to-day amenities within walking distance.",
            "Access to {$landmark} takes only a few minutes by car.",
        ][$index % 4];

        $note = $forRent
            ? 'A registered annual lease is signed, and shorter terms can be discussed.'
            : 'Installment plans are available with a down payment starting from 20%; terms can be discussed during the viewing.';

        return implode("\n\n", [
            "{$name} — a property located in {$city}, within Beni Suef Governorate.",
            "Unit details: {$rooms}, a total area of " . number_format($area) . " m², and {$finish}. The unit has independent electricity and water meters and its own entrance.",
            $lifestyle,
            "Services nearby: {$landmark}, a mosque, a pharmacy, a bakery, a supermarket and a transport stop — everything needed for comfortable daily living.",
            $purposeLine,
            'The area is growing steadily and demand for housing in Beni Suef keeps rising, which makes this a sound medium-term investment.',
            $note,
            'To arrange a viewing or ask about the required paperwork, contact the Darlii team and we will schedule a visit at your convenience.',
        ]);
    }

    private function projectDescriptionAr(string $name, string $city, int $blocks, int $floors, int $flats, int $index): string
    {
        $usps = [
            'تصميم معماري حديث يوفر تهوية وإضاءة طبيعية لكل وحدة',
            'مساحات خضراء ومناطق لعب للأطفال داخل المشروع',
            'شبكة طرق داخلية ومواقف سيارات كافية لكل عمارة',
            'إدارة للمشروع وأمن على مدار الساعة',
        ];

        return implode("\n\n", [
            "{$name} مشروع سكني متكامل في {$city} بمحافظة بني سويف، مكوّن من {$blocks} عمارة بإجمالي {$floors} دور تقريباً و{$flats} وحدة سكنية بمساحات متنوعة تناسب الأسرة الصغيرة والكبيرة.",
            'يتضمن المشروع ' . implode('، و', array_slice($usps, 0, 3)) . '.',
            'الوحدات تُسلَّم بتشطيبات جيدة ويمكن للعميل اختيار مستوى التشطيب المناسب له، مع إمكانية تعديل التقسيم الداخلي في بعض النماذج.',
            'يقع المشروع على بعد دقائق من الخدمات الأساسية والمدارس والجامعة، وقريب من محور مواصلات رئيسي يربط المدينة بالمدن المجاورة.',
            'نظام السداد مرن: مقدم يبدأ من 15% وتقسيط يصل إلى 6 سنوات بدون فوائد، مع خصم إضافي في حالة السداد النقدي.',
            'المشروع مسجّل ويتم التعاقد بعقود موثقة، ويمكن الحصول على معاينة على الطبيعة في أي وقت بالتنسيق مع فريق دارلي.',
        ]);
    }

    private function projectDescriptionEn(string $name, string $city, int $blocks, int $floors, int $flats, int $index): string
    {
        return implode("\n\n", [
            "{$name} is an integrated residential development in {$city}, Beni Suef Governorate, comprising {$blocks} buildings of roughly {$floors} floors and {$flats} residential units in a range of sizes suited to both small and large families.",
            'The master plan includes landscaped green areas, children playgrounds, internal roads and ample parking for every building.',
            'Units are handed over with good finishes, and buyers can choose the finishing level that suits them; some layouts can be modified to their preference.',
            'The site is minutes from essential services, schools and the university, and sits close to a main transport axis linking the city with its neighbours.',
            'Flexible payment: a down payment starting from 15% with installments up to six years interest-free, plus an extra discount for cash buyers.',
            'The project is officially registered and contracts are documented. A site visit can be arranged at any time through the Darlii team.',
        ]);
    }

    private function investorName(int $index): string
    {
        $investors = [
            'شركة النيل للتطوير العقاري',
            'مجموعة بني سويف للاستثمار',
            'شركة الدلتا العقارية',
            'شركة الأمل للتطوير والاستثمار',
            'مجموعة الصعيد للتعمير',
            'شركة المستقبل العقارية',
        ];

        return $investors[$index % count($investors)];
    }
}
