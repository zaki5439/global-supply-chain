<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PortSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = file_get_contents(public_path('ports-complete.json'));
        $ports = json_decode($json, true);
        
        foreach ($ports as $port) {
            $country = \App\Models\Country::where('iso2', $port['countryCode'])->first();
            
            $statusMap = [
                'operational' => 'active',
                'delayed' => 'congested',
                'critical' => 'closed'
            ];
            
            \App\Models\Port::updateOrCreate(
                ['name' => $port['name']],
                [
                    'country_id' => $country ? $country->id : 1,
                    'unlocode' => $port['countryCode'] . rand(100, 999), // Mock unlocode if not present
                    'latitude' => $port['lat'],
                    'longitude' => $port['lng'],
                    'status' => $statusMap[$port['status']] ?? 'active'
                ]
            );
        }
    }
}
