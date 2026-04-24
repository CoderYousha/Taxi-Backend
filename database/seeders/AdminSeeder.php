<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         User::updateOrCreate(
            ['number' => 'admin001'], // البحث بالرقم
            [
                'firstName' => 'Admin',
                'lastName' => 'System',
                'password' => Hash::make('admin123456'),
                'roll' => 'Admin',
                'banned' => false,
                'expireDate' => null,
            ]
        );


        $this->command->info('✓Users have been created successfully');
        $this->command->info('  - ADMIN: admin001 / admin123456');
    }
}
