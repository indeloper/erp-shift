<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('material_accounting_operation_files', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('operation_id');
            $table->unsignedInteger('manual_material_id');
            $table->string('file_name');
            $table->string('path');

            $table->unsignedInteger('user_id');

            $table->unsignedInteger('author_type');
            $table->unsignedInteger('type'); // docs or images

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_accounting_operation_files');
    }
};
