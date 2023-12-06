<?php

use App\Models\TechAcc\OurTechnic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeStartLocationIdColumnInOurTechnicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE our_technics MODIFY COLUMN start_location_id INT');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE our_technics MODIFY COLUMN start_location_id VARCHAR(255)');

    }
}
