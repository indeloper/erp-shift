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
        Schema::create('timecard_additions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('timecard_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedInteger('type');
            $table->string('name', 500);
            $table->double('amount', 10, 2);
            $table->boolean('prolonged')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['timecard_id', 'type', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timecard_additions');
    }
};
