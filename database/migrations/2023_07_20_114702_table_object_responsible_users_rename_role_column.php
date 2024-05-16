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
        Schema::table('object_responsible_users', function (Blueprint $table) {
            $table->renameColumn('role', 'object_responsible_user_role_id');
        });

        Schema::table('object_responsible_users', function (Blueprint $table) {
            $table->bigInteger('object_responsible_user_role_id')->unsigned()->change()->comment('ID роли ответственного');
            $table->foreign('object_responsible_user_role_id', 'object_responsible_user_role_foreign')->references('id')->on('object_responsible_user_roles')->change();

        });

        DB::statement("ALTER TABLE object_responsible_users COMMENT 'Ответственные на объектах'");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('object_responsible_users', function (Blueprint $table) {
            $table->dropForeign('object_responsible_user_role_foreign');
        });

        Schema::table('object_responsible_users', function (Blueprint $table) {
            $table->integer('object_responsible_user_role_id')->unsigned()->change();
            // Method Illuminate\Database\Schema\Blueprint::dropComment does not exist.
            // $table->dropComment('ID роли ответственного');
            $table->renameColumn('object_responsible_user_role_id', 'role');
        });

    }
};
