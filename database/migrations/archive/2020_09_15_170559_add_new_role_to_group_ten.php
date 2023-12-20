<?php

use App\Models\Group;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewRoleToGroupTen extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
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
     *
     * @return void
     */
    public function down()
    {
        Group::query()->where([
            'name' => 'Главный инженер (свайное направление)',
            'department_id' => 10
        ])->delete();
    }
}
