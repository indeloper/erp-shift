<?php

use App\Models\q3wMaterial\operations\q3wOperationFileType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class addNewFileTypeToUpload extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $fileTypes = [['Файл', 'custom']];

        foreach ($fileTypes as $fileTypeElement) {
            $fileType = new q3wOperationFileType();
            $fileType -> name = $fileTypeElement[0];
            $fileType -> string_identifier = $fileTypeElement[1];
            $fileType -> save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        q3wOperationFileType::where('string_identifier', 'custom')->forceDelete();
    }
}
