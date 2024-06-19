<?php

use App\Models\ProjectObject;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('short_name_project_objects',
            function (Blueprint $table) {
                $table->id();

                $table->foreignIdFor(ProjectObject::class)
                    ->constrained()
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->string('name_first')->nullable();
                $table->string('name_second')->nullable();
                $table->string('index')->nullable();
                $table->string('city')->nullable();
                $table->string('cadastral_number')->nullable();
                $table->string('street')->nullable();
                $table->string('region')->nullable();
                $table->string('house')->nullable();
                $table->string('body')->nullable();
                $table->string('literature')->nullable();
                $table->string('building')->nullable();
                $table->string('land_plot')->nullable();
                $table->string('queue')->nullable();
                $table->string('lot')->nullable();
                $table->string('stage')->nullable();
                $table->string('array')->nullable();

                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('short_name_project_objects');
    }

};
