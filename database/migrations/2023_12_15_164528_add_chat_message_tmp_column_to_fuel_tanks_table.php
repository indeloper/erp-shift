<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddChatMessageTmpColumnToFuelTanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fuel_tanks', function (Blueprint $table) {
            $table->json('chat_message_tmp')->nullable()->after('comment_movement_tmp')->comment('ID чата и сообщения о необходимости подтвердить перемещение емкости');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fuel_tanks', function (Blueprint $table) {
            $table->dropColumn('chat_message_tmp');
        });
    }
}
