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
                $table->dropColumn('contact_id');
                $table->unsignedInteger('contractor_id');
                $table->unsignedInteger('user_id');
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_object_contractors',
            function (Blueprint $table) {
                $table->unsignedInteger('contact_id');
                $table->dropColumn('contractor_id');
                $table->dropColumn('user_id');
            });
    }

};
