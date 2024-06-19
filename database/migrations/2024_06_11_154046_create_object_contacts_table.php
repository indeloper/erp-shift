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
        Schema::create('object_contacts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('project_object_id');
            $table->unsignedInteger('contact_id');

            $table->string('note');

            $table->foreign('project_object_id')
                ->references('id')
                ->on('project_objects')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('contact_id')
                ->references('id')
                ->on('contractor_contacts')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('object_contacts');
    }

};
