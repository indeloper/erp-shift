<?php

namespace Tests\Feature;

use App\Models\Contract\Contract;
use App\Models\MatAcc\MaterialAccountingOperation;
use App\Models\MatAcc\MaterialAccountingOperationMaterials;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class CertificatelessOperationsNotifyCommandTest extends TestCase
{
    /** @test */
    public function when_we_call_command_with_contracts_without_operations_nothing_should_happen(): void
    {
        // Given contract without operations
        $contract = Contract::factory()->create();
        // Given notifications count
        $notificationsCount = Notification::count();

        // When we call command
        $this->artisan('certificatless-operations:notify')->run();

        // Nothing should happen
        $this->assertEquals($notificationsCount, Notification::count());
    }

    /** @test */
    public function when_we_call_command_with_contracts_with_operations_without_certificates_but_contract_dont_have_ks_date_nothing_should_happen(): void
    {
        // Given contract without ks_date
        $contract = Contract::factory()->create();
        // Given arrival operation for contract
        $operation = MaterialAccountingOperation::factory()->create(['contract_id' => $contract->id, 'type' => 1]);
        // Given operation arrival part material without certificate
        MaterialAccountingOperationMaterials::factory()->create(['operation_id' => $operation->id, 'type' => 9]);
        // Given notifications count
        $notificationsCount = Notification::count();

        // When we call command
        $this->artisan('certificatless-operations:notify')->run();

        // Nothing should happen
        $this->assertEquals($notificationsCount, Notification::count());
    }

    /** @test */
    public function when_we_call_command_with_contracts_with_operation_without_certificates_and_contract_doesnt_have_start_notifying_before_property_something_should_happen(): void
    {
        /**
         * If contract doesn't have start_notifying_before property, we should use 10 days range
         */
        // Set date 10/03/2020
        $newNow = Carbon::setTestNow(now()->year(2020)->month(3)->day(10));
        // Given contract with ks_date
        $contract = Contract::factory()->create(['ks_date' => '20']);
        // Given arrival operation for contract
        $operation = MaterialAccountingOperation::factory()->create(['contract_id' => $contract->id, 'type' => 1]);
        // Given operation arrival part material without certificate
        MaterialAccountingOperationMaterials::factory()->create(['operation_id' => $operation->id, 'type' => 9]);
        // Given notifications count
        $notificationsCount = Notification::count();

        // When we call command
        $this->artisan('certificatless-operations:notify')->run();

        // Something should happen
        $this->assertNotEquals($notificationsCount, Notification::count());
        // Some users should have new notifications
        // "certificate worker" should have one notification with type 106
        $certificateWorker = User::find(User::HARDCODED_PERSONS['certificateWorker']);
        $this->assertCount(1, $certificateWorker->notifications);
        $this->assertEquals(106, $certificateWorker->notifications->first()->type);
        $this->assertEquals("В системе существуют операции без сертификатов, связанные с договором {$contract->name_for_humans}.", $certificateWorker->notifications->first()->name);
        // subCEO should have one notification with type 106
        $subCEO = User::find(User::HARDCODED_PERSONS['subCEO']);
        $this->assertCount(1, $subCEO->notifications);
        $this->assertEquals(106, $subCEO->notifications->first()->type);
        $this->assertEquals("В системе существуют операции без сертификатов, связанные с договором {$contract->name_for_humans}.", $subCEO->notifications->first()->name);
        // mainPTO should have one notification with type 106
        $mainPTO = User::find(User::HARDCODED_PERSONS['mainPTO']);
        $this->assertCount(1, $mainPTO->notifications);
        $this->assertEquals(106, $mainPTO->notifications->first()->type);
        $this->assertEquals("В системе существуют операции без сертификатов, связанные с договором {$contract->name_for_humans}.", $mainPTO->notifications->first()->name);
    }

    /** @test */
    public function when_we_call_command_with_contracts_with_operations_without_certificates_and_contract_doesnt_have_start_notifying_before_property_something_should_happen(): void
    {
        /**
         * If contract doesn't have start_notifying_before property, we should use 10 days range
         */
        // Set date 10/03/2020
        $newNow = Carbon::setTestNow(now()->year(2020)->month(3)->day(10));
        // Given contract with ks_date
        $contract = Contract::factory()->create(['ks_date' => '20']);
        // Given second contract with ks_date
        $secondContract = Contract::factory()->create(['ks_date' => '20']);
        // Given arrival operation for contract
        $operation = MaterialAccountingOperation::factory()->create(['contract_id' => $contract->id, 'type' => 1]);
        // Given moving operation for second contract
        $movingOperation = MaterialAccountingOperation::factory()->create(['contract_id' => $secondContract->id, 'type' => 4]);
        // Given operation arrival part material without certificate
        MaterialAccountingOperationMaterials::factory()->create(['operation_id' => $operation->id, 'type' => 9]);
        // Given operation moving part material without certificate
        MaterialAccountingOperationMaterials::factory()->create(['operation_id' => $movingOperation->id, 'type' => 9]);
        // Given notifications count
        $notificationsCount = Notification::count();

        // When we call command
        $this->artisan('certificatless-operations:notify')->run();

        // Something should happen
        $this->assertNotEquals($notificationsCount, Notification::count());
        // Some users should have new notifications
        // "certificate worker" should have two notification with type 106
        $certificateWorker = User::find(User::HARDCODED_PERSONS['certificateWorker']);
        $this->assertCount(2, $certificateWorker->notifications);
        $this->assertEquals(106, $certificateWorker->notifications->first()->type);
        $this->assertEquals("В системе существуют операции без сертификатов, связанные с договором {$contract->name_for_humans}.", $certificateWorker->notifications->first()->name);
        // subCEO should have one notification with type 106
        $subCEO = User::find(User::HARDCODED_PERSONS['subCEO']);
        $this->assertCount(2, $subCEO->notifications);
        $this->assertEquals(106, $subCEO->notifications->first()->type);
        $this->assertEquals("В системе существуют операции без сертификатов, связанные с договором {$contract->name_for_humans}.", $subCEO->notifications->first()->name);
        // mainPTO should have one notification with type 106
        $mainPTO = User::find(User::HARDCODED_PERSONS['mainPTO']);
        $this->assertCount(2, $mainPTO->notifications);
        $this->assertEquals(106, $mainPTO->notifications->first()->type);
        $this->assertEquals("В системе существуют операции без сертификатов, связанные с договором {$contract->name_for_humans}.", $mainPTO->notifications->first()->name);
    }

    /** @test */
    public function when_we_call_command_with_contracts_with_operations_without_certificates_and_contract_doesnt_have_start_notifying_before_property_in_the_middle_of_the_range_something_should_happen(): void
    {
        /**
         * If contract doesn't have start_notifying_before property, we should use 10 days range
         */
        // Set date 10/03/2020
        $newNow = Carbon::setTestNow(now()->year(2020)->month(3)->day(15));
        // Given contract with ks_date
        $contract = Contract::factory()->create(['ks_date' => '20']);
        // Given second contract with ks_date
        $secondContract = Contract::factory()->create(['ks_date' => '20']);
        // Given arrival operation for contract
        $operation = MaterialAccountingOperation::factory()->create(['contract_id' => $contract->id, 'type' => 1]);
        // Given moving operation for second contract
        $movingOperation = MaterialAccountingOperation::factory()->create(['contract_id' => $secondContract->id, 'type' => 4]);
        // Given operation arrival part material without certificate
        MaterialAccountingOperationMaterials::factory()->create(['operation_id' => $operation->id, 'type' => 9]);
        // Given operation moving part material without certificate
        MaterialAccountingOperationMaterials::factory()->create(['operation_id' => $movingOperation->id, 'type' => 9]);
        // Given notifications count
        $notificationsCount = Notification::count();

        // When we call command
        $this->artisan('certificatless-operations:notify')->run();

        // Something should happen
        $this->assertNotEquals($notificationsCount, Notification::count());
        // Some users should have new notifications
        // "certificate worker" should have two notification with type 106
        $certificateWorker = User::find(User::HARDCODED_PERSONS['certificateWorker']);
        $this->assertCount(2, $certificateWorker->notifications);
        $this->assertEquals(106, $certificateWorker->notifications->first()->type);
        $this->assertEquals("В системе существуют операции без сертификатов, связанные с договором {$contract->name_for_humans}.", $certificateWorker->notifications->first()->name);
        // subCEO should have one notification with type 106
        $subCEO = User::find(User::HARDCODED_PERSONS['subCEO']);
        $this->assertCount(2, $subCEO->notifications);
        $this->assertEquals(106, $subCEO->notifications->first()->type);
        $this->assertEquals("В системе существуют операции без сертификатов, связанные с договором {$contract->name_for_humans}.", $subCEO->notifications->first()->name);
        // mainPTO should have one notification with type 106
        $mainPTO = User::find(User::HARDCODED_PERSONS['mainPTO']);
        $this->assertCount(2, $mainPTO->notifications);
        $this->assertEquals(106, $mainPTO->notifications->first()->type);
        $this->assertEquals("В системе существуют операции без сертификатов, связанные с договором {$contract->name_for_humans}.", $mainPTO->notifications->first()->name);
    }

    /** @test */
    public function when_we_call_command_with_contracts_with_operations_without_certificates_and_contract_doesnt_have_start_notifying_before_property_in_the_end_of_the_range_something_should_happen(): void
    {
        /**
         * If contract doesn't have start_notifying_before property, we should use 10 days range
         */
        // Set date 10/03/2020
        $newNow = Carbon::setTestNow(now()->year(2020)->month(3)->day(19));
        // Given contract with ks_date
        $contract = Contract::factory()->create(['ks_date' => '20']);
        // Given second contract with ks_date
        $secondContract = Contract::factory()->create(['ks_date' => '20']);
        // Given arrival operation for contract
        $operation = MaterialAccountingOperation::factory()->create(['contract_id' => $contract->id, 'type' => 1]);
        // Given moving operation for second contract
        $movingOperation = MaterialAccountingOperation::factory()->create(['contract_id' => $secondContract->id, 'type' => 4]);
        // Given operation arrival part material without certificate
        MaterialAccountingOperationMaterials::factory()->create(['operation_id' => $operation->id, 'type' => 9]);
        // Given operation moving part material without certificate
        MaterialAccountingOperationMaterials::factory()->create(['operation_id' => $movingOperation->id, 'type' => 9]);
        // Given notifications count
        $notificationsCount = Notification::count();

        // When we call command
        $this->artisan('certificatless-operations:notify')->run();

        // Something should happen
        $this->assertNotEquals($notificationsCount, Notification::count());
        // Some users should have new notifications
        // "certificate worker" should have two notification with type 106
        $certificateWorker = User::find(User::HARDCODED_PERSONS['certificateWorker']);
        $this->assertCount(2, $certificateWorker->notifications);
        $this->assertEquals(106, $certificateWorker->notifications->first()->type);
        $this->assertEquals("В системе существуют операции без сертификатов, связанные с договором {$contract->name_for_humans}.", $certificateWorker->notifications->first()->name);
        // subCEO should have one notification with type 106
        $subCEO = User::find(User::HARDCODED_PERSONS['subCEO']);
        $this->assertCount(2, $subCEO->notifications);
        $this->assertEquals(106, $subCEO->notifications->first()->type);
        $this->assertEquals("В системе существуют операции без сертификатов, связанные с договором {$contract->name_for_humans}.", $subCEO->notifications->first()->name);
        // mainPTO should have one notification with type 106
        $mainPTO = User::find(User::HARDCODED_PERSONS['mainPTO']);
        $this->assertCount(2, $mainPTO->notifications);
        $this->assertEquals(106, $mainPTO->notifications->first()->type);
        $this->assertEquals("В системе существуют операции без сертификатов, связанные с договором {$contract->name_for_humans}.", $mainPTO->notifications->first()->name);
    }

    /** @test */
    public function when_we_call_command_with_contracts_with_operations_without_certificates_and_contract_have_start_notifying_before_property_something_should_happen(): void
    {
        // Set date 10/03/2020
        $newNow = Carbon::setTestNow(now()->year(2020)->month(3)->day(15));
        // Given contract with ks_date
        $contract = Contract::factory()->create(['ks_date' => '20', 'start_notifying_before' => 5]);
        // Given second contract with ks_date
        $secondContract = Contract::factory()->create(['ks_date' => '20', 'start_notifying_before' => 5]);
        // Given arrival operation for contract
        $operation = MaterialAccountingOperation::factory()->create(['contract_id' => $contract->id, 'type' => 1]);
        // Given moving operation for second contract
        $movingOperation = MaterialAccountingOperation::factory()->create(['contract_id' => $secondContract->id, 'type' => 4]);
        // Given operation arrival part material without certificate
        MaterialAccountingOperationMaterials::factory()->create(['operation_id' => $operation->id, 'type' => 9]);
        // Given operation moving part material without certificate
        MaterialAccountingOperationMaterials::factory()->create(['operation_id' => $movingOperation->id, 'type' => 9]);
        // Given notifications count
        $notificationsCount = Notification::count();

        // When we call command
        $this->artisan('certificatless-operations:notify')->run();

        // Something should happen
        $this->assertNotEquals($notificationsCount, Notification::count());
        // Some users should have new notifications
        // "certificate worker" should have two notification with type 106
        $certificateWorker = User::find(User::HARDCODED_PERSONS['certificateWorker']);
        $this->assertCount(2, $certificateWorker->notifications);
        $this->assertEquals(106, $certificateWorker->notifications->first()->type);
        $this->assertEquals("В системе существуют операции без сертификатов, связанные с договором {$contract->name_for_humans}.", $certificateWorker->notifications->first()->name);
        // subCEO should have one notification with type 106
        $subCEO = User::find(User::HARDCODED_PERSONS['subCEO']);
        $this->assertCount(2, $subCEO->notifications);
        $this->assertEquals(106, $subCEO->notifications->first()->type);
        $this->assertEquals("В системе существуют операции без сертификатов, связанные с договором {$contract->name_for_humans}.", $subCEO->notifications->first()->name);
        // mainPTO should have one notification with type 106
        $mainPTO = User::find(User::HARDCODED_PERSONS['mainPTO']);
        $this->assertCount(2, $mainPTO->notifications);
        $this->assertEquals(106, $mainPTO->notifications->first()->type);
        $this->assertEquals("В системе существуют операции без сертификатов, связанные с договором {$contract->name_for_humans}.", $mainPTO->notifications->first()->name);
    }

    /** @test */
    public function when_we_call_command_with_contracts_with_operations_without_certificates_and_contract_have_start_notifying_before_property_and_time_before_this_range_nothing_should_happen(): void
    {
        // Set date 10/03/2020
        $newNow = Carbon::setTestNow(now()->year(2020)->month(3)->day(10));
        // Given contract with ks_date
        $contract = Contract::factory()->create(['ks_date' => '20', 'start_notifying_before' => 5]);
        // Given second contract with ks_date
        $secondContract = Contract::factory()->create(['ks_date' => '20', 'start_notifying_before' => 5]);
        // Given arrival operation for contract
        $operation = MaterialAccountingOperation::factory()->create(['contract_id' => $contract->id, 'type' => 1]);
        // Given moving operation for second contract
        $movingOperation = MaterialAccountingOperation::factory()->create(['contract_id' => $secondContract->id, 'type' => 4]);
        // Given operation arrival part material without certificate
        MaterialAccountingOperationMaterials::factory()->create(['operation_id' => $operation->id, 'type' => 9]);
        // Given operation moving part material without certificate
        MaterialAccountingOperationMaterials::factory()->create(['operation_id' => $movingOperation->id, 'type' => 9]);
        // Given notifications count
        $notificationsCount = Notification::count();

        // When we call command
        $this->artisan('certificatless-operations:notify')->run();

        // Nothing should happen
        $this->assertEquals($notificationsCount, Notification::count());
    }
}
