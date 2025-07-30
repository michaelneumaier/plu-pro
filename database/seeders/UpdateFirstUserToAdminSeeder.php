<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UpdateFirstUserToAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update the first user to be an admin
        $firstUser = User::first();
        
        if ($firstUser) {
            $firstUser->update(['role' => 'admin']);
            $firstUser->assignRole('admin');
            
            $this->command->info("First user (ID: {$firstUser->id}, Email: {$firstUser->email}) has been updated to admin role.");
        } else {
            $this->command->warn('No users found in the database.');
        }
    }
}