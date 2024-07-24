<?php

namespace Database\Seeders;

use App\Models\LaborSafety\LaborSafetyOrderType;
use Illuminate\Database\Seeder;

class NewDocumentSIZSeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LaborSafetyOrderType::query()->create([
            'name'                   => 'О назначении ответственного за обслуживание и периодический осмотр средств индивидуальной защиты',
            'short_name'             => 'СИЗ',
            'full_name'              => 'О назначении ответственного за обслуживание и периодический осмотр средств индивидуальной защиты',
            'order_type_category_id' => 1,
            'sort_order'             => 280,
            'template'               => view('labor-safety.word.siz-template'),
        ]);
    }

}
