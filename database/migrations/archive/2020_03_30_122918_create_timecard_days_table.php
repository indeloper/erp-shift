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
        Schema::create('timecard_days', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('timecard_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('day');
            $table->boolean('is_opened')->default(0);
            $table->boolean('completed')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['timecard_id', 'day']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timecard_days');
    }
};
