<?php

use App\Models\q3wMaterial\operations\q3wOperationFileType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateOperationFileTypeRecord extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $operationFileType = q3wOperationFileType::where("string_identifier", "like", "consignment-note-photo")->first();
        $operationFileType->name = "Фото ТТН/ТН";
        $operationFileType->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $operationFileType = q3wOperationFileType::where("string_identifier", "like", "consignment-note-photo")->first();
        $operationFileType->name = "ТТН";
        $operationFileType->save();
    }
}
