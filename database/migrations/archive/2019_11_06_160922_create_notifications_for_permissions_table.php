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

        Schema::create('notifications_for_permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('notification_id');
            $table->string('permission');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('notifications_for_permissions')->insert([
            // Tasks-related notifications
            [
                'notification_id' => 5,
                'permission' => 'tasks_default_myself',
            ],
            [
                'notification_id' => 5,
                'permission' => 'tasks_default_others',
            ],
            [
                'notification_id' => 9,
                'permission' => 'mat_acc_moving_create',
            ],
            [
                'notification_id' => 9,
                'permission' => 'mat_acc_write_off_create',
            ],
            [
                'notification_id' => 9,
                'permission' => 'mat_acc_transformation_create',
            ],
            [
                'notification_id' => 9,
                'permission' => 'mat_acc_arrival_create',
            ],
            [
                'notification_id' => 10,
                'permission' => 'mat_acc_moving_create',
            ],
            [
                'notification_id' => 10,
                'permission' => 'mat_acc_write_off_create',
            ],
            [
                'notification_id' => 10,
                'permission' => 'mat_acc_transformation_create',
            ],
            [
                'notification_id' => 10,
                'permission' => 'mat_acc_arrival_create',
            ],
            [
                'notification_id' => 12,
                'permission' => 'mat_acc_moving_create',
            ],
            [
                'notification_id' => 12,
                'permission' => 'mat_acc_write_off_create',
            ],
            [
                'notification_id' => 12,
                'permission' => 'mat_acc_transformation_create',
            ],
            [
                'notification_id' => 12,
                'permission' => 'mat_acc_arrival_create',
            ],
            [
                'notification_id' => 13,
                'permission' => 'mat_acc_moving_create',
            ],
            [
                'notification_id' => 13,
                'permission' => 'mat_acc_write_off_create',
            ],
            [
                'notification_id' => 13,
                'permission' => 'mat_acc_transformation_create',
            ],
            [
                'notification_id' => 13,
                'permission' => 'mat_acc_arrival_create',
            ],
            [
                'notification_id' => 19,
                'permission' => 'contractors_create',
            ],
            [
                'notification_id' => 20,
                'permission' => 'contractors_delete',
            ],
            [
                'notification_id' => 44,
                'permission' => 'contracts_delete_request',
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
        Schema::dropIfExists('notifications_for_permissions');
    }
};
