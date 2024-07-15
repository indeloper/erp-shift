<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::beginTransaction();

        DB::table('notification_types')->insert([
            // Material Accounting notifications
            [
                'id' => 111,
                'group' => 1,
                'name' => 'Уведомление о необходимости выполнить задач',
                'for_everyone' => 1,
            ],
        ]);

        DB::commit();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::beginTransaction();

        DB::table('notification_types')->where('id', 111)->delete();

        DB::commit();
    }
};
