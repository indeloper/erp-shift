<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SyncIsUploadedColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();
        $com_offers = \App\Models\CommercialOffer\CommercialOffer::whereDoesntHave('gantts')->get();

        foreach ($com_offers as $com_offer) {
            dump($com_offer->id);
            $com_offer->is_uploaded = $com_offer->is_uploaded();
            $com_offer->save();
        }

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \App\Models\CommercialOffer\CommercialOffer::query()->update(['is_uploaded' => 0]);
    }
}