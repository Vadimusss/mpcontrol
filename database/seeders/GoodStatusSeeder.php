<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\GoodStatus;

class GoodStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'Актив. ассорт.',
                'slug' => 'active_assortment',
            ],
            [
                'name' => 'Орг. продажи',
                'slug' => 'org_sales',
            ],
            [
                'name' => 'Новинки',
                'slug' => 'new_items',
            ],
            [
                'name' => 'Вывод',
                'slug' => 'withdrawal',
            ],
            [
                'name' => 'Без статуса',
                'slug' => 'none',
            ],
        ];

        foreach ($statuses as $status) {
            GoodStatus::firstOrCreate(
                ['slug' => $status['slug']],
                $status
            );
        }
    }
}
