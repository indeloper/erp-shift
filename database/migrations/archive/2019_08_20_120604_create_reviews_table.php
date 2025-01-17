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
        Schema::create('reviews', function (Blueprint $table) {
            $table->increments('id');
            $table->text('review');
            $table->text('result_comment')->nullable();
            $table->unsignedInteger('result_status')->default(0);
            $table->unsignedInteger('commercial_offer_id')->nullable();
            $table->morphs('reviewable');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviewables');
    }
};
