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
        Schema::create('material_accounting_operations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('type');

            $table->unsignedInteger('object_id_from');
            $table->unsignedInteger('object_id_to');

            $table->string('planned_date_from');
            $table->string('planned_date_to');
            $table->string('actual_date_from');
            $table->string('actual_date_to');

            $table->text('comment_from')->nullable();
            $table->text('comment_to')->nullable();
            $table->text('comment_author')->nullable();

            $table->unsignedInteger('author_id');
            $table->unsignedInteger('sender_id');
            $table->unsignedInteger('recipient_id');

            $table->unsignedInteger('status');

            $table->boolean('is_close')->default(0);

            $table->text('reason')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_accounting_operations');
    }
};
