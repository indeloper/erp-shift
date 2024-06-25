<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('project_objects', function (Blueprint $table) {
            $table->unsignedInteger('project_id')
                ->after('id')
                ->nullable()
                ->comment('Проект');

            $table->foreign('project_id')
                ->references('id')
                ->on('projects');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_objects', function (Blueprint $table) {
            $table->dropForeign('project_objects_project_id_foreign');

            $table->dropColumn('project_id');
        });
    }

};
