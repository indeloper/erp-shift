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
        Schema::create('project_object_responsibles',
            function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('project_object_id');
                $table->unsignedInteger('contact_id');
                $table->string('note')->nullable();
                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_object_responsibles');
    }

};
