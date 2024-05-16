<?php

namespace Tests\Feature;

use App\Models\Manual\ManualMaterial;
use App\Models\MatAcc\MaterialAccountingOperation;
use App\Models\MatAcc\MaterialAccountingOperationMaterials;
use App\Models\User;
use Tests\TestCase;

class MatAccPartCloseAcceptionTest extends TestCase
{
    /**
     * Part Send axios pattern
     *
     * payload
     *  materials: {
    id: that.next_mat_id++,
    material_id: that.exist_materials[key].manual_material_id,
    material_unit: that.exist_materials[key].unit,
    material_count: Number(that.exist_materials[key].count),
    units: that.units,
    materials: that.new_materials
    },
    comment: answer.comment,
    files_ids: answer.files_ids,
    images_ids: answer.images_ids
     */

    /**
     * @var array
     */
    private $units;

    public function setUpWorkOperationWithOnePartSave($operation)
    {
        $operation->responsible_users()->create([
            'user_id' => auth()->id(),
            'type' => 0,
        ]);

        $manuals = ManualMaterial::limit(2)->get();
        $mat = MaterialAccountingOperationMaterials::create([
            'operation_id' => $operation->id,
            'manual_material_id' => $manuals[0]->id,
            'unit' => 1,
            'type' => 3,
            'count' => 100,
        ]);
        MaterialAccountingOperationMaterials::create([
            'operation_id' => $operation->id,
            'manual_material_id' => $manuals[1]->id,
            'unit' => 1,
            'type' => 3,
            'count' => 200]);

        $this->post(route('building::mat_acc::arrival::part_send', $operation->id), [
            'materials' => [
                [
                    'id' => 1,
                    'material_id' => $manuals[0]->id,
                    'material_unit' => $mat->unit,
                    'material_label' => $manuals[0]->name,
                    'material_count' => 50,
                    'units' => $this->units,
                ],
            ],
        ])->assertOk();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::find(1));
        $this->units = MaterialAccountingOperationMaterials::$main_units;
        $this->withoutExceptionHandling();
    }

    /** @test */
    public function it_creates_task_for_rp_on_part_close()
    {
        $operation = MaterialAccountingOperation::create(['status' => 1, 'type' => 1]);
        $this->setUpWorkOperationWithOnePartSave($operation);

        $this->assertCount(1, auth()->user()->tasks, 'there is no tasks');
    }
}
