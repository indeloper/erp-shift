<?php

namespace Tests\Feature;

use App\Models\Contractors\Contractor;
use App\Models\Group;
use App\Models\Manual\ManualMaterial;
use App\Models\Manual\ManualReference;
use App\Models\MatAcc\MaterialAccountingBase;
use App\Models\MatAcc\MaterialAccountingOperation;
use App\Models\MatAcc\MaterialAccountingOperationMaterials;
use App\Models\ProjectObject;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class MaterialAccountingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function operation_material_can_be_new(): void
    {
        // Given operation material
        $operationMaterial = MaterialAccountingOperationMaterials::factory()->create();

        // Then operation material should be new
        $this->assertEquals(0, $operationMaterial->used);
    }

    /** @test */
    public function operation_material_can_be_used(): void
    {
        // Given used operation material
        $operationMaterial = MaterialAccountingOperationMaterials::factory()->create(['used' => 1]);

        // Then operation material should be used
        $this->assertEquals(1, $operationMaterial->used);
    }

    /** @test */
    public function operation_material_name_getter_should_know_about_material_usage(): void
    {
        // Given used operation material
        $usedOperationMaterial = MaterialAccountingOperationMaterials::factory()->create(['used' => 1]);
        // Given new operation material
        $newOperationMaterial = MaterialAccountingOperationMaterials::factory()->create();

        // Then used operation material name should have used label
        $this->assertContains('Б/У', $usedOperationMaterial->material_name);
        // And non-used should not
        $this->assertNotContains('Б/У', $newOperationMaterial->material_name);
    }

    /** @test */
    public function base_material_can_be_new(): void
    {
        // Given base material
        $baseMaterial = MaterialAccountingBase::factory()->create();

        // Then base material should be new
        $this->assertEquals(0, $baseMaterial->used);
    }

    /** @test */
    public function base_material_can_be_used(): void
    {
        // Given used base material
        $baseMaterial = MaterialAccountingBase::factory()->create(['used' => 1]);

        // Then base material should be used
        $this->assertEquals(1, $baseMaterial->used);
    }

    /** @test */
    public function base_material_name_getter_should_know_about_material_usage(): void
    {
        // Given used base material
        $usedBaseMaterial = MaterialAccountingBase::factory()->create(['used' => 1]);
        // Given new base material
        $newBaseMaterial = MaterialAccountingBase::factory()->create();

        // Then used base material name should have used label
        $this->assertContains('Б/У', $usedBaseMaterial->material_name);
        // And non-used should not
        $this->assertNotContains('Б/У', $newBaseMaterial->material_name);
    }

    /** @test */
    public function operation_index_scope_can_return_nothing(): void
    {
        // Given absence
        // Whew we use index() scope
        $result = MaterialAccountingOperation::index()->get();

        // Then result should be empty
        $this->assertEmpty($result);
    }

    /** @test */
    public function operation_index_scope_can_return_operations_with_new_materials(): void
    {
        // Given material accounting operation
        $operation = MaterialAccountingOperation::factory()->create();
        // Given materials for operation
        $materials = MaterialAccountingOperationMaterials::factory()->count(3)->create(['operation_id' => $operation->id]);

        // Whew we use index() scope
        $result = MaterialAccountingOperation::index()->get();

        // Then result should have one operation
        $this->assertCount(1, $result);
        $returnedOperation = $result->first();
        $this->assertEquals($operation->id, $returnedOperation->id);
        // And operation materials
        $this->assertCount(3, $returnedOperation->materials);
        $this->assertEquals($materials->pluck('id'), $returnedOperation->materials->pluck('id'));
        // Materials should be new
        $this->assertEquals($materials->pluck('used'), $returnedOperation->materials->pluck('used'));
    }

    /** @test */
    public function operation_index_scope_can_return_operations_with_used_materials(): void
    {
        // Given material accounting operation
        $operation = MaterialAccountingOperation::factory()->create();
        // Given used materials for operation
        $materials = MaterialAccountingOperationMaterials::factory()->count(3)->create(['operation_id' => $operation->id, 'used' => 1]);

        // Whew we use index() scope
        $result = MaterialAccountingOperation::index()->get();

        // Then result should have one operation
        $this->assertCount(1, $result);
        $returnedOperation = $result->first();
        $this->assertEquals($operation->id, $returnedOperation->id);
        // And operation materials
        $this->assertCount(3, $returnedOperation->materials);
        $this->assertEquals($materials->pluck('id'), $returnedOperation->materials->pluck('id'));
        // Materials should be used
        $this->assertEquals($materials->pluck('used'), $returnedOperation->materials->pluck('used'));
    }

    /** @test */
    public function operation_index_scope_can_return_operations_with_new_and_used_materials(): void
    {
        // Given material accounting operation
        $operation = MaterialAccountingOperation::factory()->create();
        // Given manual material
        $manualMaterial = ManualMaterial::factory()->create();
        // Given used material for operation
        $usedMaterial = MaterialAccountingOperationMaterials::factory()->create(['operation_id' => $operation->id, 'manual_material_id' => $manualMaterial->id, 'used' => 1]);
        // Given new material for operation
        $newMaterial = MaterialAccountingOperationMaterials::factory()->create(['operation_id' => $operation->id, 'manual_material_id' => $manualMaterial->id]);

        // Whew we use index() scope
        $result = MaterialAccountingOperation::index()->get();

        // Then result should have one operation
        $this->assertCount(1, $result);
        $returnedOperation = $result->first();
        $this->assertEquals($operation->id, $returnedOperation->id);
        // And two operation materials
        $this->assertCount(2, $returnedOperation->materials);
        // With one used
        $this->assertCount(1, $returnedOperation->materials->where('used', 1));
        $this->assertEquals($usedMaterial->id, $returnedOperation->materials->where('used', 1)->first()->id);
        // And one new
        $this->assertCount(1, $returnedOperation->materials->where('used', 0));
        $this->assertEquals($newMaterial->id, $returnedOperation->materials->where('used', 0)->first()->id);
    }

    /** @test */
    public function base_index_scope_can_return_nothing(): void
    {
        // Given absence
        // Whew we use index() scope
        $result = MaterialAccountingBase::index()->get();

        // Then result should be empty
        $this->assertEmpty($result);
    }

    /** @test */
    public function base_index_scope_can_return_bases_with_new_materials(): void
    {
        // Given new material accounting base
        $base = MaterialAccountingBase::factory()->create(['used' => 0, 'count' => 1]);

        // Whew we use index() scope
        $result = MaterialAccountingBase::index()->get();

        // Then result should have one base
        $this->assertCount(1, $result);
        $returnedBase = $result->first();
        $this->assertEquals($base->id, $returnedBase->id);
        // Base should be new
        $this->assertEquals($base->used, $returnedBase->used);
    }

    /** @test */
    public function base_index_scope_can_return_bases_with_used_materials(): void
    {
        // Given used material accounting base
        $base = MaterialAccountingBase::factory()->create(['used' => 1, 'count' => 1]);

        // Whew we use index() scope
        $result = MaterialAccountingBase::index()->get();

        // Then result should have one base
        $this->assertCount(1, $result);
        $returnedBase = $result->first();
        $this->assertEquals($base->id, $returnedBase->id);
        // Base should be used
        $this->assertEquals($base->used, $returnedBase->used);
    }

    /** @test */
    public function base_index_scope_can_return_bases_with_new_and_used_materials(): void
    {
        // Given manual material
        $manualMaterial = ManualMaterial::factory()->create();
        // Given new material accounting base
        $newBase = MaterialAccountingBase::factory()->create(['used' => 0, 'count' => 1, 'manual_material_id' => $manualMaterial->id]);
        // Given used material accounting base
        $usedBase = MaterialAccountingBase::factory()->create(['used' => 1, 'count' => 1, 'manual_material_id' => $manualMaterial->id]);

        // Whew we use index() scope
        $result = MaterialAccountingBase::index()->get();

        // Then result should have two bases
        $this->assertCount(2, $result);
        $this->assertEquals([$newBase->id, $usedBase->id], $result->pluck('id')->toArray());
        $returnedNewBases = $result->where('used', 1);
        // With one used
        $this->assertCount(1, $returnedNewBases);
        $this->assertEquals($usedBase->id, $returnedNewBases->first()->id);
        // And one new
        $returnedUsedBases = $result->where('used', 0);
        $this->assertCount(1, $returnedUsedBases);
        $this->assertEquals($newBase->id, $returnedUsedBases->first()->id);
    }

    /** @test */
    public function user_without_permission_can_not_move_material_to_used_state(): void
    {
        // Given user without permissions
        $user = User::factory()->create();

        // Whew user make post request
        $response = $this->actingAs($user)->post(route('building::mat_acc::move_to_used'), []);

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function foremans_can_move_material_to_used_state(): void
    {
        // Given foreman
        $foremans = [14, 23, 31];
        $user = User::factory()->create(['group_id' => $foremans[array_rand($foremans)]]);

        // Whew user make post request
        $response = $this->actingAs($user)->post(route('building::mat_acc::move_to_used'), []);

        // Then user shouldn't have 403, but 302
        $response->assertStatus(302);
    }

    /** @test */
    public function RPs_can_move_material_to_used_state(): void
    {
        // Given RP
        $RPs = [13, 19, 27];
        $user = User::factory()->create(['group_id' => $RPs[array_rand($RPs)]]);

        // Whew user make post request
        $response = $this->actingAs($user)->post(route('building::mat_acc::move_to_used'), []);

        // Then user shouldn't have 403, but 302
        $response->assertStatus(302);
    }

    /** @test */
    public function user_can_not_move_to_used_more_materials_than_it_was_on_base(): void
    {
        // Given user
        $ableToMoveToUsed = [13, 14, 19, 23, 27, 31];
        $user = User::factory()->create(['group_id' => $ableToMoveToUsed[array_rand($ableToMoveToUsed)]]);
        // Given base
        $base = MaterialAccountingBase::factory()->create(['count' => 10]);

        // When user make post request with data
        $data = [
            'base_id' => $base->id,
            'count' => 12,
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::move_to_used'), $data);

        // Then user should have errors in session
        $response->assertSessionHasErrors('too_much');
    }

    /** @test */
    public function user_can_not_move_to_used_zero_materials(): void
    {
        // Given user
        $ableToMoveToUsed = [13, 14, 19, 23, 27, 31];
        $user = User::factory()->create(['group_id' => $ableToMoveToUsed[array_rand($ableToMoveToUsed)]]);
        // Given base
        $base = MaterialAccountingBase::factory()->create(['count' => 10]);

        // When user make post request with data
        $data = [
            'base_id' => $base->id,
            'count' => 0,
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::move_to_used'), $data);

        // Then user should have errors in session
        $response->assertSessionHasErrors('count');
    }

    /** @test */
    public function user_can_not_move_to_used_materials_that_was_not_exist(): void
    {
        // Given user
        $ableToMoveToUsed = [13, 14, 19, 23, 27, 31];
        $user = User::factory()->create(['group_id' => $ableToMoveToUsed[array_rand($ableToMoveToUsed)]]);
        // Given base
        $base = MaterialAccountingBase::factory()->create(['count' => 10]);

        // When user make post request with data
        $data = [
            'base_id' => ($base->id + 1),
            'count' => 10,
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::move_to_used'), $data);

        // Then user should have errors in session
        $response->assertSessionHasErrors('base_id');
    }

    /** @test */
    public function user_can_move_to_used_materials_all_base_materials(): void
    {
        // Given user
        $ableToMoveToUsed = [13, 14, 19, 23, 27, 31];
        $user = User::factory()->create(['group_id' => $ableToMoveToUsed[array_rand($ableToMoveToUsed)]]);
        // Given base
        $base = MaterialAccountingBase::factory()->create(['count' => 10]);

        // When user make post request with data
        $data = [
            'base_id' => $base->id,
            'count' => 10,
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::move_to_used'), $data);

        // Then ...
        // Base should have count 0, because all materials are used now
        $this->assertEquals(0, $base->refresh()->count);
        // New base should be created
        $newUsedBase = MaterialAccountingBase::get()->last();
        // With same material
        $this->assertEquals($base->manual_material_id, $newUsedBase->manual_material_id);
        // With count 10
        $this->assertEquals(10, $newUsedBase->count);
        // With used materials
        $this->assertEquals(1, $newUsedBase->used);
    }

    /** @test */
    public function user_can_move_to_used_materials_part_of_base_materials(): void
    {
        // Given user
        $ableToMoveToUsed = [13, 14, 19, 23, 27, 31];
        $user = User::factory()->create(['group_id' => $ableToMoveToUsed[array_rand($ableToMoveToUsed)]]);
        // Given base
        $base = MaterialAccountingBase::factory()->create(['count' => 10]);

        // When user make post request with data
        $data = [
            'base_id' => $base->id,
            'count' => 5,
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::move_to_used'), $data);

        // Then ...
        // Base should have count 5, because 5 materials are used now
        $this->assertEquals(5, $base->refresh()->count);
        // New base should be created
        $newUsedBase = MaterialAccountingBase::get()->last();
        // With same material
        $this->assertEquals($base->manual_material_id, $newUsedBase->manual_material_id);
        // With count 10
        $this->assertEquals(5, $newUsedBase->count);
        // With used materials
        $this->assertEquals(1, $newUsedBase->used);
    }

    /** @test */
    public function user_can_move_to_used_materials_part_of_base_materials_one_more_time(): void
    {
        // Given user
        $ableToMoveToUsed = [13, 14, 19, 23, 27, 31];
        $user = User::factory()->create(['group_id' => $ableToMoveToUsed[array_rand($ableToMoveToUsed)]]);
        // Given base
        $base = MaterialAccountingBase::factory()->create(['count' => 10.002]);

        // When user make post request with data
        $data = [
            'base_id' => $base->id,
            'count' => 10,
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::move_to_used'), $data);

        // Then ...
        // Base should have count 0.002, because 10 materials are used now
        $this->assertEquals(0.002, $base->refresh()->count);
        // New base should be created
        $newUsedBase = MaterialAccountingBase::get()->last();
        // With same material
        $this->assertEquals($base->manual_material_id, $newUsedBase->manual_material_id);
        // With count 10
        $this->assertEquals(10, $newUsedBase->count);
        // With used materials
        $this->assertEquals(1, $newUsedBase->used);
    }

    /** @test */
    public function user_can_move_to_used_materials_all_base_materials_and_them_can_be_grouped_with_used_materials(): void
    {
        // Given user
        $ableToMoveToUsed = [13, 14, 19, 23, 27, 31];
        $user = User::factory()->create(['group_id' => $ableToMoveToUsed[array_rand($ableToMoveToUsed)]]);
        // Given base
        $base = MaterialAccountingBase::factory()->create(['count' => 10]);
        // Given used base with same material
        $usedBase = MaterialAccountingBase::factory()->create(['object_id' => $base->object_id, 'used' => 1, 'count' => 10, 'manual_material_id' => $base->manual_material_id]);

        // When user make post request with data
        $data = [
            'base_id' => $base->id,
            'count' => 10,
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::move_to_used'), $data);

        // Then ...
        // Base should have count 0, because all materials are used now
        $this->assertEquals(0, $base->refresh()->count);
        // Materials should append to used base
        $usedBase = $usedBase->refresh();
        // With same material
        $this->assertEquals($base->manual_material_id, $usedBase->manual_material_id);
        // With count 20
        $this->assertEquals(20, $usedBase->count);
        // With used materials
        $this->assertEquals(1, $usedBase->used);
    }

    /** @test */
    public function user_can_move_to_used_materials_part_of_base_materials_and_them_can_be_grouped_with_used_materials(): void
    {
        // Given user
        $ableToMoveToUsed = [13, 14, 19, 23, 27, 31];
        $user = User::factory()->create(['group_id' => $ableToMoveToUsed[array_rand($ableToMoveToUsed)]]);
        // Given base
        $base = MaterialAccountingBase::factory()->create(['count' => 10]);
        // Given used base with same material
        $usedBase = MaterialAccountingBase::factory()->create(['object_id' => $base->object_id, 'used' => 1, 'count' => 10, 'manual_material_id' => $base->manual_material_id]);

        // When user make post request with data
        $data = [
            'base_id' => $base->id,
            'count' => 5,
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::move_to_used'), $data);

        // Then ...
        // Base should have count 5, because 5 materials are used now
        $this->assertEquals(5, $base->refresh()->count);
        // Materials should append to used base
        $usedBase = $usedBase->refresh();
        // With same material
        $this->assertEquals($base->manual_material_id, $usedBase->manual_material_id);
        // With count 15
        $this->assertEquals(15, $usedBase->count);
        // With used materials
        $this->assertEquals(1, $usedBase->used);
    }

    /** @test */
    public function user_without_permission_can_not_move_material_to_new_state(): void
    {
        // Given user without permissions
        $user = User::factory()->create();

        // Whew user make post request
        $response = $this->actingAs($user)->post(route('building::mat_acc::move_to_new'), []);

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function foremans_can_not_move_material_to_new_state(): void
    {
        // Given foreman
        $foremans = [14, 23, 31];
        $user = User::factory()->create(['group_id' => $foremans[array_rand($foremans)]]);

        // Whew user make post request
        $response = $this->actingAs($user)->post(route('building::mat_acc::move_to_new'), []);

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function RPs_can_move_material_to_new_state(): void
    {
        // Given RP
        $RPs = [13, 19, 27];
        $user = User::factory()->create(['group_id' => $RPs[array_rand($RPs)]]);

        // Whew user make post request
        $response = $this->actingAs($user)->post(route('building::mat_acc::move_to_new'), []);

        // Then user shouldn't have 403, but 302
        $response->assertStatus(302);
    }

    /** @test */
    public function user_can_not_move_to_new_more_materials_than_it_was_on_base(): void
    {
        // Given user
        $ableToMoveToNew = [14, 19, 27];
        $user = User::factory()->create(['group_id' => $ableToMoveToNew[array_rand($ableToMoveToNew)]]);
        // Given base
        $base = MaterialAccountingBase::factory()->create(['count' => 10, 'used' => 1]);

        // When user make post request with data
        $data = [
            'base_id' => $base->id,
            'count' => 12,
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::move_to_new'), $data);

        // Then user should have errors in session
        $response->assertSessionHasErrors('too_much');
    }

    /** @test */
    public function user_can_not_move_to_new_zero_materials(): void
    {
        // Given user
        $ableToMoveToNew = [14, 19, 27];
        $user = User::factory()->create(['group_id' => $ableToMoveToNew[array_rand($ableToMoveToNew)]]);
        // Given base
        $base = MaterialAccountingBase::factory()->create(['count' => 10, 'used' => 1]);

        // When user make post request with data
        $data = [
            'base_id' => $base->id,
            'count' => 0,
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::move_to_new'), $data);

        // Then user should have errors in session
        $response->assertSessionHasErrors('count');
    }

    /** @test */
    public function user_can_not_move_to_new_materials_that_was_not_exist(): void
    {
        // Given user
        $ableToMoveToNew = [14, 19, 27];
        $user = User::factory()->create(['group_id' => $ableToMoveToNew[array_rand($ableToMoveToNew)]]);
        // Given base
        $base = MaterialAccountingBase::factory()->create(['count' => 10, 'used' => 1]);

        // When user make post request with data
        $data = [
            'base_id' => ($base->id + 1),
            'count' => 10,
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::move_to_new'), $data);

        // Then user should have errors in session
        $response->assertSessionHasErrors('base_id');
    }

    /** @test */
    public function user_can_not_move_to_new_materials_that_was_not_used(): void
    {
        // Given user
        $ableToMoveToNew = [14, 19, 27];
        $user = User::factory()->create(['group_id' => $ableToMoveToNew[array_rand($ableToMoveToNew)]]);
        // Given base
        $base = MaterialAccountingBase::factory()->create(['count' => 10]);

        // When user make post request with data
        $data = [
            'base_id' => $base->id,
            'count' => 10,
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::move_to_new'), $data);

        // Then user should have errors in session
        $response->assertSessionHasErrors('base_id');
    }

    /** @test */
    public function user_can_move_to_new_materials_all_base_materials(): void
    {
        // Given user
        $ableToMoveToNew = [14, 19, 27];
        $user = User::factory()->create(['group_id' => $ableToMoveToNew[array_rand($ableToMoveToNew)]]);
        // Given base
        $base = MaterialAccountingBase::factory()->create(['count' => 10, 'used' => 1]);

        // When user make post request with data
        $data = [
            'base_id' => $base->id,
            'count' => 10,
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::move_to_new'), $data);

        // Then ...
        // Base should have count 0, because all materials are new now
        $this->assertEquals(0, $base->refresh()->count);
        // New base should be created
        $newUsedBase = MaterialAccountingBase::get()->last();
        // With same material
        $this->assertEquals($base->manual_material_id, $newUsedBase->manual_material_id);
        // With count 10
        $this->assertEquals(10, $newUsedBase->count);
        // With new materials
        $this->assertEquals(0, $newUsedBase->used);
    }

    /** @test */
    public function user_can_move_to_new_materials_part_of_base_materials(): void
    {
        // Given user
        $ableToMoveToNew = [14, 19, 27];
        $user = User::factory()->create(['group_id' => $ableToMoveToNew[array_rand($ableToMoveToNew)]]);
        // Given base
        $base = MaterialAccountingBase::factory()->create(['count' => 10, 'used' => 1]);

        // When user make post request with data
        $data = [
            'base_id' => $base->id,
            'count' => 5,
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::move_to_new'), $data);

        // Then ...
        // Base should have count 5, because 5 materials are new now
        $this->assertEquals(5, $base->refresh()->count);
        // New base should be created
        $newUsedBase = MaterialAccountingBase::get()->last();
        // With same material
        $this->assertEquals($base->manual_material_id, $newUsedBase->manual_material_id);
        // With count 5
        $this->assertEquals(5, $newUsedBase->count);
        // With new materials
        $this->assertEquals(0, $newUsedBase->used);
    }

    /** @test */
    public function user_can_move_to_new_materials_all_base_materials_and_then_can_be_grouped_with_new_materials(): void
    {
        // Given user
        $ableToMoveToNew = [14, 19, 27];
        $user = User::factory()->create(['group_id' => $ableToMoveToNew[array_rand($ableToMoveToNew)]]);
        // Given used base
        $base = MaterialAccountingBase::factory()->create(['count' => 10, 'used' => 1]);
        // Given new base with same material
        $newBase = MaterialAccountingBase::factory()->create(['object_id' => $base->object_id, 'count' => 10, 'manual_material_id' => $base->manual_material_id]);

        // When user make post request with data
        $data = [
            'base_id' => $base->id,
            'count' => 10,
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::move_to_new'), $data);

        // Then ...
        // Base should have count 0, because all materials are new now
        $this->assertEquals(0, $base->refresh()->count);
        // Materials should append to new base
        $newBase = $newBase->refresh();
        // With same material
        $this->assertEquals($base->manual_material_id, $newBase->manual_material_id);
        // With count 20
        $this->assertEquals(20, $newBase->count);
        // With new materials
        $this->assertEquals(0, $newBase->used);
    }

    /** @test */
    public function user_can_move_to_new_materials_part_of_base_materials_and_then_can_be_grouped_with_new_materials(): void
    {
        // Given user
        $ableToMoveToNew = [14, 19, 27];
        $user = User::factory()->create(['group_id' => $ableToMoveToNew[array_rand($ableToMoveToNew)]]);
        // Given base
        $base = MaterialAccountingBase::factory()->create(['count' => 10, 'used' => 1]);
        // Given new base with same material
        $newBase = MaterialAccountingBase::factory()->create(['object_id' => $base->object_id, 'count' => 10, 'manual_material_id' => $base->manual_material_id]);

        // When user make post request with data
        $data = [
            'base_id' => $base->id,
            'count' => 5,
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::move_to_new'), $data);

        // Then ...
        // Base should have count 5, because 5 materials are new now
        $this->assertEquals(5, $base->refresh()->count);
        // Materials should append to new base
        $newBase = $newBase->refresh();
        // With same material
        $this->assertEquals($base->manual_material_id, $newBase->manual_material_id);
        // With count 15
        $this->assertEquals(15, $newBase->count);
        // With new materials
        $this->assertEquals(0, $newBase->used);
    }

    /** @test */
    public function user_can_move_to_new_materials_part_of_base_materials_one_more_time(): void
    {
        // Given user
        $ableToMoveToNew = [14, 19, 27];
        $user = User::factory()->create(['group_id' => $ableToMoveToNew[array_rand($ableToMoveToNew)]]);
        // Given base
        $base = MaterialAccountingBase::factory()->create(['count' => 10.002, 'used' => 1]);

        // When user make post request with data
        $data = [
            'base_id' => $base->id,
            'count' => 10,
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::move_to_new'), $data);

        // Then ...
        // Base should have count 0.002, because 10 materials are new now
        $this->assertEquals(0.002, $base->refresh()->count);
        // New base should be created
        $newUsedBase = MaterialAccountingBase::get()->last();
        // With same material
        $this->assertEquals($base->manual_material_id, $newUsedBase->manual_material_id);
        // With count 10
        $this->assertEquals(10, $newUsedBase->count);
        // With new materials
        $this->assertEquals(0, $newUsedBase->used);
    }

    /** @test */
    public function user_without_permission_cannot_create_arrival_with_used_materials(): void
    {
        // Given user without permission
        $groupWithoutPermission = Group::whereNotIn('id', [13, 14, 19, 23, 27, 31])->inRandomOrder()->first()->id;
        $user = User::factory()->create(['group_id' => $groupWithoutPermission]);
        // Given material
        $material = ManualMaterial::inRandomOrder()->first();

        // When user try to create arrival draft with used materials
        $data = [
            'materials' => [
                'material_id' => $material->id,
                'material_unit' => 1,
                'material_count' => 10,
                'used' => 1,
            ],
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::arrival::store'), $data);

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function user_with_permission_can_create_arrival_with_used_materials(): void
    {
        // Given user with permission
        $ableToMoveToUsed = [13, 14, 19, 23, 27, 31];
        $user = User::factory()->create(['group_id' => $ableToMoveToUsed[array_rand($ableToMoveToUsed)]]);
        // Given material
        $material = ManualMaterial::inRandomOrder()->first();

        // When user try to create arrival draft with used materials
        $data = [
            'materials' => [
                'material_id' => $material->id,
                'material_unit' => 1,
                'material_count' => 10,
                'used' => 1,
            ],
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::arrival::store'), $data);

        // Then user shouldn't have 403, but 302
        $response->assertStatus(302);
    }

    /** @test */
    public function we_can_create_arrival_with_used_materials(): void
    {
        // Given user with permission
        $ableToMoveToUsed = [19, 27];
        $user = User::factory()->create(['group_id' => $ableToMoveToUsed[array_rand($ableToMoveToUsed)]]);
        // Given material
        $material = ManualMaterial::inRandomOrder()->first();
        // Given responsible user
        $responsibleUser = User::factory()->create();
        // Given supplier
        $supplier = Contractor::factory()->create();
        // Given object
        $object = ProjectObject::factory()->create();
        // Given some dates
        $plannedDateTo = now()->addDay()->format('d.m.Y');
        $plannedDateFrom = now()->format('d.m.Y');

        // When user send data
        $data = [
            'responsible_user_id' => $responsibleUser->id,
            'supplier_id' => $supplier->id,
            'object_id' => $object->id,
            'planned_date_to' => $plannedDateTo,
            'planned_date_from' => $plannedDateFrom,
            'materials' => [
                [
                    'material_id' => $material->id,
                    'material_unit' => 1,
                    'material_count' => 10,
                    'used' => 1,
                ],
            ],
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::arrival::store'), $data);

        // Then ...
        // New arrival operation should be created
        $newOperation = MaterialAccountingOperation::first();
        $this->assertEquals(1, $newOperation->type);
        $this->assertEquals($object->id, $newOperation->object_id_to);
        $this->assertEquals($supplier->id, $newOperation->supplier_id);
        // With responsible user
        $operationResponsibleUser = $newOperation->responsible_users->first();
        $this->assertEquals($responsibleUser->id, $operationResponsibleUser->user_id);
        // With used material
        $operationMaterial = $newOperation->materials->first();
        $this->assertEquals(1, $operationMaterial->used);
        $this->assertEquals($material->id, $operationMaterial->manual_material_id);
        $this->assertEquals(10, $operationMaterial->count);
    }

    /** @test */
    public function we_can_create_arrival_with_new_and_used_material(): void
    {
        // Given user with permission
        $ableToMoveToUsed = [19, 27];
        $user = User::factory()->create(['group_id' => $ableToMoveToUsed[array_rand($ableToMoveToUsed)]]);
        // Given material
        $material = ManualMaterial::inRandomOrder()->first();
        // Given responsible user
        $responsibleUser = User::factory()->create();
        // Given supplier
        $supplier = Contractor::factory()->create();
        // Given object
        $object = ProjectObject::factory()->create();
        // Given some dates
        $plannedDateTo = now()->addDay()->format('d.m.Y');
        $plannedDateFrom = now()->format('d.m.Y');

        // When user send data
        $data = [
            'responsible_user_id' => $responsibleUser->id,
            'supplier_id' => $supplier->id,
            'object_id' => $object->id,
            'planned_date_to' => $plannedDateTo,
            'planned_date_from' => $plannedDateFrom,
            'materials' => [
                [
                    'material_id' => $material->id,
                    'material_unit' => 1,
                    'material_count' => 11,
                    'used' => 0,
                ],
                [
                    'material_id' => $material->id,
                    'material_unit' => 1,
                    'material_count' => 10,
                    'used' => 1,
                ],
            ],
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::arrival::store'), $data);

        // Then ...
        // New arrival operation should be created
        $newOperation = MaterialAccountingOperation::first();
        $this->assertEquals(1, $newOperation->type);
        $this->assertEquals($object->id, $newOperation->object_id_to);
        $this->assertEquals($supplier->id, $newOperation->supplier_id);
        // With responsible user
        $operationResponsibleUser = $newOperation->responsible_users->first();
        $this->assertEquals($responsibleUser->id, $operationResponsibleUser->user_id);
        // With used material
        $operationMaterial = $newOperation->materials->first();
        $this->assertEquals(0, $operationMaterial->used);
        $this->assertEquals($material->id, $operationMaterial->manual_material_id);
        $this->assertEquals(11, $operationMaterial->count);
        // With used material
        $operationMaterial = $newOperation->materials->last();
        $this->assertEquals(1, $operationMaterial->used);
        $this->assertEquals($material->id, $operationMaterial->manual_material_id);
        $this->assertEquals(10, $operationMaterial->count);
    }

    /** @test */
    public function we_can_create_arrival_part_send_with_same_materials(): void
    {
        // Given user with permission
        $ableToMoveToUsed = [19, 27];
        $user = User::factory()->create(['group_id' => $ableToMoveToUsed[array_rand($ableToMoveToUsed)]]);
        // Given material
        $material = ManualMaterial::inRandomOrder()->first();
        // Given responsible user
        $responsibleUser = User::factory()->create();
        // Given supplier
        $supplier = Contractor::factory()->create();
        // Given object
        $object = ProjectObject::factory()->create();
        // Given some dates
        $plannedDateTo = now()->addDay()->format('d.m.Y');
        $plannedDateFrom = now()->format('d.m.Y');

        // When user send data
        $data = [
            'responsible_user_id' => $responsibleUser->id,
            'supplier_id' => $supplier->id,
            'object_id' => $object->id,
            'planned_date_to' => $plannedDateTo,
            'planned_date_from' => $plannedDateFrom,
            'materials' => [
                [
                    'material_id' => $material->id,
                    'material_unit' => 1,
                    'material_count' => 11,
                    'used' => 0,
                ],
                [
                    'material_id' => $material->id,
                    'material_unit' => 1,
                    'material_count' => 10,
                    'used' => 1,
                ],
            ],
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::arrival::store'), $data);

        // Then ...
        // New arrival operation should be created
        $newOperation = MaterialAccountingOperation::first();
        $this->assertEquals(1, $newOperation->type);
        $this->assertEquals($object->id, $newOperation->object_id_to);
        $this->assertEquals($supplier->id, $newOperation->supplier_id);
        // With responsible user
        $operationResponsibleUser = $newOperation->responsible_users->first();
        $this->assertEquals($responsibleUser->id, $operationResponsibleUser->user_id);
        // With used material
        $operationMaterial = $newOperation->materials->first();
        $this->assertEquals(0, $operationMaterial->used);
        $this->assertEquals($material->id, $operationMaterial->manual_material_id);
        $this->assertEquals(11, $operationMaterial->count);
        // With used material
        $operationMaterial = $newOperation->materials->last();
        $this->assertEquals(1, $operationMaterial->used);
        $this->assertEquals($material->id, $operationMaterial->manual_material_id);
        $this->assertEquals(10, $operationMaterial->count);

        // When user make part send with same materials
        $data = [
            'materials' => [
                [
                    'material_id' => $material->id,
                    'material_unit' => 1,
                    'material_count' => 11,
                    'used' => 0,
                ],
                [
                    'material_id' => $material->id,
                    'material_unit' => 1,
                    'material_count' => 10,
                    'used' => 1,
                ],
            ],
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::arrival::part_send', $newOperation->id), $data);
        // Arrival operation should be updated
        $newOperation->refresh();
        // With used part send material
        $operationMaterial = $newOperation->materialsPartTo->first();
        $this->assertEquals(0, $operationMaterial->used);
        $this->assertEquals($material->id, $operationMaterial->manual_material_id);
        $this->assertEquals(11, $operationMaterial->count);
        // With used material
        $operationMaterial = $newOperation->materialsPartTo->last();
        $this->assertEquals(1, $operationMaterial->used);
        $this->assertEquals($material->id, $operationMaterial->manual_material_id);
        $this->assertEquals(10, $operationMaterial->count);
    }

    /** @test */
    public function we_can_create_arrival_part_send_and_move_materials_to_used(): void
    {
        // Given user with permission
        $ableToMoveToUsed = [19, 27];
        $user = User::factory()->create(['group_id' => $ableToMoveToUsed[array_rand($ableToMoveToUsed)]]);
        // Given material
        $material = ManualMaterial::inRandomOrder()->first();
        // Given responsible user
        $responsibleUser = User::factory()->create();
        // Given supplier
        $supplier = Contractor::factory()->create();
        // Given object
        $object = ProjectObject::factory()->create();
        // Given some dates
        $plannedDateTo = now()->addDay()->format('d.m.Y');
        $plannedDateFrom = now()->format('d.m.Y');

        // When user send data
        $data = [
            'responsible_user_id' => $responsibleUser->id,
            'supplier_id' => $supplier->id,
            'object_id' => $object->id,
            'planned_date_to' => $plannedDateTo,
            'planned_date_from' => $plannedDateFrom,
            'materials' => [
                [
                    'material_id' => $material->id,
                    'material_unit' => 1,
                    'material_count' => 11,
                    'used' => 0,
                ],
                [
                    'material_id' => $material->id,
                    'material_unit' => 1,
                    'material_count' => 10,
                    'used' => 1,
                ],
            ],
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::arrival::store'), $data);

        // Then ...
        // New arrival operation should be created
        $newOperation = MaterialAccountingOperation::first();
        $this->assertEquals(1, $newOperation->type);
        $this->assertEquals($object->id, $newOperation->object_id_to);
        $this->assertEquals($supplier->id, $newOperation->supplier_id);
        // With responsible user
        $operationResponsibleUser = $newOperation->responsible_users->first();
        $this->assertEquals($responsibleUser->id, $operationResponsibleUser->user_id);
        // With used material
        $operationMaterial = $newOperation->materials->first();
        $this->assertEquals(0, $operationMaterial->used);
        $this->assertEquals($material->id, $operationMaterial->manual_material_id);
        $this->assertEquals(11, $operationMaterial->count);
        // With used material
        $operationMaterial = $newOperation->materials->last();
        $this->assertEquals(1, $operationMaterial->used);
        $this->assertEquals($material->id, $operationMaterial->manual_material_id);
        $this->assertEquals(10, $operationMaterial->count);

        // When user make part send with same materials
        $data = [
            'materials' => [
                [
                    'material_id' => $material->id,
                    'material_unit' => 1,
                    'material_count' => 11,
                    'used' => 1,
                ],
                [
                    'material_id' => $material->id,
                    'material_unit' => 1,
                    'material_count' => 10,
                    'used' => 1,
                ],
            ],
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::arrival::part_send', $newOperation->id), $data);
        // Arrival operation should be updated
        $newOperation->refresh();
        // With used part send material
        $operationMaterial = $newOperation->materialsPartTo->first();
        $this->assertEquals(1, $operationMaterial->used);
        $this->assertEquals($material->id, $operationMaterial->manual_material_id);
        $this->assertEquals(11, $operationMaterial->count);
        // With used material
        $operationMaterial = $newOperation->materialsPartTo->last();
        $this->assertEquals(1, $operationMaterial->used);
        $this->assertEquals($material->id, $operationMaterial->manual_material_id);
        $this->assertEquals(10, $operationMaterial->count);
    }

    /** @test */
    public function we_can_create_arrival_part_send_and_move_materials_to_new(): void
    {
        // Given user with permission
        $ableToMoveToUsed = [19, 27];
        $user = User::factory()->create(['group_id' => $ableToMoveToUsed[array_rand($ableToMoveToUsed)]]);
        // Given material
        $material = ManualMaterial::inRandomOrder()->first();
        // Given responsible user
        $responsibleUser = User::factory()->create();
        // Given supplier
        $supplier = Contractor::factory()->create();
        // Given object
        $object = ProjectObject::factory()->create();
        // Given some dates
        $plannedDateTo = now()->addDay()->format('d.m.Y');
        $plannedDateFrom = now()->format('d.m.Y');

        // When user send data
        $data = [
            'responsible_user_id' => $responsibleUser->id,
            'supplier_id' => $supplier->id,
            'object_id' => $object->id,
            'planned_date_to' => $plannedDateTo,
            'planned_date_from' => $plannedDateFrom,
            'materials' => [
                [
                    'material_id' => $material->id,
                    'material_unit' => 2,
                    'material_count' => 11,
                    'used' => 0,
                ],
                [
                    'material_id' => $material->id,
                    'material_unit' => 2,
                    'material_count' => 10,
                    'used' => 1,
                ],
            ],
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::arrival::store'), $data);

        // Then ...
        // New arrival operation should be created
        $newOperation = MaterialAccountingOperation::first();
        $this->assertEquals(1, $newOperation->type);
        $this->assertEquals($object->id, $newOperation->object_id_to);
        $this->assertEquals($supplier->id, $newOperation->supplier_id);
        // With responsible user
        $operationResponsibleUser = $newOperation->responsible_users->first();
        $this->assertEquals($responsibleUser->id, $operationResponsibleUser->user_id);
        // With used material
        $operationMaterial = $newOperation->materials->first();
        $this->assertEquals(0, $operationMaterial->used);
        $this->assertEquals($material->id, $operationMaterial->manual_material_id);
        $this->assertEquals(11, $operationMaterial->count);
        // With used material
        $operationMaterial = $newOperation->materials->last();
        $this->assertEquals(1, $operationMaterial->used);
        $this->assertEquals($material->id, $operationMaterial->manual_material_id);
        $this->assertEquals(10, $operationMaterial->count);

        // When user make part send with same materials
        $data = [
            'materials' => [
                [
                    'material_id' => $material->id,
                    'material_unit' => 2,
                    'material_count' => 11,
                    'used' => 0,
                ],
                [
                    'material_id' => $material->id,
                    'material_unit' => 2,
                    'material_count' => 10,
                    'used' => 0,
                ],
            ],
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::arrival::part_send', $newOperation->id), $data);
        // Arrival operation should be updated
        $newOperation->refresh();
        // With used part send material
        $operationMaterial = $newOperation->materialsPartTo->first();
        $this->assertEquals(0, $operationMaterial->used);
        $this->assertEquals($material->id, $operationMaterial->manual_material_id);
        $this->assertEquals(11, $operationMaterial->count);
        // With used material
        $operationMaterial = $newOperation->materialsPartTo->last();
        $this->assertEquals(0, $operationMaterial->used);
        $this->assertEquals($material->id, $operationMaterial->manual_material_id);
        $this->assertEquals(10, $operationMaterial->count);
    }

    /** @test */
    public function user_without_permission_cannot_create_transformation_with_used_materials(): void
    {
        // Given user without permission
        $groupWithoutPermission = Group::whereNotIn('id', [13, 14, 19, 23, 27, 31])->inRandomOrder()->first()->id;
        $user = User::factory()->create(['group_id' => $groupWithoutPermission]);
        // Given material
        $material = ManualMaterial::inRandomOrder()->first();

        // When user try to create write off draft with used materials
        $data = [
            'materials_to' => [
                [
                    'material_id' => $material->id,
                    'material_unit' => 1,
                    'material_count' => 10,
                    'used' => 1,
                ],
            ],
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::transformation::store'), $data);

        // Then user should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function user_with_permission_can_create_transformation_with_used_materials(): void
    {
        // Given user with permission
        $ableToMoveToUsed = [13, 14, 19, 23, 27, 31];
        $user = User::factory()->create(['group_id' => $ableToMoveToUsed[array_rand($ableToMoveToUsed)]]);
        // Given material
        $material = ManualMaterial::inRandomOrder()->first();

        // When user try to create write off draft with used materials
        $data = [
            'materials_to' => [
                [
                    'material_id' => $material->id,
                    'material_unit' => 1,
                    'material_count' => 10,
                    'used' => 1,
                ],
            ],
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::transformation::store'), $data);

        // Then user shouldn't have 403, but 302
        $response->assertStatus(302);
    }

    /** @test */
    public function we_can_create_transformation_1(): void
    {
        // From used material to used material
        // Given user with permission
        $ableToMoveToUsed = [27];
        $user = User::factory()->create(['group_id' => $ableToMoveToUsed[array_rand($ableToMoveToUsed)]]);
        // Given materials
        $preTransformMaterial = ManualMaterial::inRandomOrder()->first();
        $postTransformMaterial = ManualMaterial::inRandomOrder()->first();
        // Given responsible user
        $responsibleUser = User::factory()->create();
        // Given object
        $object = ProjectObject::factory()->create();
        // Given some dates
        $plannedDateTo = now()->addDay()->format('d.m.Y');

        // When user send data
        $data = [
            'responsible_user_id' => $responsibleUser->id,
            'object_id' => $object->id,
            'planned_date_to' => $plannedDateTo,
            'reason' => 'Производство работ',
            'comment' => 'HEHE',
            'materials_to' => [
                [
                    'material_id' => $preTransformMaterial->id,
                    'material_unit' => 2,
                    'material_count' => 10,
                    'used' => 1,
                ],
            ],
            'materials_from' => [
                [
                    'material_id' => $postTransformMaterial->id,
                    'material_unit' => 2,
                    'material_count' => 9,
                    'used' => 1,
                ],
            ],
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::transformation::store'), $data);

        // Then ...
        // New transformation operation should be created
        $newOperation = MaterialAccountingOperation::first();
        $this->assertEquals(3, $newOperation->type);
        $this->assertEquals($object->id, $newOperation->object_id_from);
        // With responsible user
        $operationResponsibleUser = $newOperation->responsible_users->first();
        $this->assertEquals($responsibleUser->id, $operationResponsibleUser->user_id);
        // With used material
        $operationMaterial = $newOperation->materials->first();
        $this->assertEquals(1, $operationMaterial->used);
        $this->assertEquals($postTransformMaterial->id, $operationMaterial->manual_material_id);
        $this->assertEquals(9, $operationMaterial->count);
    }

    /** @test */
    public function we_can_create_transformation_2(): void
    {
        // From used materials to used materials
        // Given user with permission
        $ableToMoveToUsed = [27];
        $user = User::factory()->create(['group_id' => $ableToMoveToUsed[array_rand($ableToMoveToUsed)]]);
        // Given materials
        $preTransformMaterial1 = ManualMaterial::inRandomOrder()->first();
        $postTransformMaterial1 = ManualMaterial::inRandomOrder()->first();
        $preTransformMaterial2 = ManualMaterial::inRandomOrder()->first();
        $postTransformMaterial2 = ManualMaterial::inRandomOrder()->first();
        // Given responsible user
        $responsibleUser = User::factory()->create();
        // Given object
        $object = ProjectObject::factory()->create();
        // Given some dates
        $plannedDateTo = now()->addDay()->format('d.m.Y');

        // When user send data
        $data = [
            'responsible_user_id' => $responsibleUser->id,
            'object_id' => $object->id,
            'planned_date_to' => $plannedDateTo,
            'reason' => 'Производство работ',
            'comment' => 'HEHE',
            'materials_to' => [
                [
                    'material_id' => $preTransformMaterial1->id,
                    'material_unit' => 2,
                    'material_count' => 2,
                    'used' => 1,
                ],
                [
                    'material_id' => $preTransformMaterial2->id,
                    'material_unit' => 2,
                    'material_count' => 3,
                    'used' => 1,
                ],
            ],
            'materials_from' => [
                [
                    'material_id' => $postTransformMaterial1->id,
                    'material_unit' => 2,
                    'material_count' => 4,
                    'used' => 1,
                ],
                [
                    'material_id' => $postTransformMaterial2->id,
                    'material_unit' => 2,
                    'material_count' => 5,
                    'used' => 1,
                ],
            ],
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::transformation::store'), $data);

        // Then ...
        // New transformation operation should be created
        $newOperation = MaterialAccountingOperation::first();
        $this->assertEquals(3, $newOperation->type);
        $this->assertEquals($object->id, $newOperation->object_id_from);
        // With responsible user
        $operationResponsibleUser = $newOperation->responsible_users->first();
        $this->assertEquals($responsibleUser->id, $operationResponsibleUser->user_id);
        // With used material
        $operationMaterials = $newOperation->materials->where('type', 7);
        $this->assertEquals([1, 1], $operationMaterials->pluck('used')->toArray());
        $this->assertEquals([$postTransformMaterial1->id, $postTransformMaterial2->id], $operationMaterials->pluck('manual_material_id')->toArray());
        $this->assertEquals([4, 5], $operationMaterials->pluck('count')->toArray());
    }

    /** @test */
    public function we_can_create_transformation_3(): void
    {
        // From new material to new material
        // Given user with permission
        $ableToMoveToUsed = [27];
        $user = User::factory()->create(['group_id' => $ableToMoveToUsed[array_rand($ableToMoveToUsed)]]);
        // Given materials
        $preTransformMaterial = ManualMaterial::inRandomOrder()->first();
        $postTransformMaterial = ManualMaterial::inRandomOrder()->first();
        // Given responsible user
        $responsibleUser = User::factory()->create();
        // Given object
        $object = ProjectObject::factory()->create();
        // Given some dates
        $plannedDateTo = now()->addDay()->format('d.m.Y');

        // When user send data
        $data = [
            'responsible_user_id' => $responsibleUser->id,
            'object_id' => $object->id,
            'planned_date_to' => $plannedDateTo,
            'reason' => 'Производство работ',
            'comment' => 'HEHE',
            'materials_to' => [
                [
                    'material_id' => $preTransformMaterial->id,
                    'material_unit' => 2,
                    'material_count' => 10,
                    'used' => 0,
                ],
            ],
            'materials_from' => [
                [
                    'material_id' => $postTransformMaterial->id,
                    'material_unit' => 2,
                    'material_count' => 9,
                    'used' => 0,
                ],
            ],
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::transformation::store'), $data);

        // Then ...
        // New transformation operation should be created
        $newOperation = MaterialAccountingOperation::first();
        $this->assertEquals(3, $newOperation->type);
        $this->assertEquals($object->id, $newOperation->object_id_from);
        // With responsible user
        $operationResponsibleUser = $newOperation->responsible_users->first();
        $this->assertEquals($responsibleUser->id, $operationResponsibleUser->user_id);
        // With new material
        $operationMaterial = $newOperation->materials->first();
        $this->assertEquals(0, $operationMaterial->used);
        $this->assertEquals($postTransformMaterial->id, $operationMaterial->manual_material_id);
        $this->assertEquals(9, $operationMaterial->count);
    }

    /** @test */
    public function we_can_create_transformation_4(): void
    {
        // From new materials to new materials
        // Given user with permission
        $ableToMoveToUsed = [27];
        $user = User::factory()->create(['group_id' => $ableToMoveToUsed[array_rand($ableToMoveToUsed)]]);
        // Given materials
        $preTransformMaterial1 = ManualMaterial::inRandomOrder()->first();
        $postTransformMaterial1 = ManualMaterial::inRandomOrder()->first();
        $preTransformMaterial2 = ManualMaterial::inRandomOrder()->first();
        $postTransformMaterial2 = ManualMaterial::inRandomOrder()->first();
        // Given responsible user
        $responsibleUser = User::factory()->create();
        // Given object
        $object = ProjectObject::factory()->create();
        // Given some dates
        $plannedDateTo = now()->addDay()->format('d.m.Y');

        // When user send data
        $data = [
            'responsible_user_id' => $responsibleUser->id,
            'object_id' => $object->id,
            'planned_date_to' => $plannedDateTo,
            'reason' => 'Производство работ',
            'comment' => 'HEHE',
            'materials_to' => [
                [
                    'material_id' => $preTransformMaterial1->id,
                    'material_unit' => 2,
                    'material_count' => 2,
                    'used' => 0,
                ],
                [
                    'material_id' => $preTransformMaterial2->id,
                    'material_unit' => 2,
                    'material_count' => 3,
                    'used' => 0,
                ],
            ],
            'materials_from' => [
                [
                    'material_id' => $postTransformMaterial1->id,
                    'material_unit' => 2,
                    'material_count' => 4,
                    'used' => 0,
                ],
                [
                    'material_id' => $postTransformMaterial2->id,
                    'material_unit' => 2,
                    'material_count' => 5,
                    'used' => 0,
                ],
            ],
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::transformation::store'), $data);

        // Then ...
        // New transformation operation should be created
        $newOperation = MaterialAccountingOperation::first();
        $this->assertEquals(3, $newOperation->type);
        $this->assertEquals($object->id, $newOperation->object_id_from);
        // With responsible user
        $operationResponsibleUser = $newOperation->responsible_users->first();
        $this->assertEquals($responsibleUser->id, $operationResponsibleUser->user_id);
        // With new materials
        $operationMaterials = $newOperation->materials->where('type', 7);
        $this->assertEquals([0, 0], $operationMaterials->pluck('used')->toArray());
        $this->assertEquals([$postTransformMaterial1->id, $postTransformMaterial2->id], $operationMaterials->pluck('manual_material_id')->toArray());
        $this->assertEquals([4, 5], $operationMaterials->pluck('count')->toArray());
    }

    /** @test */
    public function we_can_create_transformation_5(): void
    {
        // From used material to new material
        // Given user with permission
        $ableToMoveToUsed = [27];
        $user = User::factory()->create(['group_id' => $ableToMoveToUsed[array_rand($ableToMoveToUsed)]]);
        // Given materials
        $preTransformMaterial = ManualMaterial::inRandomOrder()->first();
        $postTransformMaterial = ManualMaterial::inRandomOrder()->first();
        // Given responsible user
        $responsibleUser = User::factory()->create();
        // Given object
        $object = ProjectObject::factory()->create();
        // Given some dates
        $plannedDateTo = now()->addDay()->format('d.m.Y');

        // When user send data
        $data = [
            'responsible_user_id' => $responsibleUser->id,
            'object_id' => $object->id,
            'planned_date_to' => $plannedDateTo,
            'reason' => 'Производство работ',
            'comment' => 'HEHE',
            'materials_to' => [
                [
                    'material_id' => $preTransformMaterial->id,
                    'material_unit' => 2,
                    'material_count' => 10,
                    'used' => 1,
                ],
            ],
            'materials_from' => [
                [
                    'material_id' => $postTransformMaterial->id,
                    'material_unit' => 2,
                    'material_count' => 9,
                    'used' => 0,
                ],
            ],
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::transformation::store'), $data);

        // Then ...
        // New transformation operation should be created
        $newOperation = MaterialAccountingOperation::first();
        $this->assertEquals(3, $newOperation->type);
        $this->assertEquals($object->id, $newOperation->object_id_from);
        // With responsible user
        $operationResponsibleUser = $newOperation->responsible_users->first();
        $this->assertEquals($responsibleUser->id, $operationResponsibleUser->user_id);
        // With new material
        $operationMaterial = $newOperation->materials->first();
        $this->assertEquals(0, $operationMaterial->used);
        $this->assertEquals($postTransformMaterial->id, $operationMaterial->manual_material_id);
        $this->assertEquals(9, $operationMaterial->count);
    }

    /** @test */
    public function we_can_create_transformation_6(): void
    {
        // From used materials to new materials
        // Given user with permission
        $ableToMoveToUsed = [27];
        $user = User::factory()->create(['group_id' => $ableToMoveToUsed[array_rand($ableToMoveToUsed)]]);
        // Given materials
        $preTransformMaterial1 = ManualMaterial::inRandomOrder()->first();
        $postTransformMaterial1 = ManualMaterial::inRandomOrder()->first();
        $preTransformMaterial2 = ManualMaterial::inRandomOrder()->first();
        $postTransformMaterial2 = ManualMaterial::inRandomOrder()->first();
        // Given responsible user
        $responsibleUser = User::factory()->create();
        // Given object
        $object = ProjectObject::factory()->create();
        // Given some dates
        $plannedDateTo = now()->addDay()->format('d.m.Y');

        // When user send data
        $data = [
            'responsible_user_id' => $responsibleUser->id,
            'object_id' => $object->id,
            'planned_date_to' => $plannedDateTo,
            'reason' => 'Производство работ',
            'comment' => 'HEHE',
            'materials_to' => [
                [
                    'material_id' => $preTransformMaterial1->id,
                    'material_unit' => 2,
                    'material_count' => 2,
                    'used' => 1,
                ],
                [
                    'material_id' => $preTransformMaterial2->id,
                    'material_unit' => 2,
                    'material_count' => 3,
                    'used' => 1,
                ],
            ],
            'materials_from' => [
                [
                    'material_id' => $postTransformMaterial1->id,
                    'material_unit' => 2,
                    'material_count' => 4,
                    'used' => 0,
                ],
                [
                    'material_id' => $postTransformMaterial2->id,
                    'material_unit' => 2,
                    'material_count' => 5,
                    'used' => 0,
                ],
            ],
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::transformation::store'), $data);

        // Then ...
        // New transformation operation should be created
        $newOperation = MaterialAccountingOperation::first();
        $this->assertEquals(3, $newOperation->type);
        $this->assertEquals($object->id, $newOperation->object_id_from);
        // With responsible user
        $operationResponsibleUser = $newOperation->responsible_users->first();
        $this->assertEquals($responsibleUser->id, $operationResponsibleUser->user_id);
        // With new material
        $operationMaterials = $newOperation->materials->where('type', 7);
        $this->assertEquals([0, 0], $operationMaterials->pluck('used')->toArray());
        $this->assertEquals([$postTransformMaterial1->id, $postTransformMaterial2->id], $operationMaterials->pluck('manual_material_id')->toArray());
        $this->assertEquals([4, 5], $operationMaterials->pluck('count')->toArray());
    }

    /** @test */
    public function we_can_create_transformation_7(): void
    {
        // From new material to used material
        // Given user with permission
        $ableToMoveToUsed = [27];
        $user = User::factory()->create(['group_id' => $ableToMoveToUsed[array_rand($ableToMoveToUsed)]]);
        // Given materials
        $preTransformMaterial = ManualMaterial::inRandomOrder()->first();
        $postTransformMaterial = ManualMaterial::inRandomOrder()->first();
        // Given responsible user
        $responsibleUser = User::factory()->create();
        // Given object
        $object = ProjectObject::factory()->create();
        // Given some dates
        $plannedDateTo = now()->addDay()->format('d.m.Y');

        // When user send data
        $data = [
            'responsible_user_id' => $responsibleUser->id,
            'object_id' => $object->id,
            'planned_date_to' => $plannedDateTo,
            'reason' => 'Производство работ',
            'comment' => 'HEHE',
            'materials_to' => [
                [
                    'material_id' => $preTransformMaterial->id,
                    'material_unit' => 2,
                    'material_count' => 10,
                    'used' => 0,
                ],
            ],
            'materials_from' => [
                [
                    'material_id' => $postTransformMaterial->id,
                    'material_unit' => 2,
                    'material_count' => 9,
                    'used' => 1,
                ],
            ],
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::transformation::store'), $data);

        // Then ...
        // New transformation operation should be created
        $newOperation = MaterialAccountingOperation::first();
        $this->assertEquals(3, $newOperation->type);
        $this->assertEquals($object->id, $newOperation->object_id_from);
        // With responsible user
        $operationResponsibleUser = $newOperation->responsible_users->first();
        $this->assertEquals($responsibleUser->id, $operationResponsibleUser->user_id);
        // With used material
        $operationMaterial = $newOperation->materials->first();
        $this->assertEquals(1, $operationMaterial->used);
        $this->assertEquals($postTransformMaterial->id, $operationMaterial->manual_material_id);
        $this->assertEquals(9, $operationMaterial->count);
    }

    /** @test */
    public function we_can_create_transformation_8(): void
    {
        // From new materials to used materials
        // Given user with permission
        $ableToMoveToUsed = [27];
        $user = User::factory()->create(['group_id' => $ableToMoveToUsed[array_rand($ableToMoveToUsed)]]);
        // Given materials
        $preTransformMaterial1 = ManualMaterial::inRandomOrder()->first();
        $postTransformMaterial1 = ManualMaterial::inRandomOrder()->first();
        $preTransformMaterial2 = ManualMaterial::inRandomOrder()->first();
        $postTransformMaterial2 = ManualMaterial::inRandomOrder()->first();
        // Given responsible user
        $responsibleUser = User::factory()->create();
        // Given object
        $object = ProjectObject::factory()->create();
        // Given some dates
        $plannedDateTo = now()->addDay()->format('d.m.Y');

        // When user send data
        $data = [
            'responsible_user_id' => $responsibleUser->id,
            'object_id' => $object->id,
            'planned_date_to' => $plannedDateTo,
            'reason' => 'Производство работ',
            'comment' => 'HEHE',
            'materials_to' => [
                [
                    'material_id' => $preTransformMaterial1->id,
                    'material_unit' => 2,
                    'material_count' => 2,
                    'used' => 0,
                ],
                [
                    'material_id' => $preTransformMaterial2->id,
                    'material_unit' => 2,
                    'material_count' => 3,
                    'used' => 0,
                ],
            ],
            'materials_from' => [
                [
                    'material_id' => $postTransformMaterial1->id,
                    'material_unit' => 2,
                    'material_count' => 4,
                    'used' => 1,
                ],
                [
                    'material_id' => $postTransformMaterial2->id,
                    'material_unit' => 2,
                    'material_count' => 5,
                    'used' => 1,
                ],
            ],
        ];
        $response = $this->actingAs($user)->post(route('building::mat_acc::transformation::store'), $data);

        // Then ...
        // New transformation operation should be created
        $newOperation = MaterialAccountingOperation::first();
        $this->assertEquals(3, $newOperation->type);
        $this->assertEquals($object->id, $newOperation->object_id_from);
        // With responsible user
        $operationResponsibleUser = $newOperation->responsible_users->first();
        $this->assertEquals($responsibleUser->id, $operationResponsibleUser->user_id);
        // With used material
        $operationMaterials = $newOperation->materials->where('type', 7);
        $this->assertEquals([1, 1], $operationMaterials->pluck('used')->toArray());
        $this->assertEquals([$postTransformMaterial1->id, $postTransformMaterial2->id], $operationMaterials->pluck('manual_material_id')->toArray());
        $this->assertEquals([4, 5], $operationMaterials->pluck('count')->toArray());
    }

    /** @test */
    public function it_can_filter_bases_by_reference(): void
    {
        $newBase = MaterialAccountingBase::factory()->count(5)->create(['used' => 0]);

        //  0 => 19
        //    1 => 20
        //    2 => 121
        //    3 => 122
        //    4 => 359335
        //    5 => 359346
        //    6 => 359656
        //    7 => 359658
        //    8 => 359667

        $mats = [
            0 => 19,
            1 => 20,
            2 => 121,
            3 => 122,
            4 => 359335,
            5 => 359346,
            6 => 359656,
            7 => 359658,
            8 => 359667,
        ];
        foreach ($mats as $mat_id) {
            MaterialAccountingBase::factory()->create(['used' => 0, 'manual_material_id' => $mat_id]);
        }

        $reference = ManualReference::first();
        $this->actingAs(User::find(1));

        $payload = [
            'filter' => [
                [
                    'parameter_id' => 3,
                    'value_id' => [
                        //                        'reference_name' => $reference->name,
                        'reference_id' => $reference->id,
                        'parameters' => [
                            [
                                'attr_id' => $reference->category->attributes()->where('name', 'Длина')->first()->id,
                                'value' => [
                                    'from' => 0,
                                    'to' => 16,
                                ],
                            ],
                        ],
                    ],
                ],
            ]];

        //        $response = $this->get(route('building::mat_acc::report_card'))->viewData('bases');
        $response = $this->post(route('building::mat_acc::report_card::filter_base'), $payload)->json('result');
        dd($response);
    }

    /** @test */
    public function it_test_getter(): void
    {
        $reference = ManualReference::first();
        $this->actingAs(User::find(1));
        $payload = [
            'attr_id' => $reference->category->attributes()->where('name', 'Длина')->first()->id,
            'reference_name' => $reference->name,
        ];
        $response = $this->post(route('building::materials::select_attr_value'), $payload)->json();

        dd($response);
    }

    /** @test */
    public function it_offers_conflict_solution(): void
    {
        $material = ManualMaterial::first();
        $object = ProjectObject::first();
        $old_base = MaterialAccountingBase::factory()->create([
            'manual_material_id' => $material->id,
            'count' => 100,
            'object_id' => $object->id,
            'used' => 0,
        ]);
        $base = MaterialAccountingBase::factory()->create([
            'manual_material_id' => $material->id,
            'count' => 5,
            'object_id' => $object->id,
            'unit' => 'т',
            'used' => 1,
        ]);
        $base->refresh();

        $operation_mat = [
            'material_id' => $material->id,
            'material_unit' => array_flip((new MaterialAccountingOperation())->units_name)['т'],
            'material_count' => 10,
            'used' => 1,
        ];

        $payload = [
            'materials' => [
                $operation_mat,
            ],
            'object_id' => $object->id,
            'planned_date_to' => Carbon::now(),
        ];

        $this->withoutExceptionHandling();
        $response = $this->actingAs(User::first())->post(route('building::mat_acc::suggest_solution'), $payload);
        $response->assertOk();

        $expected_result = [
            'transform' => true,
            'failure' => false,
            'solutions' => [
                $old_base->id => [
                    'status' => 'transform',
                    'to' => $base->id,
                    'count' => 5,
                    'message' => 'На '.Carbon::now()->isoFormat('DD.MM.YYYY')." имеется 5 т {$material->name} выбранного вами б/у материала. 5 т будет переведено в б/у автоматически.",
                ],
            ],
        ];
        $this->assertEquals($expected_result, $response->json());
    }

    /** @test */
    public function it_offers_conflict_solution_for_new_mat(): void
    {
        $material = ManualMaterial::first();
        $object = ProjectObject::first();
        $old_base = MaterialAccountingBase::factory()->create([
            'manual_material_id' => $material->id,
            'count' => 100,
            'object_id' => $object->id,
            'used' => 1,
        ]);
        $base = MaterialAccountingBase::factory()->create([
            'manual_material_id' => $material->id,
            'count' => 5,
            'object_id' => $object->id,
            'unit' => 'т',
            'used' => 0,
        ]);
        $base->refresh();

        $operation_mat = [
            'material_id' => $material->id,
            'material_unit' => array_flip((new MaterialAccountingOperation())->units_name)['т'],
            'material_count' => 10,
            'used' => 0,
        ];

        $payload = [
            'materials' => [
                $operation_mat,
            ],
            'object_id' => $object->id,
            'planned_date_to' => Carbon::now(),
        ];

        $this->withoutExceptionHandling();
        $response = $this->actingAs(User::first())->post(route('building::mat_acc::suggest_solution'), $payload);
        $response->assertOk();

        $expected_result = [
            'transform' => true,
            'failure' => false,
            'solutions' => [
                $old_base->id => [
                    'status' => 'transform',
                    'to' => $base->id,
                    'count' => 5,
                    'message' => 'На '.Carbon::now()->isoFormat('DD.MM.YYYY')." имеется 5 т {$material->name} выбранного вами нового материала. 5 т будет переведено в новый автоматически.",
                ],
            ],
        ];
        $this->assertEquals($expected_result, $response->json());
    }
}
