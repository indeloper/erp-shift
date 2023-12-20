<?php

use App\Models\CommercialOffer\CommercialOfferManualRequirement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteOneOfComOfferManualNote extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        $note = CommercialOfferManualRequirement::where('name', 'like', '%Заказчик для погружения шпунта/монтажа системы крепления обеспечивает подъезд и разворот для длинномерного транспорта и безопасные подходы к месту разгрузки/складирования шпунта%')->first();
        $note->delete();

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        CommercialOfferManualRequirement::create(['name' => 'Заказчик для погружения шпунта/монтажа системы крепления обеспечивает подъезд и разворот для длинномерного транспорта и безопасные подходы к месту разгрузки/складирования шпунта', 'need_value' => 0]);
    }
}
