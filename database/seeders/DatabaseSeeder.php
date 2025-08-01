<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a test user for marketplace lists
        User::factory()->create([
            'id' => 1,
            'name' => 'PLUPro Admin',
            'email' => 'admin@plupro.com',
        ]);

        $this->call([
            PLUCodesSeeder::class,
        ]);
        $this->call(ConsumerUsageTierSeeder::class);
        $this->call(MarketplaceListsSeeder::class);
    }
}
