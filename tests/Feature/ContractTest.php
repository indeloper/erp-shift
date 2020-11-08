<?php

namespace Tests\Feature;

use App\Models\Contract\Contract;
use App\Models\MatAcc\MaterialAccountingOperation;
use App\Models\ProjectObject;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ContractTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function scope_ten_days_before_date_of_KC_can_return_nothing_if_no_contracts_exist()
    {
        // Given no contracts
        Contract::query()->delete();

        // When we use tenDaysBeforeDateOfKC scope
        $result = Contract::tenDaysBeforeDateOfKC()->get();

        // Then result should be empty
        $this->assertEmpty($result);
    }

    /** @test */
    public function scope_ten_days_before_date_of_KC_can_return_nothing_if_no_contracts_with_KC_date_exist()
    {
        // Given contracts without date of KC
        $contracts = factory(Contract::class, 3)->create();

        // When we use tenDaysBeforeDateOfKC scope
        $result = Contract::tenDaysBeforeDateOfKC()->get();

        // Then result should be empty
        $this->assertEmpty($result);
    }

    /** @test */
    public function scope_ten_days_before_date_of_KC_can_return_nothing_if_no_contracts_with_proper_KC_date_exist()
    {
        // Set test date
        $newNow = now()->day(15);
        Carbon::setTestNow($newNow);
        // Given contracts with date of KC
        $contracts = factory(Contract::class, 3)->create(['ks_date' => now()->day(10)->format('d'), 'type' => 1]);

        // When we use tenDaysBeforeDateOfKC scope
        $result = Contract::tenDaysBeforeDateOfKC()->get();

        // Then result should be empty
        $this->assertEmpty($result);
    }

    /** @test */
    public function scope_ten_days_before_date_of_KC_can_return_contracts_with_proper_KC_date()
    {
        // Set test date
        $newNow = now()->day(15);
        Carbon::setTestNow($newNow);
        // Given contracts with date of KC
        $contracts = factory(Contract::class, 3)->create(['ks_date' => now()->addDays(10)->format('d'), 'type' => 1]);

        // When we use tenDaysBeforeDateOfKC scope
        $result = Contract::tenDaysBeforeDateOfKC()->get();

        // Then result shouldn't be empty
        $this->assertNotEmpty($result);
        $this->assertEquals($contracts->pluck('id'), $result->pluck('id'));
    }

    /** @test */
    public function contract_can_have_operations_relation()
    {
        // Given some project
        $project = factory(ProjectObject::class)->create();
        // Given contracts for project with ks date
        $contract = factory(Contract::class)->create(['project_id' => $project->id, 'type' => 1, 'ks_date' => now()->addDays(10)->format('d')]);
        // Given operation
        $operation = factory(MaterialAccountingOperation::class)->create(['contract_id' => $contract->id]);

        // Then contract should have operations relation
        $operations = $contract->refresh()->operations;
        // With count 1
        $this->assertCount(1, $operations);
        // To operation
        $this->assertEquals($operation->id, $operations->first()->id);
    }
}
