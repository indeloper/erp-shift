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
        Schema::table('our_technics', function (Blueprint $table) {
            $table->integer('manufacture_year')->nullable()->after('technic_brand_model_id')->comment('Год выпуска');
            $table->string('serial_number')->nullable()->after('manufacture_year')->comment('Заводской номер');
            $table->string('registration_number')->nullable()->after('serial_number')->comment('Государственный номер регистрации');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('our_technics', function (Blueprint $table) {
            $table->dropColumn('manufacture_year');
            $table->dropColumn('serial_number');
            $table->dropColumn('registration_number');
        });
    }
};
