<?php

use App\Models\q3wMaterial\q3wMaterialTransformationType;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $transformationType = new q3wMaterialTransformationType();
        $transformationType->value = 'Изготовление клиновидного';
        $transformationType->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        q3wMaterialTransformationType::where('value', 'like', 'Изготовление клиновидного')->first()->forceDelete();
    }
};
