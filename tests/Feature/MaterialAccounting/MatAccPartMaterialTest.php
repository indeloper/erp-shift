<?php

namespace Tests\Feature\OldModules;

use App\Http\Controllers\Building\MaterialAccounting\MaterialAccountingController;
use App\Models\Manual\ManualMaterial;
use App\Models\MatAcc\MaterialAccountingOperation;
use App\Models\MatAcc\MaterialAccountingOperationMaterials;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class MatAccPartMaterialTest extends TestCase
{
    use DatabaseTransactions;

    public $units_name = [
        1 => 'т',
        2 => 'шт',
        3 => 'м.п',
    ];

    /** @test */
    public function it_decrease_fact_when_delete_part_close()
    {
        $operation = MaterialAccountingOperation::create(['status' => 1, 'type' => 1]);
        $mats = [
            MaterialAccountingOperationMaterials::create([
                'operation_id' => $operation->id,
                'manual_material_id' => 1,
                'unit' => 1,
                'type' => 9,
                'count' => 100,
            ]),
            MaterialAccountingOperationMaterials::create([
                'operation_id' => $operation->id,
                'manual_material_id' => 1,
                'unit' => 1,
                'type' => 9,
                'count' => 100]),
            MaterialAccountingOperationMaterials::create([
                'operation_id' => $operation->id,
                'manual_material_id' => 1,
                'unit' => 1,
                'type' => 1,
                'count' => 200]),
        ];

        $controller = new MaterialAccountingController();
        $request = new Request();
        $request->merge(['material_id' => $mats[0]->id]);

        $controller->delete_part_operation($request);

        $this->assertCount(2, MaterialAccountingOperationMaterials::all());
    }

    /** @test */
    public function it_decrease_fact_when_delete_part_close_on_transformation()
    {
        //1.2.4.5.6.7.8.9
        $operation = MaterialAccountingOperation::create(['status' => 1, 'type' => 3]);
        $mats = [
            MaterialAccountingOperationMaterials::create([
                'operation_id' => $operation->id,
                'manual_material_id' => 1,
                'unit' => 1,
                'type' => 8,
                'count' => 100,
            ]),
            MaterialAccountingOperationMaterials::create([
                'operation_id' => $operation->id,
                'manual_material_id' => 1,
                'unit' => 1,
                'type' => 8,
                'count' => 100]),
            MaterialAccountingOperationMaterials::create([
                'operation_id' => $operation->id,
                'manual_material_id' => 1,
                'unit' => 1,
                'type' => 1,
                'count' => 200]),
        ];

        $controller = new MaterialAccountingController();
        $request = new Request();
        $request->merge(['material_id' => $mats[0]->id]);

        $controller->delete_part_operation($request);

        $this->assertCount(2, MaterialAccountingOperationMaterials::all());
        $this->assertEquals(100, MaterialAccountingOperationMaterials::where('type', 1)->first()->count);
    }

    /** @test */
    public function it_change_fact_when_update_part_close()
    {
        $this->actingAs(User::first());
        $operation = MaterialAccountingOperation::create(['status' => 1, 'type' => 1]);
        $man_mat = ManualMaterial::first();
        $unit = array_search($man_mat->category->category_unit, $this->units_name);

        $mats = [
            MaterialAccountingOperationMaterials::create([
                'operation_id' => $operation->id,
                'manual_material_id' => $man_mat->id,
                'unit' => $unit,
                'type' => 9,
                'count' => 100,
            ]),
            MaterialAccountingOperationMaterials::create([
                'operation_id' => $operation->id,
                'manual_material_id' => $man_mat->id,
                'unit' => $unit,
                'type' => 9,
                'count' => 100]),
            MaterialAccountingOperationMaterials::create([
                'operation_id' => $operation->id,
                'manual_material_id' => $man_mat->id,
                'unit' => $unit,
                'type' => 1,
                'count' => 200]),
        ];

        $controller = new MaterialAccountingController();
        $request = new Request();
        $request->merge([
            'material_id' => $mats[0]->id,
            'material_unit' => $unit,
            'material_count' => 120,
            'manual_material_id' => $mats[0]->manual_material_id,
        ]);

        $controller->update_part_operation($request);

        $this->assertCount(3, MaterialAccountingOperationMaterials::all());
        $this->assertEquals(220, MaterialAccountingOperationMaterials::where('type', 1)->sum('count'));
    }

    /** @test */
    public function it_change_fact_when_update_part_close_on_transformation()
    {
        $this->actingAs(User::first());
        $operation = MaterialAccountingOperation::create(['status' => 1, 'type' => 4]);
        $man_mat = ManualMaterial::first();
        $unit = array_search($man_mat->category->category_unit, $this->units_name);

        $mats = [
            MaterialAccountingOperationMaterials::create([
                'operation_id' => $operation->id,
                'manual_material_id' => $man_mat->id,
                'unit' => $unit,
                'type' => 9,
                'count' => 100,
            ]),
            MaterialAccountingOperationMaterials::create([
                'operation_id' => $operation->id,
                'manual_material_id' => $man_mat->id,
                'unit' => $unit,
                'type' => 9,
                'count' => 100]),
            MaterialAccountingOperationMaterials::create([
                'operation_id' => $operation->id,
                'manual_material_id' => $man_mat->id,
                'unit' => $unit,
                'type' => 2,
                'count' => 200]),
        ];

        $controller = new MaterialAccountingController();
        $request = new Request();
        $request->merge([
            'material_id' => $mats[0]->id,
            'material_unit' => $unit,
            'material_count' => 120,
            'manual_material_id' => $mats[0]->manual_material_id,
        ]);
        $controller->update_part_operation($request);

        $this->assertCount(3, MaterialAccountingOperationMaterials::all());
        $this->assertEquals(220, MaterialAccountingOperationMaterials::where('type', 2)->sum('count'));
    }

    /** @test */
    public function it_change_fact_when_update_part_close_on_moving()
    {
        $this->actingAs(User::first());
        $operation = MaterialAccountingOperation::create(['status' => 1, 'type' => 4]);
        $man_mat = ManualMaterial::first();
        $unit = array_search($man_mat->category->category_unit, $this->units_name);

        $mats = [
            MaterialAccountingOperationMaterials::create([
                'operation_id' => $operation->id,
                'manual_material_id' => $man_mat->id,
                'unit' => $unit,
                'type' => 8,
                'count' => 100,
            ]),
            MaterialAccountingOperationMaterials::create([
                'operation_id' => $operation->id,
                'manual_material_id' => $man_mat->id,
                'unit' => $unit,
                'type' => 8,
                'count' => 100]),
            MaterialAccountingOperationMaterials::create([
                'operation_id' => $operation->id,
                'manual_material_id' => $man_mat->id,
                'unit' => $unit,
                'type' => 1,
                'count' => 200]),
        ];
        $operation->status = 2;
        $operation->save();
        $controller = new MaterialAccountingController();
        $request = new Request();
        $request->merge(['material_id' => $mats[0]->id]);

        $controller->delete_part_operation($request);

        $this->assertCount(2, MaterialAccountingOperationMaterials::all());
        $this->assertCount(3, MaterialAccountingOperationMaterials::withTrashed()->get());
        $this->assertEquals(100, MaterialAccountingOperationMaterials::where('type', 1)->sum('count'));
    }
}
