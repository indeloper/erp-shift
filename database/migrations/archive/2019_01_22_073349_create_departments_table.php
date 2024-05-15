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
        Schema::create('departments', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('name');
        });

        DB::table('departments')->insert([
            ['name' => 'Администрация'],
            ['name' => 'Бухгалтерия'],
            ['name' => 'Материально-технический'],
            ['name' => 'Отдел качества'],
            ['name' => 'Отдел персонала'],
            ['name' => 'Отдел продаж'],
            ['name' => 'Претензионно-договорной'],
            ['name' => 'Проектный'],
            ['name' => 'ПТО'],
            ['name' => 'Строительный'],
            ['name' => 'УМиТ'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
