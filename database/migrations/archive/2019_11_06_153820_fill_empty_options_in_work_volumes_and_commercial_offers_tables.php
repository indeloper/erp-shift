<?php

use App\Models\CommercialOffer\CommercialOffer;
use App\Models\WorkVolume\WorkVolume;
use Illuminate\Database\Migrations\Migration;

class FillEmptyOptionsInWorkVolumesAndCommercialOffersTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        WorkVolume::where('option', '')->update(['option' => 'Стандартное']);
        CommercialOffer::where('option', '')->update(['option' => 'Стандартное']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
