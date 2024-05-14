<?php

namespace Tests\Feature;

use App\Models\Manual\ManualMaterial;
use App\Models\MatAcc\MaterialAccountingBase;
use App\Models\MatAcc\MaterialAccountingOperation;
use App\Models\MatAcc\MaterialAccountingOperationMaterials;
use App\Models\ProjectObject;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MatAccCreateOperationMaterialsTest extends TestCase
{
    use DatabaseTransactions;

    public function testAcceptAriivalOperationMaterialsOnPeriod()
    {
        $basesCount = MaterialAccountingBase::count();

        $operation = factory(MaterialAccountingOperation::class)->create([
            'type' => 1, // arrival
            'planned_date_from' => Carbon::today()->subDays(5)->format('d.m.Y'),
            'planned_date_to' => Carbon::today()->format('d.m.Y'),
        ]);

        $manualMaterial = ManualMaterial::with('category')->first();

        $materials = [
            [
                'material_id' => $manualMaterial->id,
                'material_unit' => array_flip((new MaterialAccountingOperationMaterials)->units_name)[$manualMaterial->category->category_unit],
                'material_count' => 5,
            ],
        ];
        $this->assertEquals(MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($operation, $materials, 2 /* -> arrival accept material type */, 'arrival'), true);

        $updatedBasesCount = MaterialAccountingBase::count();

        $newBases = MaterialAccountingBase::orderBy('id', 'desc')->take(6)->get();

        foreach ($newBases as $key => $base) {
            if ($base->date != Carbon::today()->format('d.m.Y')) {
                $this->assertEquals($base->transferred_today, 1);
            } else {
                $this->assertEquals($base->transferred_today, 0);
            }
            $this->assertEquals($base->count, 5);
        }

        $this->assertEquals($basesCount + 6, $updatedBasesCount);
    }

    public function testAcceptWriteOffOperationMaterialsOnPeriod()
    {
        $basesCount = MaterialAccountingBase::count();

        $operationArrival = factory(MaterialAccountingOperation::class)->create([
            'type' => 1, // arrival
            'planned_date_from' => Carbon::today()->subDays(5)->format('d.m.Y'),
            'planned_date_to' => Carbon::today()->format('d.m.Y'),
        ]);

        $operation = factory(MaterialAccountingOperation::class)->create([
            'type' => 2, // write off
            'planned_date_from' => Carbon::today()->subDays(5)->format('d.m.Y'),
            'planned_date_to' => Carbon::today()->format('d.m.Y'),
        ]);

        $manualMaterial = ManualMaterial::with('category')->first();

        $materials = [
            [
                'material_id' => $manualMaterial->id,
                'material_unit' => array_flip((new MaterialAccountingOperationMaterials)->units_name)[$manualMaterial->category->category_unit],
                'material_count' => 5,
            ],
        ];

        MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($operationArrival, $materials, 2 /* -> arrival accept material type */, 'arrival');

        $this->assertEquals(MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($operation, $materials, 2 /* -> write off accept material type */, 'write_off'), true);

        $updatedBasesCount = MaterialAccountingBase::count();

        $newBases = MaterialAccountingBase::orderBy('id', 'desc')->take(6)->get();
        foreach ($newBases as $key => $base) {
            if ($base->date != Carbon::today()->format('d.m.Y')) {
                $this->assertEquals($base->transferred_today, 1);
            } else {
                $this->assertEquals($base->transferred_today, 0);
            }
            $this->assertEquals($base->count, 0);
        }

        $this->assertEquals($basesCount + 6, $updatedBasesCount);
    }

    public function testAcceptMovingOperationMaterialsOnPeriod()
    {
        $basesCount = MaterialAccountingBase::count();

        $operationArrival = factory(MaterialAccountingOperation::class)->create([
            'type' => 1, // arrival
            'planned_date_from' => Carbon::today()->subDays(5)->format('d.m.Y'),
            'planned_date_to' => Carbon::today()->format('d.m.Y'),
        ]);

        $operation = factory(MaterialAccountingOperation::class)->create([
            'type' => 4, // moving
            'planned_date_from' => Carbon::today()->subDays(5)->format('d.m.Y'),
            'planned_date_to' => Carbon::today()->format('d.m.Y'),
            'object_id_from' => ProjectObject::first()->id,
            'object_id_to' => ProjectObject::orderBy('id', 'desc')->first()->id,
        ]);

        $manualMaterial = ManualMaterial::with('category')->first();

        $materials = [
            [
                'material_id' => $manualMaterial->id,
                'material_unit' => array_flip((new MaterialAccountingOperationMaterials)->units_name)[$manualMaterial->category->category_unit],
                'material_count' => 5,
            ],
        ];

        MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($operationArrival, $materials, 2 /* -> arrival accept material type */, 'arrival');

        $this->assertEquals(MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($operation, $materials, 4, 'moving'), true);

        $updatedBasesCount = MaterialAccountingBase::count();

        $newBases = MaterialAccountingBase::orderBy('id', 'desc')->where('object_id', $operation->object_id_from)->take(6)->get();
        foreach ($newBases as $key => $base) {
            if ($base->date != Carbon::today()->format('d.m.Y')) {
                $this->assertEquals($base->transferred_today, 1);
            } else {
                $this->assertEquals($base->transferred_today, 0);
            }
            $this->assertEquals($base->count, 0);
        }

        $newBases = MaterialAccountingBase::orderBy('id', 'desc')->where('object_id', $operation->object_id_to)->take(6)->get();
        foreach ($newBases as $key => $base) {
            if ($base->date != Carbon::today()->format('d.m.Y')) {
                $this->assertEquals($base->transferred_today, 1);
            } else {
                $this->assertEquals($base->transferred_today, 0);
            }
            $this->assertEquals($base->count, 5);
        }

        $this->assertEquals($basesCount + 6 + 6, $updatedBasesCount);
    }

    public function testAcceptTransformationOperationMaterialsOnPeriod()
    {
        $basesCount = MaterialAccountingBase::count();

        $operationArrival = factory(MaterialAccountingOperation::class)->create([
            'type' => 1, // arrival
            'planned_date_from' => Carbon::today()->subDays(5)->format('d.m.Y'),
            'planned_date_to' => Carbon::today()->format('d.m.Y'),
        ]);

        $operation = factory(MaterialAccountingOperation::class)->create([
            'type' => 3, // transformation
            'planned_date_from' => Carbon::today()->subDays(5)->format('d.m.Y'),
            'planned_date_to' => Carbon::today()->format('d.m.Y'),
            'object_id_from' => ProjectObject::first()->id,
            'object_id_to' => ProjectObject::orderBy('id', 'desc')->first()->id,
        ]);

        $manualMaterial = ManualMaterial::with('category')->take(2)->get();

        $materials_from = [
            [
                'material_id' => $manualMaterial[0]->id,
                'material_unit' => array_flip((new MaterialAccountingOperationMaterials)->units_name)[$manualMaterial[0]->category->category_unit],
                'material_count' => 5,
            ],
        ];

        $materials_to = [
            [
                'material_id' => $manualMaterial[1]->id,
                'material_unit' => array_flip((new MaterialAccountingOperationMaterials)->units_name)[$manualMaterial[1]->category->category_unit],
                'material_count' => 4,
            ],
        ];

        MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($operationArrival, $materials_from, 2 /* -> arrival accept material type */, 'arrival');

        $this->assertEquals(MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($operation, $materials_from, 5, 'transformation_from'), true);
        $this->assertEquals(MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($operation, $materials_to, 4, 'transformation_to'), true);

        $updatedBasesCount = MaterialAccountingBase::count();

        $newBases = MaterialAccountingBase::orderBy('id', 'desc')->where('object_id', $operation->object_id_from)->take(6)->get();
        foreach ($newBases as $key => $base) {
            if ($base->date != Carbon::today()->format('d.m.Y')) {
                $this->assertEquals($base->transferred_today, 1);
            } else {
                $this->assertEquals($base->transferred_today, 0);
            }

            $this->assertEquals($base->manual_material_id, $manualMaterial[0]->id);
            $this->assertEquals($base->count, 0);
        }

        $newBases = MaterialAccountingBase::orderBy('id', 'desc')->where('object_id', $operation->object_id_to)->take(6)->get();
        foreach ($newBases as $key => $base) {
            if ($base->date != Carbon::today()->format('d.m.Y')) {
                $this->assertEquals($base->transferred_today, 1);
            } else {
                $this->assertEquals($base->transferred_today, 0);
            }

            $this->assertEquals($base->manual_material_id, $manualMaterial[1]->id);
            $this->assertEquals($base->count, 4);
        }

        $this->assertEquals($basesCount + 6 + 6, $updatedBasesCount);
    }
}
