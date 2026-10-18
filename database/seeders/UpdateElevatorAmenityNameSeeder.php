<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class UpdateElevatorAmenityNameSeeder extends Seeder
{
    public function run(): void
    {
        $elevator = Amenity::query()->where('key', 'elevator')->first();

        if (! $elevator) {
            return;
        }

        $elevator->translations()->updateOrCreate(
            ['locale' => 'ar'],
            ['name' => 'مصعد كهربائي']
        );

        $elevator->translations()->updateOrCreate(
            ['locale' => 'en'],
            ['name' => 'Electric elevator']
        );
    }
}
