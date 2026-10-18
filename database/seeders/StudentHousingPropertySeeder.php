<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StudentHousingPropertySeeder extends Seeder
{
    public function run(): void
    {
        $currency = Currency::query()->where('code', 'EGP')->firstOrFail();
        $category = PropertyCategory::query()->where('key', 'apartment')->first()
            ?? PropertyCategory::query()->findOrFail(1);
        $location = Location::query()
            ->whereHas('translations', fn ($query) => $query->where('locale', 'ar')->where('name', 'like', '%بني سويف%'))
            ->first() ?? Location::query()->firstOrFail();
        $owner = User::query()->whereNotNull('email_verified_at')->first() ?? User::query()->firstOrFail();

        $properties = [
            [
                'reference_code' => 'DARLI-STUDENT-001',
                'price' => 3500,
                'bedrooms' => 2,
                'bathrooms' => 1,
                'area' => 85,
                'latitude' => 29.0661,
                'longitude' => 31.0994,
                'name' => 'شقة مفروشة للإيجار الطلابي قرب جامعة بني سويف',
                'slug' => 'شقة-مفروشة-للإيجار-الطلابي-قرب-جامعة-بني-سويف',
                'address' => 'شرق النيل، بالقرب من جامعة بني سويف',
            ],
            [
                'reference_code' => 'DARLI-STUDENT-002',
                'price' => 2800,
                'bedrooms' => 2,
                'bathrooms' => 1,
                'area' => 75,
                'latitude' => 29.0712,
                'longitude' => 31.0951,
                'name' => 'سكن طلاب هادئ ومجهز في بني سويف الجديدة',
                'slug' => 'سكن-طلاب-مجهز-في-بني-سويف-الجديدة',
                'address' => 'بني سويف الجديدة، شرق النيل',
            ],
            [
                'reference_code' => 'DARLI-STUDENT-003',
                'price' => 4200,
                'bedrooms' => 3,
                'bathrooms' => 2,
                'area' => 110,
                'latitude' => 29.0737,
                'longitude' => 31.1018,
                'name' => 'شقة مشتركة للطلاب بجوار الخدمات والمواصلات',
                'slug' => 'شقة-مشتركة-للطلاب-بجوار-الخدمات-والمواصلات',
                'address' => 'شرق النيل، بني سويف',
            ],
        ];

        foreach ($properties as $index => $data) {
            $property = Property::query()->updateOrCreate(
                ['reference_code' => $data['reference_code']],
                [
                    'uuid' => (string) Str::uuid(),
                    'owner_id' => $owner->id,
                    'created_by' => $owner->id,
                    'managed_by' => $owner->id,
                    'category_id' => $category->id,
                    'location_id' => $location->id,
                    'currency_id' => $currency->id,
                    'purpose' => 'rent',
                    'status' => 'published',
                    'price' => $data['price'],
                    'price_type' => 'fixed',
                    'bedrooms' => $data['bedrooms'],
                    'bathrooms' => $data['bathrooms'],
                    'area' => $data['area'],
                    'area_unit' => 'sqm',
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'published_at' => now()->subMinutes($index),
                ]
            );

            $property->translations()->updateOrCreate(
                ['locale' => 'ar'],
                [
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'short_description' => 'سكن طلابي مفروش وجاهز للإقامة الشهرية بالقرب من الجامعة والخدمات.',
                    'description' => 'شقة مخصصة للإيجار الطلابي، مفروشة وجاهزة للسكن، في موقع قريب من الجامعة والمواصلات والخدمات اليومية.',
                    'address_text' => $data['address'],
                ]
            );
        }
    }
}
