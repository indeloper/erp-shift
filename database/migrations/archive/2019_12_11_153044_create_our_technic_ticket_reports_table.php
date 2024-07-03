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
        Schema::create('our_technic_ticket_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('our_technic_ticket_id');
            $table->float('hours', 3, 1);
            $table->unsignedInteger('user_id');
            $table->text('comment')->nullable();
            $table->date('date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('our_technic_ticket_reports');
    }
};
