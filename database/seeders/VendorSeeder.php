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

        $vendors = [
            'Mystic Merchants',
            'Temporal Traders',
            'Celestial Crafts',
            'Echo Emporium',
            'Quantum Quarters',
            'Cosmic Companions',
            'Divine Delights',
            'Ethereal Emporium',
            'Arcane Arts',
            'Starlight Supplies',
            'Nebula Novelties',
            'Astral Artifacts',
            'Galactic Goods',
            'Eclipse Essentials',
            'Lunar Luxuries',
            'Solar Souvenirs',
            'Meteoric Merchandise',
            'Orbital Offerings',
            'Planetary Provisions',
            'Stellar Stores',
            'Comet Collections',
            'Asteroid Accessories',
            'Interstellar Impressions',
            'Galaxy Gifts',
            'Constellation Creations',
            'Space Station Shop',
            'Astronautical Attire',
            'Celestial Curios',
            'Moonlight Market',
            'Sunburst Supplies',
            'Twilight Treasures',
            'Aurora Appearances',
            'Meteor Market',
            'Cosmos Crafts',
            'Nebula Necessities',
            'Astro Artisans',
            'Stardust Selections',
            'Lunar Lifestyles',
            'Solar System Supplies',
            'Black Hole Bazaar',
            'Spacetime Sundries',
            'Alien Antiques',
            'Gravity Goods',
            'Time Travel Treasures',
            'Parallel Provisions',
            'Universal Uniques',
            'Gravity Well Gifts',
            'Wormhole Wonders',
            'Cosmic Cartography',
            'Stellar Stationery',
            'Galactic Gadgets',
            'Interdimensional Imports',
            'Multiverse Mementos',
            'Exotic Exports',
            'Orbital Oddities',
            'Interstellar Instruments',
            'Celestial Collectibles',
        ];

        foreach ($vendors as $name) {
            $location = Location::inRandomOrder()->first();

            Vendor::firstOrCreate([
                'name' => $name,
                'event_id' => $event->id,
                'location_id' => $location?->id,
            ]);
        }
    }
}

