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
                $table->unsignedInteger('project_object_id');
                $table->unsignedInteger('contact_id');
                $table->string('note')->nullable();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_object_contractors',
            function (Blueprint $table) {
                $table->dropColumn('project_object_id');
                $table->dropColumn('contact_id');
                $table->dropColumn('note');
            });
    }

};
