<?php

use App\Models\MatAcc\MaterialAccountingMaterialAddition;
use App\Models\MatAcc\MaterialAccountingOperation;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AddExistPartMaterialsToBase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    /**
     * The Illuminate application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    private $test;

    public function up()
    {
        DB::beginTransaction();

        $operations = MaterialAccountingOperation::query()
            ->with(['materialsPart.materialAddition'])
            ->where('type', '!=', 5)
            ->where('is_close', '!=', 1)
            ->orderBy('id', 'desc')
            ->get();

        foreach ($operations as $operation) {
            if ($operation->materialsPart->count()) {
                foreach ($operation->materialsPart as $material) {
                    $authUser = $material->materialAddition->user;
                    if ($authUser) {
                        Auth::login($authUser);
                    } else {
                        dd($operation);
                    }

                    $resultDelete = $material->deletePart('withoutBase');
                    dump($operation->id, $material->id);
                    if ($resultDelete['status'] == 'error') {
                        echo 'Все пропало!(Удаление) '.$operation->type_name.', '.$operation->status_name.' id:'.$operation->id.PHP_EOL;
                    }

                    $fakeRequest = new Request([
                        'count_files' => 2,
                        'type' => $material->type,
                        'materials' => [
                            [
                                'material_id' => $material->manual_material_id,
                                'material_unit' => $material->unit,
                                'material_count' => $material->count,
                                'material_date' => $material->fact_date ? Carbon::parse($material->fact_date) : Carbon::today(),
                                'used' => $material->used ? $material->used : false,
                            ],
                        ],
                    ]);
                    $resultCreate = $operation->partSend($fakeRequest);

                    if ($resultCreate['status'] == 'error') {
                        echo 'Все пропало!(Создание) '.$operation->type_name.', '.$operation->status_name.PHP_EOL;
                    }

                    MaterialAccountingMaterialAddition::where('operation_id', $operation->id)
                        ->where('operation_material_id', $material->id)
                        ->update([
                            'operation_material_id' => $resultCreate['operation_material_id'],
                        ]);

                }

            }
        }

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
