<?php

use App\Models\Manual\ManualRelationMaterialWork;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $removed_relations = ManualRelationMaterialWork::whereIn('manual_work_id', [1, 2, 3])->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
