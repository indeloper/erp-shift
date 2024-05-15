<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::table('project_objects', function (Blueprint $table) {
            $table->boolean('is_participates_in_material_accounting')->comment('Участвует в материальном учете');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('project_objects', function (Blueprint $table) {
            $table->dropColumn('is_participates_in_material_accounting');
        });
    }
};
