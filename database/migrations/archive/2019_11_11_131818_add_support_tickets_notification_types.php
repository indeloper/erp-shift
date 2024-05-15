<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        DB::table('notification_types')->insert([
            // Tech Support Notifications
            [
                'id' => 53,
                'group' => 3,
                'name' => 'Уведомление о изменении срока приблизительного исполнения заявки в техническую поддержку',
                'for_everyone' => 1,
            ],
            [
                'id' => 54,
                'group' => 3,
                'name' => 'Уведомление о изменении статуса заявки в техническую поддержку',
                'for_everyone' => 1,
            ],
        ]);

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::beginTransaction();

        DB::table('notification_types')->where('id', 53)->delete();
        DB::table('notification_types')->where('id', 54)->delete();

        DB::commit();
    }
};
