<?php

namespace Tests\Feature;

use App\Models\Manual\ManualMaterial;
use App\Models\MatAcc\MaterialAccountingBase;
use App\Models\MatAcc\MaterialAccountingOperation;
use App\Models\MatAcc\MaterialAccountingOperationMaterials;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MatAccPartToBaseTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testAcceptArrivalOperationMaterialsOnPeriod()
    {
        $basesCount = MaterialAccountingBase::count();

        $operation = factory(MaterialAccountingOperation::class)->create([
            'type' => 1, // arrival
        ]);

        $manualMaterial = ManualMaterial::with('category')->first();

        $materials = [
            [
                'material_id' => $manualMaterial->id,
                'material_unit' => array_flip((new MaterialAccountingOperationMaterials)->units_name)[$manualMaterial->category->category_unit],
                'material_count' => 5,
            ]
        ];

        $this->post(route('building::mat_acc::arrival::part_send', $operation->id), [
            'materials' => $materials,
        ])->assertStatus(302);
//        dd(MaterialAccountingBase::get());
        $newBasesCount = MaterialAccountingBase::count();

        $this->assertEquals($basesCount + 5, $newBasesCount);
    }

}