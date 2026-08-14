<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Staff;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    /**
     * Seed 5 staff (one Admin). Keyed by email so re-seeding never
     * hits the unique constraint.
     */
    public function run(): void
    {
        $items = [
            ['name' => 'Alice Admin', 'email' => 'alice.admin@example.com', 'role' => Role::Admin],
            ['name' => 'Bob Carter', 'email' => 'bob.carter@example.com', 'role' => Role::Staff],
            ['name' => 'Carol Diaz', 'email' => 'carol.diaz@example.com', 'role' => Role::Staff],
            ['name' => 'Dave Nguyen', 'email' => 'dave.nguyen@example.com', 'role' => Role::Staff],
            ['name' => 'Eve Thompson', 'email' => 'eve.thompson@example.com', 'role' => Role::Staff],
        ];

        foreach ($items as $item) {
            Staff::firstOrCreate(
                ['email' => $item['email']],
                ['name' => $item['name'], 'role' => $item['role']]
            );
        }
    }
}
