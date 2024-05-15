<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('our_technic_tickets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('our_technic_id');
            $table->unsignedInteger('sending_object_id')->nullable();
            $table->unsignedInteger('getting_object_id')->nullable();
            $table->unsignedInteger('usage_days')->nullable();
            $table->unsignedInteger('status')->default(1);
            $table->unsignedInteger('type')->default(1);
            $table->timestamp('sending_from_date')->nullable();
            $table->timestamp('sending_to_date')->nullable();
            $table->timestamp('getting_from_date')->nullable();
            $table->timestamp('getting_to_date')->nullable();
            $table->timestamp('usage_from_date')->nullable();
            $table->timestamp('usage_to_date')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('our_technic_tickets');
    }
};
