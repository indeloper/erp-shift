<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::beginTransaction();

        Schema::create('user_disabled_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('notification_id');
            $table->boolean('in_telegram')->default(1);
            $table->boolean('in_system')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('user_disabled_notifications')->insert([
            // Ismagilov Disabled Notifications
            [
                'notification_id' => 28,
                'user_id' => 6,
                'in_telegram' => 0,
                'in_system' => 0,
            ],
            [
                'notification_id' => 30,
                'user_id' => 6,
                'in_telegram' => 0,
                'in_system' => 0,
            ],
            [
                'notification_id' => 29,
                'user_id' => 6,
                'in_telegram' => 0,
                'in_system' => 0,
            ],
            [
                'notification_id' => 37,
                'user_id' => 6,
                'in_telegram' => 0,
                'in_system' => 0,
            ],
            [
                'notification_id' => 38,
                'user_id' => 6,
                'in_telegram' => 0,
                'in_system' => 0,
            ],
            [
                'notification_id' => 39,
                'user_id' => 6,
                'in_telegram' => 0,
                'in_system' => 0,
            ],
            [
                'notification_id' => 40,
                'user_id' => 6,
                'in_telegram' => 0,
                'in_system' => 0,
            ],
            [
                'notification_id' => 42,
                'user_id' => 6,
                'in_telegram' => 0,
                'in_system' => 0,
            ],
            [
                'notification_id' => 41,
                'user_id' => 6,
                'in_telegram' => 0,
                'in_system' => 0,
            ],
            [
                'notification_id' => 6,
                'user_id' => 6,
                'in_telegram' => 0,
                'in_system' => 0,
            ],
            [
                'notification_id' => 21,
                'user_id' => 6,
                'in_telegram' => 0,
                'in_system' => 0,
            ],
            [
                'notification_id' => 25,
                'user_id' => 6,
                'in_telegram' => 0,
                'in_system' => 0,
            ],
            [
                'notification_id' => 23,
                'user_id' => 6,
                'in_telegram' => 0,
                'in_system' => 0,
            ],
            [
                'notification_id' => 27,
                'user_id' => 6,
                'in_telegram' => 0,
                'in_system' => 0,
            ],
            [
                'notification_id' => 4,
                'user_id' => 6,
                'in_telegram' => 0,
                'in_system' => 0,
            ],
            [
                'notification_id' => 50,
                'user_id' => 6,
                'in_telegram' => 0,
                'in_system' => 0,
            ],
            [
                'notification_id' => 7,
                'user_id' => 6,
                'in_telegram' => 0,
                'in_system' => 0,
            ],
            [
                'notification_id' => 47,
                'user_id' => 6,
                'in_telegram' => 0,
                'in_system' => 0,
            ],
            [
                'notification_id' => 48,
                'user_id' => 6,
                'in_telegram' => 0,
                'in_system' => 0,
            ],
        ]);

        DB::commit();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_disabled_notifications');
    }
};
