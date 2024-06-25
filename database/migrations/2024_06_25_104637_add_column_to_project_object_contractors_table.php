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
        Schema::table('project_object_contractors',
            function (Blueprint $table) {
                $table->boolean('is_main')
                    ->after('id')
                    ->default(false);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_object_contractors',
            function (Blueprint $table) {
                $table->dropColumn('is_main');
            });
    }

};
