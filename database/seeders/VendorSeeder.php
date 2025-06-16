<?php

namespace Database\Seeders;

use App\Models\Vendor;
use App\Models\Event;
use App\Models\Location;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    public function run()
    {
        $event = Event::first();
        $location = Location::inRandomOrder()->first();

        $vendors = [
            'Mystic Merchants',
            'Temporal Traders',
            'Celestial Crafts',
            'Echo Emporium',
            'Quantum Quarters'
        ];

        foreach ($vendors as $name) {
            Vendor::firstOrCreate([
                'name' => $name,
                'event_id' => $event->id,
                'location_id' => $location?->id,
            ]);
        }
    }
}
