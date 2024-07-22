<?php

namespace Database\Seeders;

use App\Models\ReportingGroup;
use Illuminate\Database\Seeder;

class ReportingGroupSeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ReportingGroup::query()->firstOrCreate([
            'name' => 'База',
        ]);

        ReportingGroup::query()->firstOrCreate([
            'name' => 'Геодезисты - сваи',
        ]);
        ReportingGroup::query()->firstOrCreate([
            'name' => 'Геодезисты - шпунт',
        ]);
        ReportingGroup::query()->firstOrCreate([
            'name' => 'ГеоТест',
        ]);
        ReportingGroup::query()->firstOrCreate([
            'name' => 'Крановщики',
        ]);
        ReportingGroup::query()->firstOrCreate([
            'name' => 'Механики',
        ]);
        ReportingGroup::query()->firstOrCreate([
            'name' => 'Наемные сотрудники',
        ]);
        ReportingGroup::query()->firstOrCreate([
            'name' => 'Офис',
        ]);
        ReportingGroup::query()->firstOrCreate([
            'name' => 'Прорабы - сваи',
        ]);
        ReportingGroup::query()->firstOrCreate([
            'name' => 'Прорабы - шпунт',
        ]);
        ReportingGroup::query()->firstOrCreate([
            'name' => 'Руководители',
        ]);
        ReportingGroup::query()->firstOrCreate([
            'name' => 'Сваи вдавливание',
        ]);
        ReportingGroup::query()->firstOrCreate([
            'name' => 'Сотрудничество шпунт',
        ]);
    }

}
