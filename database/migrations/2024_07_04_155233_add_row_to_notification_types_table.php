<?php

use App\Models\Notifications\NotificationTypes;
use Illuminate\Database\Migrations\Migration;

class AddRowToNotificationTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        NotificationTypes::query()
            ->create([
                'group' => 9,
                'name' => 'Уведомление о стаже работ',
                'for_everyone' => true
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
