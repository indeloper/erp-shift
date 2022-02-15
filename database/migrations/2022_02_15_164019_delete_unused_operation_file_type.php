<?php

use App\Models\q3wMaterial\operations\q3wOperationFileType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteUnusedOperationFileType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $operationFileType = q3wOperationFileType::where('string_identifier', 'like', 'materials-photo');
        $operationFileType->forceDelete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $fileTypes = [['Фото материалов', 'materials-photo']];

        foreach ($fileTypes as $fileTypeElement) {
            $fileType = new q3wOperationFileType();
            $fileType -> name = $fileTypeElement[0];
            $fileType -> string_identifier = $fileTypeElement[1];
            $fileType -> save();
        }
    }
}
