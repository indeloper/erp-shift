<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('commercial_offer_manual_notes')->insert([
            [
                'name' => 'Испытания свай производятся после срока отдыха свай до 21 суток.
                    На момент отдыха свай Подрядчик имеет право мобилизироваться с объекта после погружения испытуемой сваи и до проведения испытаний,
                    при этом Заказчик оплачивает дополнительную мобилизацию из расчета 400 000р. за комплект, либо простой в размере 20 000р./смена.',

                'need_value' => 0,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
