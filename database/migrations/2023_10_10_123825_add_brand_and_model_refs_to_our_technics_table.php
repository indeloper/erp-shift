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
        Schema::table('our_technics', function (Blueprint $table) {
            $table->bigInteger('technic_brand_id')->nullable()->unsigned()->after('company_id')->comment('ID бренда техники');
            $table->foreign('technic_brand_id')->references('id')->on('technic_brands');

            $table->bigInteger('technic_brand_model_id')->nullable()->unsigned()->after('technic_brand_id')->comment('ID модели техники');
            $table->foreign('technic_brand_model_id')->references('id')->on('technic_brand_models');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('our_technics', function (Blueprint $table) {
            $table->dropForeign(['technic_brand_id']);
            $table->dropColumn('technic_brand_id');

            $table->dropForeign(['technic_brand_model_id']);
            $table->dropColumn('technic_brand_model_id');
        });
    }
};
