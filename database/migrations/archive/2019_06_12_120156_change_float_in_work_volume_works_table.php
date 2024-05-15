<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE work_volume_works CHANGE COLUMN count count DOUBLE(10, 3) NULL DEFAULT NULL ;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE work_volume_works CHANGE COLUMN count count DOUBLE(10, 2) NULL DEFAULT NULL ;');
    }
};
