<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\Currency;
use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CornicheYasmineApartmentSeeder extends Seeder
{
    /**
     * Run the database seeds for the Corniche Al-Yasmine Tower Apartment.
     */
    public function run(): void
    {
        // 1. Resolve Currency (EGP or fallback to first)
        $currency = Currency::where('code', 'EGP')->first() ?? Currency::first();

        // 2. Resolve Category (Apartments / شقق)
        $category = PropertyCategory::find(1) ?? PropertyCategory::first();

        // 3. Resolve Location (مدينة بني سويف)
        $location = Location::find(31) ?? Location::first();

        // 4. Resolve Owner / Agent
        $owner = User::find(79) ?? User::first();

        // 5. Create or Update Amenities (Elevator, Natural Gas, etc.)
        $elevator = Amenity::firstOrCreate(
            ['key' => 'elevator'],
            ['icon' => 'icon-elevator', 'status' => 1]
        );
        $elevator->translations()->updateOrCreate(
            ['locale' => 'ar'],
            ['name' => 'مصعد كهربائي (2 أسانسير)']
        );
        $elevator->translations()->updateOrCreate(
            ['locale' => 'en'],
            ['name' => 'Elevator (2 modern lifts)']
        );

        $gas = Amenity::firstOrCreate(
            ['key' => 'natural-gas'],
            ['icon' => 'icon-gas', 'status' => 1]
        );
        $gas->translations()->updateOrCreate(
            ['locale' => 'ar'],
            ['name' => 'غاز طبيعي وعدادات متكاملة']
        );
        $gas->translations()->updateOrCreate(
            ['locale' => 'en'],
            ['name' => 'Natural Gas & Full Utilities']
        );

        // 6. Create or Update the Property
        $referenceCode = 'DARLI-BNS-YASMINE-180';

        $property = Property::updateOrCreate(
            ['reference_code' => $referenceCode],
            [
                'uuid' => (string) Str::uuid(),
                'owner_id' => $owner?->id,
                'created_by' => $owner?->id,
                'managed_by' => $owner?->id,
                'agency_id' => 1,
                'category_id' => $category?->id,
                'location_id' => $location?->id,
                'currency_id' => $currency?->id,
                'purpose' => 'sale',
                'status' => 'published',
                'price' => 0,
                'price_type' => 'contact',
                'price_notes' => 'السعر عند التواصل - متاح تفاوض وتسهيلات',
                'bedrooms' => 3,
                'bathrooms' => 2,
                'parking_spaces' => 1,
                'area' => 180.00,
                'land_area' => null,
                'area_unit' => 'sqm',
                'year_built' => 2024,
                'floors_count' => 12,
                'floor_number' => 3,
                'latitude' => 29.0744,
                'longitude' => 31.0978,
                'is_featured' => true,
                'featured_until' => now()->addMonths(6),
                'published_at' => now(),
            ]
        );

        // 7. Property Descriptions (formatted for clean nl2br rendering)
        $arDescription = <<<TEXT
فرصة سكنية واستثمارية استثنائية على كورنيش بني سويف مباشرة؛ شقة فاخرة بمساحة 180 متراً مربعاً بتشطيب فندقي ألترا سوبر لوكس وإطلالة بانورامية ساحرة على نهر النيل في أرقى أبراج الكورنيش (برج الياسمين - بجانب مطعم بابچي).

✨ الموقع والإطلالة:
• العنوان: الكورنيش - برج الياسمين (بجانب مطعم بابچي)، مدينة بني سويف.
• الإطلالة: بلكونة أمامية واسعة تطل على النيل مباشرة بدون أي حواجب.
• الطابق: الدور الثالث (تاني بلكونة) - موقع مثالي يجمع بين الخصوصية والرؤية المباشرة للنهر.

📐 المساحة والتقسيم الداخلي:
• المساحة: 180 متراً مربعاً صافياً مستغلة بأعلى كفاءة هندسية.
• غرف النوم: 3 غرف نوم واسعة ومريحة مع تهوية وإضاءة طبيعية ممتازة.
• الحمامات: 2 حمام بتشطيبات مودرن وأطقم صحية مستوردة وكابينة شاور زجاجية.
• الريسبشن: صالة استقبال كبيرة تتسع لعدة قطع مع إضاءات ليد مخفية وديكورات جدارية راقية.
• المطبخ: مطبخ أمريكان عصري (بار مفتوح مع إمكانية تقفيله بسهولة).

💎 التشطيب والمرافق:
• مستوى التشطيب: ألترا سوبر لوكس وجاهزة للسكن الفوري.
• العدادات المتوفرة: عداد مياه مستقل، عداد كهرباء، وعداد غاز طبيعي.
• التكييفات: تأسيس كامل وجاهز لجميع التكييفات بالمواسير والوصلات المدفونة.

🏢 خدمات ومميزات البرج:
• المصاعد: 2 مصعد كهربائي حديث (أسانسير) بحمولة واسعة وسرعة عالية.
• الأمن والحراسة: نظام أمني متكامل وحراسة على مدار 24 ساعة لراحة وسلامة السكان.
• خدمات المبنى: خدمات نظافة دورية للمدخل والممرات وإدارة محترفة للعقار.
TEXT;

        $enDescription = <<<TEXT
An exceptional residential and investment opportunity directly on the Beni Suef Nile Corniche; a luxury 180 sqm apartment with ultra super lux finishing and a breathtaking panoramic view of the Nile River in one of the most prestigious towers on the Corniche (Al-Yasmine Tower - next to Babji Restaurant).

✨ Location & View:
• Address: Corniche - Al-Yasmine Tower (next to Babji restaurant), Beni Suef City.
• View: Front balcony with direct, unobstructed view of the River Nile.
• Floor: 3rd Floor (2nd balcony) - ideal height balancing privacy with optimal river sightlines.

📐 Layout & Dimensions:
• Total Area: 180 sqm efficiently planned.
• Bedrooms: 3 spacious bedrooms with great natural ventilation and sunlight.
• Bathrooms: 2 modern bathrooms with premium sanitary ware and glass walk-in shower.
• Reception: Large open reception area with concealed LED ambient lighting and elegant wall moldings.
• Kitchen: Modern American-style kitchen (open bar counter with option to enclose).

💎 Finishing & Utilities:
• Finishing Grade: Ultra Super Lux, turn-key ready for immediate occupancy.
• Meters & Utilities: Water, Electricity, and Natural Gas fully installed and active.
• Air Conditioning: Pre-installed copper piping and drainage infrastructure for AC units in all rooms.

🏢 Tower Amenities:
• Elevators: 2 high-speed modern passenger elevators.
• Security: 24/7 on-site security and surveillance for resident peace of mind.
• Maintenance: Regular housekeeping for communal areas and dedicated building management.
TEXT;

        // Arabic Translation
        $property->translations()->updateOrCreate(
            ['locale' => 'ar'],
            [
                'name' => 'شقة الترا سوبر لوكس 180م بإطلالة مباشرة على النيل - برج الياسمين كورنيش بني سويف',
                'slug' => 'apartment-180m-nile-view-yasmine-tower-corniche-beni-suef',
                'short_description' => 'شقة فاخرة 180 متر مربع بتشطيب ألترا سوبر لوكس وإطلالة مباشرة على النيل بالدور الثالث في برج الياسمين بجانب مطعم بابچي على كورنيش بني سويف.',
                'description' => $arDescription,
                'address_text' => 'الكورنيش - برج الياسمين (بجانب مطعم بابچي)، مدينة بني سويف',
                'seo_title' => 'شقة 180م على نيل بني سويف مباشرة - برج الياسمين الكورنيش | دارلي',
                'seo_description' => 'شقة للبيع 180م بتشطيب الترا سوبر لوكس وريسبشن كبير و3 غرف و2 حمام بإطلالة نيلية ساحرة في برج الياسمين بكورنيش بني سويف.',
            ]
        );

        // English Translation
        $property->translations()->updateOrCreate(
            ['locale' => 'en'],
            [
                'name' => 'Ultra Super Lux 180m Apartment with Direct Nile View - Al-Yasmine Tower, Beni Suef Corniche',
                'slug' => 'en-apartment-180m-nile-view-yasmine-tower-corniche-beni-suef',
                'short_description' => 'Luxury 180 sqm apartment with ultra super lux finish and direct Nile view on the 3rd floor of Al-Yasmine Tower, next to Babji on Beni Suef Corniche.',
                'description' => $enDescription,
                'address_text' => 'Corniche - Al-Yasmine Tower (next to Babji restaurant), Beni Suef City',
                'seo_title' => '180m Luxury Apartment directly on the Nile - Al-Yasmine Tower | Darlii',
                'seo_description' => 'Luxury 180 sqm apartment for sale with direct Nile view, 3 bedrooms, 2 bathrooms, American kitchen in Al-Yasmine Tower, Beni Suef Corniche.',
            ]
        );

        // 8. Attach Amenities
        $amenityKeys = ['balcony', 'security', 'air-conditioning', 'elevator', 'natural-gas', 'wifi'];
        $amenityIds = Amenity::whereIn('key', $amenityKeys)->pluck('id')->all();
        $property->amenities()->sync($amenityIds);

        // 9. Attach Media Gallery
        $mediaImages = [
            [
                'path' => 'properties/corniche-yasmine-1.jpg',
                'sort_order' => 0,
                'is_primary' => true,
                'caption' => 'الريسبشن الفاخر وتشطيبات البورسلين والإضاءات الليد المخفية',
            ],
            [
                'path' => 'properties/corniche-yasmine-2.jpg',
                'sort_order' => 1,
                'is_primary' => false,
                'caption' => 'الحمام المودرن بتصميم فندقي وكابينة شاور زجاجية ومرآة ليد مضيئة',
            ],
            [
                'path' => 'properties/corniche-yasmine-3.jpg',
                'sort_order' => 2,
                'is_primary' => false,
                'caption' => 'امتداد الصالة والريسبشن وإطلالة على البار وممر الغرف',
            ],
            [
                'path' => 'properties/corniche-yasmine-4.jpg',
                'sort_order' => 3,
                'is_primary' => false,
                'caption' => 'المطبخ الأمريكي المفتوح والمدخل الرئيسي بتشطيب راقٍ',
            ],
        ];

        // Ensure disk is public and files exist
        foreach ($mediaImages as $item) {
            DB::table('property_media')->updateOrInsert(
                [
                    'property_id' => $property->id,
                    'path' => $item['path'],
                ],
                [
                    'type' => 'image',
                    'disk' => 'public',
                    'sort_order' => $item['sort_order'],
                    'is_primary' => $item['is_primary'] ? 1 : 0,
                    'caption' => $item['caption'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command?->info("Corniche Al-Yasmine Tower Apartment seeded successfully! ID: {$property->id}, Ref: {$referenceCode}");
    }
}
