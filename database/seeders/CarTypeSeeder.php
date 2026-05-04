<?php

namespace Database\Seeders;

use App\Models\CarType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CarTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        CarType::updateOrCreate(
            ['name' => 'العداد الحر'],
            [
                'timePrice' => 0,
                'KMPrice' => 0,
                'openPrice' => 0
            ]
        );
        $this->command->info('CarType Initialize Done');
    }
}
