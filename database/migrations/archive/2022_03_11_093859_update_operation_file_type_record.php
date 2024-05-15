<?php

use App\Models\q3wMaterial\operations\q3wOperationFileType;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $operationFileType = q3wOperationFileType::where('string_identifier', 'like', 'consignment-note-photo')->first();
        $operationFileType->name = 'Фото ТТН/ТН';
        $operationFileType->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        $operationFileType = q3wOperationFileType::where('string_identifier', 'like', 'consignment-note-photo')->first();
        $operationFileType->name = 'ТТН';
        $operationFileType->save();
    }
};
