<?php

use App\Models\q3wMaterial\operations\q3wOperationFile;
use App\Models\q3wMaterial\operations\q3wOperationFileType;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $fileTypes = [['Файл', 'custom']];

        foreach ($fileTypes as $fileTypeElement) {
            $fileType = new q3wOperationFileType();
            $fileType->name = $fileTypeElement[0];
            $fileType->string_identifier = $fileTypeElement[1];
            $fileType->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $customFileType = q3wOperationFileType::where('string_identifier', 'custom')->first();
        q3wOperationFile::where('upload_file_type', '=', $customFileType->id)->forceDelete();
        $customFileType->forceDelete();
    }
};
