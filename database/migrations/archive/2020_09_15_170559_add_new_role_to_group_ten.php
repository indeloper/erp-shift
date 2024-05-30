<?php

use App\Models\Group;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::beginTransaction();
        $new_role = ['name' => 'Главный инженер (свайное направление)',
            'department_id' => 10];

        if (Group::query()->where($new_role)->doesntExist()) {
            Group::create([
                'name' => 'Главный инженер (свайное направление)',
                'department_id' => 10,
            ]);
        }

        DB::commit();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Group::query()->where([
            'name' => 'Главный инженер (свайное направление)',
            'department_id' => 10,
        ])->delete();
    }
};
