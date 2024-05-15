<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::beginTransaction();

        Schema::create('notifications_for_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('notification_id');
            $table->unsignedInteger('group_id');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('notifications_for_groups')->insert([
            [
                'notification_id' => 5,
                'group_id' => 5,
            ],
            [
                'notification_id' => 5,
                'group_id' => 50,
            ],
            [
                'notification_id' => 5,
                'group_id' => 53,
            ],

            [
                'notification_id' => 6,
                'group_id' => 8,
            ],
            [
                'notification_id' => 6,
                'group_id' => 19,
            ],
            [
                'notification_id' => 6,
                'group_id' => 27,
            ],
            [
                'notification_id' => 6,
                'group_id' => 49,
            ],
            [
                'notification_id' => 6,
                'group_id' => 50,
            ],
            [
                'notification_id' => 6,
                'group_id' => 52,
            ],
            [
                'notification_id' => 6,
                'group_id' => 53,
            ],
            [
                'notification_id' => 6,
                'group_id' => 54,
            ],
            [
                'notification_id' => 7,
                'group_id' => 50,
            ],
            [
                'notification_id' => 7,
                'group_id' => 54,
            ],
            [
                'notification_id' => 7,
                'group_id' => 49,
            ],
            [
                'notification_id' => 8,
                'group_id' => 6,
            ],
            [
                'notification_id' => 16,
                'group_id' => 5,
            ],
            [
                'notification_id' => 17,
                'group_id' => 5,
            ],
            [
                'notification_id' => 18,
                'group_id' => 5,
            ],
            [
                'notification_id' => 21,
                'group_id' => 52,
            ],
            [
                'notification_id' => 21,
                'group_id' => 53,
            ],
            [
                'notification_id' => 21,
                'group_id' => 54,
            ],
            [
                'notification_id' => 23,
                'group_id' => 5,
            ],
            [
                'notification_id' => 23,
                'group_id' => 6,
            ],
            [
                'notification_id' => 23,
                'group_id' => 8,
            ],
            [
                'notification_id' => 23,
                'group_id' => 19,
            ],
            [
                'notification_id' => 23,
                'group_id' => 27,
            ],
            [
                'notification_id' => 23,
                'group_id' => 49,
            ],
            [
                'notification_id' => 23,
                'group_id' => 50,
            ],
            [
                'notification_id' => 23,
                'group_id' => 52,
            ],
            [
                'notification_id' => 23,
                'group_id' => 53,
            ],
            [
                'notification_id' => 23,
                'group_id' => 54,
            ],
            [
                'notification_id' => 24,
                'group_id' => 53,
            ],
            [
                'notification_id' => 25,
                'group_id' => 53,
            ],
            [
                'notification_id' => 26,
                'group_id' => 52,
            ],
            [
                'notification_id' => 26,
                'group_id' => 53,
            ],
            [
                'notification_id' => 26,
                'group_id' => 54,
            ],
            [
                'notification_id' => 27,
                'group_id' => 5,
            ],
            [
                'notification_id' => 27,
                'group_id' => 6,
            ],
            [
                'notification_id' => 27,
                'group_id' => 8,
            ],
            [
                'notification_id' => 27,
                'group_id' => 19,
            ],
            [
                'notification_id' => 27,
                'group_id' => 27,
            ],
            [
                'notification_id' => 27,
                'group_id' => 49,
            ],
            [
                'notification_id' => 27,
                'group_id' => 50,
            ],
            [
                'notification_id' => 27,
                'group_id' => 52,
            ],
            [
                'notification_id' => 27,
                'group_id' => 53,
            ],
            [
                'notification_id' => 27,
                'group_id' => 54,
            ],
            [
                'notification_id' => 28,
                'group_id' => 50,
            ],
            [
                'notification_id' => 30,
                'group_id' => 50,
            ],
            [
                'notification_id' => 31,
                'group_id' => 5,
            ],
            [
                'notification_id' => 31,
                'group_id' => 6,
            ],
            [
                'notification_id' => 32,
                'group_id' => 5,
            ],
            [
                'notification_id' => 32,
                'group_id' => 19,
            ],
            [
                'notification_id' => 33,
                'group_id' => 50,
            ],
            [
                'notification_id' => 35,
                'group_id' => 50,
            ],
            [
                'notification_id' => 36,
                'group_id' => 5,
            ],
            [
                'notification_id' => 37,
                'group_id' => 5,
            ],
            [
                'notification_id' => 37,
                'group_id' => 6,
            ],
            [
                'notification_id' => 37,
                'group_id' => 8,
            ],
            [
                'notification_id' => 37,
                'group_id' => 19,
            ],
            [
                'notification_id' => 37,
                'group_id' => 27,
            ],
            [
                'notification_id' => 37,
                'group_id' => 49,
            ],
            [
                'notification_id' => 37,
                'group_id' => 50,
            ],
            [
                'notification_id' => 37,
                'group_id' => 52,
            ],
            [
                'notification_id' => 37,
                'group_id' => 53,
            ],
            [
                'notification_id' => 37,
                'group_id' => 54,
            ],
            [
                'notification_id' => 38,
                'group_id' => 49,
            ],
            [
                'notification_id' => 38,
                'group_id' => 54,
            ],
            [
                'notification_id' => 39,
                'group_id' => 49,
            ],
            [
                'notification_id' => 39,
                'group_id' => 54,
            ],
            [
                'notification_id' => 41,
                'group_id' => 49,
            ],
            [
                'notification_id' => 41,
                'group_id' => 54,
            ],
            [
                'notification_id' => 42,
                'group_id' => 49,
            ],
            [
                'notification_id' => 42,
                'group_id' => 54,
            ],
            [
                'notification_id' => 43,
                'group_id' => 5,
            ],
            [
                'notification_id' => 45,
                'group_id' => 5,
            ],
            [
                'notification_id' => 50,
                'group_id' => 49,
            ],
            [
                'notification_id' => 50,
                'group_id' => 54,
            ],
            [
                'notification_id' => 51,
                'group_id' => 49,
            ],
            [
                'notification_id' => 51,
                'group_id' => 54,
            ],
        ]);

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications_for_groups');
    }
};
