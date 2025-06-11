<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;
use App\Models\Event;

class LocationSeeder extends Seeder
{
    public function run()
    {
        $locationNames = [
            'The Pentagon',
            'The Astra Realm',
            'Chromatic Caverns',
            'Dreamscape Docks',
            'Echo Bloom',
            'The Glimmerwood',
            'Harmonic Haven',
            'The Luminous Labyrinth',
            'Mythic Meadows',
            'Neon Nook',
            'The Orbital Oasis',            
            'Phantom Phantasia',
            'Rhythm Rift',            
            'The Shimmering Sanctuary',
            'Sonic Spire',
            'Stardust Summit',
            'Terra Tempo',
            'The Verdant Vortex',
            'Whispering Willows',
            'Zenith Zone',
            'The Celestial Canvas',
            'The Enigmatic Enclave',
            'The Harmonious Haven',
            'The Mystic Maelstrom',
            'The Radiant Realm',
            'The Serene Sanctuary',
            'The Sparkling Spire',
        ];

        // Create locations first
        $locations = [];
        foreach ($locationNames as $locationName) {
            $location = Location::firstOrCreate([
                'name' => $locationName,
                'description' => 'A magical venue for events',
            ]);
            $locations[] = $location;
        }

        // Create the event with a location_id
        if (!empty($locations)) {
            $firstLocation = $locations[0]; // Use the first location for the event
            
            Event::firstOrCreate(
                ['name' => 'The World Faire of Whispering Isles'],
                [
                    'name' => 'The World Faire of Whispering Isles',
                    'location_id' => $firstLocation->id,
                    'start_date' => now()->addDays(30),
                    'end_date' => now()->addDays(32),
                ]
            );
        }
    }
}
