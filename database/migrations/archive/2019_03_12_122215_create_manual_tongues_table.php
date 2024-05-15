<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('manual_tongues', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type')->nullable();
            $table->string('weight')->nullable();
            $table->string('width')->nullable();
            $table->timestamps();
        });

        DB::table('manual_tongues')->insert([
            ['type' => 'Ларсен 5УМ', 'weight' => '113.88', 'width' => '0.5'],
            ['type' => 'Ларсен 4', 'weight' => '74', 'width' => '0.4'],
            ['type' => 'Ларсен 5', 'weight' => '100', 'width' => '0.42'],
            ['type' => 'AU 18', 'weight' => '88.5', 'width' => '0.75'],
            ['type' => 'AU 20', 'weight' => '96.9', 'width' => '0.75'],
            ['type' => 'AU 23', 'weight' => '102.1', 'width' => '0.75'],
            ['type' => 'AU 25', 'weight' => '110.4', 'width' => '0.75'],
            ['type' => 'AZ 19-700', 'weight' => '80', 'width' => '0.7'],
            ['type' => 'AZ 24-700', 'weight' => '95.7', 'width' => '0.7'],
            ['type' => 'GU 18N', 'weight' => '76.9', 'width' => '0.6'],
            ['type' => 'GU 20N', 'weight' => '81.1', 'width' => '0.6'],
            ['type' => 'GU 21N', 'weight' => '81.9', 'width' => '0.6'],
            ['type' => 'GU 22N', 'weight' => '86.1', 'width' => '0.6'],
            ['type' => 'PU 18', 'weight' => '76.9', 'width' => '0.6'],
            ['type' => 'PU 22', 'weight' => '86.1', 'width' => '0.6'],
            ['type' => 'VL 606A', 'weight' => '86.2', 'width' => '0.6'],
            ['type' => 'Larssen 604n', 'weight' => '73.8', 'width' => '0.6'],
            ['type' => 'Larssen 606', 'weight' => '94.2', 'width' => '0.6'],
            ['type' => 'Larssen 607n', 'weight' => '114', 'width' => '0.6'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_tongues');
    }
};
