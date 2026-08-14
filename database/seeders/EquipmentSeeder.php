<?php

namespace Database\Seeders;

use App\Enums\Condition;
use App\Enums\EquipmentStatus;
use App\Models\Equipment;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    /**
     * Seed 10 catalog items. Keyed by serial_no so re-seeding never
     * hits the unique constraint.
     */
    public function run(): void
    {
        $items = [
            ['name' => 'Canon EOS R5', 'category' => 'camera', 'serial_no' => 'CAM-000001'],
            ['name' => 'Canon EOS R6', 'category' => 'camera', 'serial_no' => 'CAM-000002'],
            ['name' => 'Sony A7 IV', 'category' => 'camera', 'serial_no' => 'CAM-000003'],
            ['name' => 'DeWalt Drill', 'category' => 'tool', 'serial_no' => 'TL-000001'],
            ['name' => 'Makita Circular Saw', 'category' => 'tool', 'serial_no' => 'TL-000002'],
            ['name' => 'Bosch Impact Driver', 'category' => 'tool', 'serial_no' => 'TL-000003'],
            ['name' => 'Shure SM7B', 'category' => 'av', 'serial_no' => 'AV-000001'],
            ['name' => 'Rode NT1', 'category' => 'av', 'serial_no' => 'AV-000002'],
            ['name' => 'Pioneer DJ Controller', 'category' => 'av', 'serial_no' => 'AV-000003'],
            ['name' => 'JBL PA Speaker', 'category' => 'av', 'serial_no' => 'AV-000004'],
        ];

        foreach ($items as $item) {
            Equipment::firstOrCreate(
                ['serial_no' => $item['serial_no']],
                [
                    'name' => $item['name'],
                    'category' => $item['category'],
                    'photo' => null,
                    'condition' => Condition::Good,
                    'status' => EquipmentStatus::Available,
                ]
            );
        }
    }
}
