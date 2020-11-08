<?php

namespace Tests\Feature\Commerce;

use App\Models\Contract\Contract;
use App\Models\Contractors\Contractor;
use App\Models\Project;
use App\Models\ProjectObject;
use App\Models\User;
use Tests\TestCase;

class ContractTest extends TestCase
{
    const USERS_THAT_CAN_WORK_WITH_CONTRACTS = [5, 6, 7, 8, 9, 13, 19, 27, 49, 50, 52, 53, 54];

    /** @test */
    public function filter_scope_can_return_nothing_if_no_contracts_given(): void
    {
        // Given no contracts
        Contract::query()->delete();
        // Given user
        $user = factory(User::class)->create(['group_id' => self::USERS_THAT_CAN_WORK_WITH_CONTRACTS[array_rand(self::USERS_THAT_CAN_WORK_WITH_CONTRACTS)]]);

        // When we use filter scope
        $result = $this->actingAs($user)->post(route('contracts::filtered'), [])->json();

        // Then results should be empty
        $this->assertEmpty($result['contracts']);
    }

    /** @test */
    public function filter_scope_can_return_all_contracts_without_filters(): void
    {
        // Given contracts
        $contracts = factory(Contract::class, 5)->create();
        // Given user
        $user = factory(User::class)->create(['group_id' => self::USERS_THAT_CAN_WORK_WITH_CONTRACTS[array_rand(self::USERS_THAT_CAN_WORK_WITH_CONTRACTS)]]);

        // When we use filter scope
        $result = $this->actingAs($user)->post(route('contracts::filtered'), [])->json();

        // Then results should have five contracts
        $this->assertNotEmpty($result['contracts']);
        $this->assertCount(5, $result['contracts']);
        $this->assertEquals($contracts->reverse()->pluck('id'), collect($result['contracts'])->pluck('id'));
    }

    /** @test */
    public function filter_scope_can_filter_contracts_by_id(): void
    {
        // Given contracts
        $contracts = factory(Contract::class, 5)->create();
        // And special one
        $contract = factory(Contract::class)->create(['contract_id' => 101]);
        // Given user
        $user = factory(User::class)->create(['group_id' => self::USERS_THAT_CAN_WORK_WITH_CONTRACTS[array_rand(self::USERS_THAT_CAN_WORK_WITH_CONTRACTS)]]);

        // When we use filter scope
        $result = $this->actingAs($user)->post(route('contracts::filtered', ['url' => route('contracts::index') . "?contracts.contract_id={$contract->contract_id}"]))->json();

        // Then results should have one contract
        $this->assertNotEmpty($result['contracts']);
        $this->assertCount(1, $result['contracts']);
        $this->assertEquals($contract->id, $result['contracts'][0]['id']);
    }

    /** @test */
    public function filter_scope_can_filter_contracts_by_ids_array(): void
    {
        // Given contracts
        $contracts = factory(Contract::class, 5)->create(['contract_id' => 102]);
        // And special one
        $contract = factory(Contract::class)->create(['contract_id' => 101]);
        // Given user
        $user = factory(User::class)->create(['group_id' => self::USERS_THAT_CAN_WORK_WITH_CONTRACTS[array_rand(self::USERS_THAT_CAN_WORK_WITH_CONTRACTS)]]);

        // When we use filter scope
        $result = $this->actingAs($user)->post(route('contracts::filtered', [
            'url' => route('contracts::index') . "?contracts.contract_id%5B0%5D=102&contracts.contract_id%5B1%5D=101"
        ]))->json();

        // Then results should have six contracts
        $this->assertNotEmpty($result['contracts']);
        $this->assertCount(6, $result['contracts']);
        $this->assertEquals($contracts->push($contract)->reverse()->pluck('id'), collect($result['contracts'])->pluck('id'));
    }

    /** @test */
    public function filter_scope_can_filter_contracts_by_foreign_id(): void
    {
        // Given contracts
        $contracts = factory(Contract::class, 5)->create();
        // And special one
        $contract = factory(Contract::class)->create(['foreign_id' => 'СПЕЦИАЛЬНЫЙ']);
        // Given user
        $user = factory(User::class)->create(['group_id' => self::USERS_THAT_CAN_WORK_WITH_CONTRACTS[array_rand(self::USERS_THAT_CAN_WORK_WITH_CONTRACTS)]]);

        // When we use filter scope
        $result = $this->actingAs($user)->post(route('contracts::filtered', ['url' => route('contracts::index') . "?contracts.foreign_id={$contract->foreign_id}"]))->json();

        // Then results should have one contract
        $this->assertNotEmpty($result['contracts']);
        $this->assertCount(1, $result['contracts']);
        $this->assertEquals($contract->id, $result['contracts'][0]['id']);
    }

    /** @test */
    public function filter_scope_can_filter_contracts_by_foreign_ids_array(): void
    {
        // Given contracts
        $contracts = factory(Contract::class, 5)->create(['foreign_id' => 'СП-2019']);
        // And special one
        $contract = factory(Contract::class)->create(['foreign_id' => '874 КА']);
        // Given user
        $user = factory(User::class)->create(['group_id' => self::USERS_THAT_CAN_WORK_WITH_CONTRACTS[array_rand(self::USERS_THAT_CAN_WORK_WITH_CONTRACTS)]]);

        // When we use filter scope
        $result = $this->actingAs($user)->post(route('contracts::filtered', [
            'url' => route('contracts::index') . '?contracts.foreign_id%5B0%5D=СП&contracts.foreign_id%5B1%5D=КА'
        ]))->json();

        // Then results should have six contracts
        $this->assertNotEmpty($result['contracts']);
        $this->assertCount(6, $result['contracts']);
        $this->assertEquals($contracts->push($contract)->reverse()->pluck('id'), collect($result['contracts'])->pluck('id'));
    }

    /** @test */
    public function filter_scope_can_filter_contracts_by_contractor_short_name(): void
    {
        // Given contracts
        $contracts = factory(Contract::class, 5)->create();
        // Given contractor
        $contractor = factory(Contractor::class)->create();
        // Given project with contractor
        $project = factory(Project::class)->create(['contractor_id' => $contractor->id]);
        // And special one
        $contract = factory(Contract::class)->create(['project_id' => $project->id]);
        // Given user
        $user = factory(User::class)->create(['group_id' => self::USERS_THAT_CAN_WORK_WITH_CONTRACTS[array_rand(self::USERS_THAT_CAN_WORK_WITH_CONTRACTS)]]);

        // When we use filter scope
        $result = $this->actingAs($user)->post(route('contracts::filtered', ['url' => route('contracts::index') . "?contractors.short_name={$contractor->short_name}"]))->json();

        // Then results should have one contract
        $this->assertNotEmpty($result['contracts']);
        $this->assertCount(1, $result['contracts']);
        $this->assertEquals($contract->id, $result['contracts'][0]['id']);
    }

    /** @test */
    public function filter_scope_can_filter_contracts_by_contractor_short_names_array(): void
    {
        // Given contractor
        $contractor = factory(Contractor::class)->create();
        // Given project with contractor
        $project = factory(Project::class)->create(['contractor_id' => $contractor->id]);
        // Given contracts
        $contracts = factory(Contract::class, 5)->create(['project_id' => $project->id]);
        // Given another contractor
        $anotherContractor = factory(Contractor::class)->create();
        // Given another project with contractor
        $anotherProject = factory(Project::class)->create(['contractor_id' => $anotherContractor->id]);
        // And special one
        $contract = factory(Contract::class)->create(['project_id' => $anotherProject->id]);
        // Given user
        $user = factory(User::class)->create(['group_id' => self::USERS_THAT_CAN_WORK_WITH_CONTRACTS[array_rand(self::USERS_THAT_CAN_WORK_WITH_CONTRACTS)]]);

        // When we use filter scope
        $result = $this->actingAs($user)->post(route('contracts::filtered', [
            'url' => route('contracts::index') . "?contractors.short_name%5B0%5D={$contractor->short_name}&contractors.short_name%5B1%5D={$anotherContractor->short_name}"
        ]))->json();

        // Then results should have six contracts
        $this->assertNotEmpty($result['contracts']);
        $this->assertCount(6, $result['contracts']);
        $this->assertEquals($contracts->push($contract)->reverse()->pluck('id'), collect($result['contracts'])->pluck('id'));
    }

    /** @test */
    public function filter_scope_can_filter_contracts_by_project_object_address(): void
    {
        // Given contracts
        $contracts = factory(Contract::class, 5)->create();
        // Given object
        $object = factory(ProjectObject::class)->create();
        // Given project with object
        $project = factory(Project::class)->create(['object_id' => $object->id]);
        // And special one
        $contract = factory(Contract::class)->create(['project_id' => $project->id]);
        // Given user
        $user = factory(User::class)->create(['group_id' => self::USERS_THAT_CAN_WORK_WITH_CONTRACTS[array_rand(self::USERS_THAT_CAN_WORK_WITH_CONTRACTS)]]);

        // When we use filter scope
        $result = $this->actingAs($user)->post(route('contracts::filtered', ['url' => route('contracts::index') . "?project_objects.address={$object->address}"]))->json();

        // Then results should have one contract
        $this->assertNotEmpty($result['contracts']);
        $this->assertCount(1, $result['contracts']);
        $this->assertEquals($contract->id, $result['contracts'][0]['id']);
    }

    /** @test */
    public function filter_scope_can_filter_contracts_by_project_object_addresses_array(): void
    {
        // Given object
        $object = factory(ProjectObject::class)->create();
        // Given project with object
        $project = factory(Project::class)->create(['object_id' => $object->id]);
        // Given contracts
        $contracts = factory(Contract::class, 5)->create(['project_id' => $project->id]);
        // Given another object
        $anotherObject = factory(ProjectObject::class)->create();
        // Given another project with object
        $anotherProject = factory(Project::class)->create(['object_id' => $object->id]);
        // And special one
        $contract = factory(Contract::class)->create(['project_id' => $anotherProject->id]);
        // Given user
        $user = factory(User::class)->create(['group_id' => self::USERS_THAT_CAN_WORK_WITH_CONTRACTS[array_rand(self::USERS_THAT_CAN_WORK_WITH_CONTRACTS)]]);

        // When we use filter scope
        $result = $this->actingAs($user)->post(route('contracts::filtered', [
            'url' => route('contracts::index') . "?project_objects.address%5B0%5D={$object->address}&project_objects.address%5B1%5D={$anotherObject->id}"
        ]))->json();

        // Then results should have six contracts
        $this->assertNotEmpty($result['contracts']);
        $this->assertCount(6, $result['contracts']);
        $this->assertEquals($contracts->push($contract)->reverse()->pluck('id'), collect($result['contracts'])->pluck('id'));
    }

    /** @test */
    public function filter_scope_can_filter_contracts_by_project_name(): void
    {
        // Given contracts
        $contracts = factory(Contract::class, 5)->create();
        // Given project
        $project = factory(Project::class)->create();
        // And special one
        $contract = factory(Contract::class)->create(['project_id' => $project->id]);
        // Given user
        $user = factory(User::class)->create(['group_id' => self::USERS_THAT_CAN_WORK_WITH_CONTRACTS[array_rand(self::USERS_THAT_CAN_WORK_WITH_CONTRACTS)]]);

        // When we use filter scope
        $result = $this->actingAs($user)->post(route('contracts::filtered', ['url' => route('contracts::index') . "?projects.name={$project->name}"]))->json();

        // Then results should have one contract
        $this->assertNotEmpty($result['contracts']);
        $this->assertCount(1, $result['contracts']);
        $this->assertEquals($contract->id, $result['contracts'][0]['id']);
    }

    /** @test */
    public function filter_scope_can_filter_contracts_by_project_names_array(): void
    {
        // Given project with
        $project = factory(Project::class)->create();
        // Given contracts
        $contracts = factory(Contract::class, 5)->create(['project_id' => $project->id]);
        // Given another project
        $anotherProject = factory(Project::class)->create();
        // And special one
        $contract = factory(Contract::class)->create(['project_id' => $anotherProject->id]);
        // Given user
        $user = factory(User::class)->create(['group_id' => self::USERS_THAT_CAN_WORK_WITH_CONTRACTS[array_rand(self::USERS_THAT_CAN_WORK_WITH_CONTRACTS)]]);

        // When we use filter scope
        $result = $this->actingAs($user)->post(route('contracts::filtered', [
            'url' => route('contracts::index') . "?projects.name%5B0%5D={$project->name}&projects.name%5B1%5D={$anotherProject->name}"
        ]))->json();

        // Then results should have six contracts
        $this->assertNotEmpty($result['contracts']);
        $this->assertCount(6, $result['contracts']);
        $this->assertEquals($contracts->push($contract)->reverse()->pluck('id'), collect($result['contracts'])->pluck('id'));
    }

    /** @test */
    public function filter_scope_can_filter_contracts_by_name(): void
    {
        // Given contracts
        $contracts = factory(Contract::class, 5)->create(['name' => 'Договор услуг']);
        // And special one
        $contract = factory(Contract::class)->create(['name' => 'Договор с заказчиком']);
        // Given user
        $user = factory(User::class)->create(['group_id' => self::USERS_THAT_CAN_WORK_WITH_CONTRACTS[array_rand(self::USERS_THAT_CAN_WORK_WITH_CONTRACTS)]]);

        // When we use filter scope
        $result = $this->actingAs($user)->post(route('contracts::filtered', ['url' => route('contracts::index') . "?contracts.name={$contract->name}"]))->json();

        // Then results should have one contract
        $this->assertNotEmpty($result['contracts']);
        $this->assertCount(1, $result['contracts']);
        $this->assertEquals($contract->id, $result['contracts'][0]['id']);
    }

    /** @test */
    public function filter_scope_can_filter_contracts_by_names_array(): void
    {
        // Given contracts
        $contracts = factory(Contract::class, 5)->create(['name' => 'Договор услуг']);
        // And special one
        $contract = factory(Contract::class)->create(['name' => 'Договор поставки']);
        // Given user
        $user = factory(User::class)->create(['group_id' => self::USERS_THAT_CAN_WORK_WITH_CONTRACTS[array_rand(self::USERS_THAT_CAN_WORK_WITH_CONTRACTS)]]);

        // When we use filter scope
        $result = $this->actingAs($user)->post(route('contracts::filtered', [
            'url' => route('contracts::index') . "?contracts.name%5B0%5D={$contracts[0]->name}&contracts.name%5B1%5D={$contract->name}"
        ]))->json();

        // Then results should have six contracts
        $this->assertNotEmpty($result['contracts']);
        $this->assertCount(6, $result['contracts']);
        $this->assertEquals($contracts->push($contract)->reverse()->pluck('id'), collect($result['contracts'])->pluck('id'));
    }

    /** @test */
    public function filter_scope_can_filter_contracts_by_status(): void
    {
        // Given contracts
        $contracts = factory(Contract::class, 5)->create(['status' => 1]);
        // And special one
        $contract = factory(Contract::class)->create(['status' => 6]);
        // Given user
        $user = factory(User::class)->create(['group_id' => self::USERS_THAT_CAN_WORK_WITH_CONTRACTS[array_rand(self::USERS_THAT_CAN_WORK_WITH_CONTRACTS)]]);

        // When we use filter scope
        $result = $this->actingAs($user)->post(route('contracts::filtered', ['url' => route('contracts::index') . "?contracts.status={$contract->status}"]))->json();

        // Then results should have one contract
        $this->assertNotEmpty($result['contracts']);
        $this->assertCount(1, $result['contracts']);
        $this->assertEquals($contract->id, $result['contracts'][0]['id']);
    }

    /** @test */
    public function filter_scope_can_filter_contracts_by_statuses_array(): void
    {
        // Given contracts
        $contracts = factory(Contract::class, 5)->create(['status' => 1]);
        // And special one
        $contract = factory(Contract::class)->create(['status' => 5]);
        // Given user
        $user = factory(User::class)->create(['group_id' => self::USERS_THAT_CAN_WORK_WITH_CONTRACTS[array_rand(self::USERS_THAT_CAN_WORK_WITH_CONTRACTS)]]);

        // When we use filter scope
        $result = $this->actingAs($user)->post(route('contracts::filtered', [
            'url' => route('contracts::index') . "?contracts.status%5B0%5D={$contracts[0]->status}&contracts.status%5B1%5D={$contract->status}"
        ]))->json();

        // Then results should have six contracts
        $this->assertNotEmpty($result['contracts']);
        $this->assertCount(6, $result['contracts']);
        $this->assertEquals($contracts->push($contract)->reverse()->pluck('id'), collect($result['contracts'])->pluck('id'));
    }

    /** @test */
    public function filter_scope_can_filter_contracts_by_project_entity(): void
    {
        /** SINGLE USE FILTER */
        // Given project
        $anotherProject = factory(Project::class)->create(['entity' => 2]);
        // Given contracts
        $contracts = factory(Contract::class, 5)->create(['project_id' => $anotherProject->id]);
        // Given project
        $project = factory(Project::class)->create(['entity' => 1]);
        // And special one
        $contract = factory(Contract::class)->create(['project_id' => $project->id]);
        // Given user
        $user = factory(User::class)->create(['group_id' => self::USERS_THAT_CAN_WORK_WITH_CONTRACTS[array_rand(self::USERS_THAT_CAN_WORK_WITH_CONTRACTS)]]);

        // When we use filter scope
        $result = $this->actingAs($user)->post(route('contracts::filtered', ['url' => route('contracts::index') . "?projects.entity={$project->entity}"]))->json();

        // Then results should have one contract
        $this->assertNotEmpty($result['contracts']);
        $this->assertCount(1, $result['contracts']);
        $this->assertEquals($contract->id, $result['contracts'][0]['id']);
    }

    /** @test */
    public function filter_scope_can_filter_contracts_by_contracts_created_at_date(): void
    {
        /** SINGLE USE FILTER */
        // Given contracts
        $contracts = factory(Contract::class, 5)->create(['created_at' => now()->subYear()]);
        // And special one
        $contract = factory(Contract::class)->create(['created_at' => now()->subWeek()]);
        // Given user
        $user = factory(User::class)->create(['group_id' => self::USERS_THAT_CAN_WORK_WITH_CONTRACTS[array_rand(self::USERS_THAT_CAN_WORK_WITH_CONTRACTS)]]);

        // When we use filter scope
        $dateRange = now()->subMonth()->format('d.m.Y') . '|' . now()->format('d.m.Y');
        $result = $this->actingAs($user)->post(route('contracts::filtered', ['url' => route('contracts::index') . "?contracts.created_at={$dateRange}"]))->json();

        // Then results should have one contract
        $this->assertNotEmpty($result['contracts']);
        $this->assertCount(1, $result['contracts']);
        $this->assertEquals($contract->id, $result['contracts'][0]['id']);
    }

    /** @test */
    public function filter_scope_can_filter_contracts_by_search_parameter(): void
    {
        // Given contracts
        $contracts = factory(Contract::class, 5)->create(['status' => 1]);
        // And special one
        $contract = factory(Contract::class)->create(['status' => 5]);
        // Given user
        $user = factory(User::class)->create(['group_id' => self::USERS_THAT_CAN_WORK_WITH_CONTRACTS[array_rand(self::USERS_THAT_CAN_WORK_WITH_CONTRACTS)]]);

        // When we use filter scope
        $result = $this->actingAs($user)->post(route('contracts::filtered', ['url' => route('contracts::index') . '?search=гаран']))->json();

        // Then results should have one contract
        $this->assertNotEmpty($result['contracts']);
        $this->assertCount(1, $result['contracts']);
        $this->assertEquals($contract->id, $result['contracts'][0]['id']);
    }

    /** @test */
    public function filter_scope_can_filter_contracts_by_search_parameters(): void
    {
        // Given contracts
        $contracts = factory(Contract::class, 5)->create(['status' => 1, 'name' => 'Договор на аренду техники']);
        // And special one
        $contract = factory(Contract::class)->create(['status' => 5, 'name' => 'Доп. соглашение']);
        // Given user
        $user = factory(User::class)->create(['group_id' => self::USERS_THAT_CAN_WORK_WITH_CONTRACTS[array_rand(self::USERS_THAT_CAN_WORK_WITH_CONTRACTS)]]);

        // When we use filter scope
        $result = $this->actingAs($user)->post(route('contracts::filtered', ['url' => route('contracts::index') . '?search=гаран•соглашение']))->json();

        // Then results should have one contract
        $this->assertNotEmpty($result['contracts']);
        $this->assertCount(1, $result['contracts']);
        $this->assertEquals($contract->id, $result['contracts'][0]['id']);
    }
}
