<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Property;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CityLandmarksSeeder extends Seeder
{
    public function run(): void
    {
        $landmarks = [
            'beni-suef-city' => [['جامعة بني سويف', 'Beni Suef University'], ['كورنيش النيل', 'Nile Corniche'], ['ميدان الشهداء', 'Martyrs Square']],
            'new-beni-suef' => [['جامعة النهضة', 'Nahda University'], ['مجمع الجامعات بشرق النيل', 'East Nile Universities'], ['المنطقة الصناعية', 'Industrial Zone']],
            'west-beni-suef' => [['محطة قطار بني سويف', 'Beni Suef Railway Station'], ['ميدان المديرية', 'El Mudiriya Square']],
            'el-wasta' => [['هرم ميدوم', 'Meidum Pyramid'], ['محطة قطار الواسطى', 'El Wasta Railway Station']],
            'nasser' => [['مستشفى ناصر المركزي', 'Nasser Central Hospital'], ['محطة ناصر', 'Nasser Station']],
            'fashn' => [['مستشفى الفشن المركزي', 'El Fashn Central Hospital'], ['محطة قطار الفشن', 'El Fashn Railway Station']],
            'biba' => [['مستشفى ببا المركزي', 'Biba Central Hospital'], ['محطة قطار ببا', 'Biba Railway Station']],
            'sumusta' => [['مستشفى سمسطا المركزي', 'Sumusta Central Hospital'], ['موقف سمسطا', 'Sumusta Bus Station']],
            'ihnasya' => [['آثار إهناسيا', 'Ihnasya Archaeological Site'], ['مستشفى إهناسيا المركزي', 'Ihnasya Central Hospital']],
        ];

        foreach ($landmarks as $citySlug => $items) {
            $city = Location::query()->whereHas('translations', fn ($query) => $query->where('slug', $citySlug))->first();
            if (! $city) continue;

            $propertyIds = Property::query()
                ->where(function ($query) use ($city) {
                    $query->where('location_id', $city->id)
                        ->orWhereIn('location_id', $city->children()->pluck('id'));
                })
                ->pluck('id');

            foreach ($items as $index => [$ar, $en]) {
                $slug = $citySlug . '-landmark-' . ($index + 1);
                $landmark = Location::query()->whereHas('translations', fn ($query) => $query->where('locale', 'en')->where('slug', $slug))->first()
                    ?? new Location();
                $landmark->fill([
                    'parent_id' => $city->id,
                    'type' => 'landmark',
                    'country_code' => 'EG',
                    'latitude' => $city->latitude,
                    'longitude' => $city->longitude,
                    'status' => true,
                ])->save();
                $landmark->translations()->updateOrCreate(['locale' => 'ar'], ['name' => $ar, 'slug' => $slug . '-ar']);
                $landmark->translations()->updateOrCreate(['locale' => 'en'], ['name' => $en, 'slug' => $slug]);

                foreach ($propertyIds as $propertyId) {
                    DB::table('property_nearby_places')->updateOrInsert(
                        ['property_id' => $propertyId, 'type' => $ar],
                        ['distance' => 0.5 + (($propertyId + $index) % 25) / 10, 'distance_unit' => 'km', 'created_at' => now(), 'updated_at' => now()]
                    );
                }
            }
        }
    }
}
