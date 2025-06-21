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
            
        Event::firstOrCreate(
            ['name' => 'The World Faire of Whispering Isles'],
            [
                'name' => 'The World Faire of Whispering Isles',
                'address' => 'Prestantstraat 27 Maastricht',
                'start_date' => now()->addDays(30),
                'end_date' => now()->addDays(32),
            ]
        );
    }
}
