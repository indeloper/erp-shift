<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
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
};
