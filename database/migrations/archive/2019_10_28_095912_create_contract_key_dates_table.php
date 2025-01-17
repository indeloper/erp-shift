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
        Schema::create('contract_key_dates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('contract_id');
            $table->unsignedInteger('key_date_id')->nullable();
            $table->string('name')->nullable();
            $table->string('sum')->nullable();
            $table->timestamp('date_from')->nullable();
            $table->timestamp('date_to')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_key_dates');
    }
};
