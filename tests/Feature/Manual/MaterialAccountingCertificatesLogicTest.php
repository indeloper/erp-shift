<?php

namespace Tests\Feature\Manual;

use App\Models\MatAcc\MaterialAccountingMaterialFile;
use App\Models\MatAcc\MaterialAccountingOperation;
use App\Models\MatAcc\MaterialAccountingOperationMaterials;
use App\Models\Notification;
use App\Models\Task;
use App\Models\User;
use Tests\TestCase;

class MaterialAccountingCertificatesLogicTest extends TestCase
{
    /** @test */
    public function when_somebody_complete_write_off_operation_no_certificates_logic_fires(): void
    {
        // Given material accounting write off operation
        $operation = factory(MaterialAccountingOperation::class)->create(['type' => 2]);
        // Given operation write off part material without certificate
        factory(MaterialAccountingOperationMaterials::class)->create(['operation_id' => $operation->id, 'type' => 8]);
        // Given tasks count
        $tasksCount = Task::count();
        // Given notifications count
        $notificationsCount = Notification::count();

        // When somebody close operation (emit it with update to third status)
        $operation->update(['status' => 3]);

        // Then tasks and notifications count should be the same
        $this->assertEquals($tasksCount, Task::count());
        $this->assertEquals($notificationsCount, Notification::count());
    }

    /** @test */
    public function when_somebody_complete_transformation_operation_no_certificates_logic_fires(): void
    {
        // Given material accounting transformation operation
        $operation = factory(MaterialAccountingOperation::class)->create(['type' => 3]);
        // Given operation transformation part material without certificate
        factory(MaterialAccountingOperationMaterials::class)->create(['operation_id' => $operation->id, 'type' => 8]);
        // Given tasks count
        $tasksCount = Task::count();
        // Given notifications count
        $notificationsCount = Notification::count();

        // When somebody close operation (emit it with update to third status)
        $operation->update(['status' => 3]);

        // Then tasks and notifications count should be the same
        $this->assertEquals($tasksCount, Task::count());
        $this->assertEquals($notificationsCount, Notification::count());
    }

    /** @test */
    public function when_somebody_complete_arrival_operation_with_certificates_no_certificates_logic_fires(): void
    {
        // Given material accounting arrival operation
        $operation = factory(MaterialAccountingOperation::class)->create(['type' => 1]);
        // Given operation arrival part material with certificate
        $partSave = factory(MaterialAccountingOperationMaterials::class)->create(['operation_id' => $operation->id, 'type' => 9]);
        factory(MaterialAccountingMaterialFile::class)->create(['type' => 3, 'operation_id' => $operation->id, 'operation_material_id' => $partSave->id]);
        // Given tasks count
        $tasksCount = Task::count();
        // Given notifications count
        $notificationsCount = Notification::count();

        // When somebody close operation (emit it with update to third status)
        $operation->update(['status' => 3]);

        // Then tasks and notifications count should be the same
        $this->assertEquals($tasksCount, Task::count());
        $this->assertEquals($notificationsCount, Notification::count());
    }

    /** @test */
    public function when_somebody_complete_moving_operation_with_certificates_no_certificates_logic_fires(): void
    {
        // Given material accounting moving operation
        $operation = factory(MaterialAccountingOperation::class)->create(['type' => 4]);
        // Given operation moving part material with certificate
        $partSave = factory(MaterialAccountingOperationMaterials::class)->create(['operation_id' => $operation->id, 'type' => 9]);
        factory(MaterialAccountingMaterialFile::class)->create(['type' => 3, 'operation_id' => $operation->id, 'operation_material_id' => $partSave->id]);
        // Given tasks count
        $tasksCount = Task::count();
        // Given notifications count
        $notificationsCount = Notification::count();

        // When somebody close operation (emit it with update to third status)
        $operation->update(['status' => 3]);

        // Then tasks and notifications count should be the same
        $this->assertEquals($tasksCount, Task::count());
        $this->assertEquals($notificationsCount, Notification::count());
    }

    /** @test */
    public function when_somebody_complete_arrival_operation_without_certificates_certificates_logic_fires(): void
    {
        // Given material accounting arrival operation
        $operation = factory(MaterialAccountingOperation::class)->create(['type' => 1]);
        // Given operation arrival part material without certificate
        factory(MaterialAccountingOperationMaterials::class)->create(['operation_id' => $operation->id, 'type' => 9]);
        // Given tasks count
        $tasksCount = Task::count();
        // Given notifications count
        $notificationsCount = Notification::count();

        // When somebody close operation (emit it with update to third status)
        $operation->update(['status' => 3]);

        // Then tasks and notifications count shouldn't be the same
        $this->assertNotEquals($tasksCount, Task::count());
        $this->assertNotEquals($notificationsCount, Notification::count());
    }

    /** @test */
    public function when_somebody_complete_moving_operation_without_certificates_certificates_logic_fires(): void
    {
        // Given material accounting moving operation
        $operation = factory(MaterialAccountingOperation::class)->create(['type' => 4]);
        // Given operation moving part material without certificate
        factory(MaterialAccountingOperationMaterials::class)->create(['operation_id' => $operation->id, 'type' => 9]);
        // Given tasks count
        $tasksCount = Task::count();
        // Given notifications count
        $notificationsCount = Notification::count();

        // When somebody close operation (emit it with update to third status)
        $operation->update(['status' => 3]);

        // Then tasks and notifications count shouldn't be the same
        $this->assertNotEquals($tasksCount, Task::count());
        $this->assertNotEquals($notificationsCount, Notification::count());
    }

    /** @test */
    public function when_somebody_complete_arrival_operation_without_certificate_in_one_part_save_certificates_logic_fires(): void
    {
        // Given material accounting arrival operation
        $operation = factory(MaterialAccountingOperation::class)->create(['type' => 1]);
        // Given operation arrival part material with certificate
        $partSave = factory(MaterialAccountingOperationMaterials::class)->create(['operation_id' => $operation->id, 'type' => 9]);
        factory(MaterialAccountingMaterialFile::class)->create(['type' => 3, 'operation_id' => $operation->id, 'operation_material_id' => $partSave->id]);
        // Given operation arrival part material without certificate
        factory(MaterialAccountingOperationMaterials::class)->create(['operation_id' => $operation->id, 'type' => 9]);
        // Given tasks count
        $tasksCount = Task::count();
        // Given notifications count
        $notificationsCount = Notification::count();

        // When somebody close operation (emit it with update to third status)
        $operation->update(['status' => 3]);

        // Then tasks and notifications count shouldn't be the same
        $this->assertNotEquals($tasksCount, Task::count());
        $this->assertNotEquals($notificationsCount, Notification::count());
    }

    /** @test */
    public function when_somebody_complete_moving_operation_without_certificate_in_one_part_save_certificates_logic_fires(): void
    {
        // Given material accounting moving operation
        $operation = factory(MaterialAccountingOperation::class)->create(['type' => 4]);
        // Given operation arrival part material with certificate
        $partSave = factory(MaterialAccountingOperationMaterials::class)->create(['operation_id' => $operation->id, 'type' => 9]);
        factory(MaterialAccountingMaterialFile::class)->create(['type' => 3, 'operation_id' => $operation->id, 'operation_material_id' => $partSave->id]);
        // Given operation arrival part material without certificate
        factory(MaterialAccountingOperationMaterials::class)->create(['operation_id' => $operation->id, 'type' => 9]);
        // Given tasks count
        $tasksCount = Task::count();
        // Given notifications count
        $notificationsCount = Notification::count();

        // When somebody close operation (emit it with update to third status)
        $operation->update(['status' => 3]);

        // Then tasks and notifications count shouldn't be the same
        $this->assertNotEquals($tasksCount, Task::count());
        $this->assertNotEquals($notificationsCount, Notification::count());
    }

    /** @test */
    public function when_somebody_complete_arrival_operation_without_certificates_logic_fires_detailed(): void
    {
        // Given material accounting arrival operation
        $operation = factory(MaterialAccountingOperation::class)->create(['type' => 1]);
        // Given operation arrival part material without certificate
        factory(MaterialAccountingOperationMaterials::class)->create(['operation_id' => $operation->id, 'type' => 9]);
        // Given tasks count
        $tasksCount = Task::count();
        // Given notifications count
        $notificationsCount = Notification::count();

        // When somebody close operation (emit it with update to third status)
        $operation->update(['status' => 3]);

        // Then tasks and notifications count shouldn't be the same
        $this->assertNotEquals($tasksCount, Task::count());
        $this->assertNotEquals($notificationsCount, Notification::count());
        // Certificate Worker should have one task with status 43
        $certificateWorker = User::find(User::HARDCODED_PERSONS['certificateWorker']);
        $this->assertCount(1, $certificateWorker->tasks);
        $this->assertEquals(43, $certificateWorker->tasks->first()->status);
        // And notification about it
        $this->assertCount(1, $certificateWorker->notifications);
        $this->assertEquals(104, $certificateWorker->notifications->first()->type);
        $this->assertEquals("Новая задача «{$certificateWorker->tasks->first()->name}».", $certificateWorker->notifications->first()->name);
    }

    /** @test */
    public function when_somebody_complete_moving_operation_without_certificate_in_one_part_save_certificates_logic_fires_detailed(): void
    {
        // Given material accounting moving operation
        $operation = factory(MaterialAccountingOperation::class)->create(['type' => 4]);
        // Given operation arrival part material without certificate
        factory(MaterialAccountingOperationMaterials::class)->create(['operation_id' => $operation->id, 'type' => 9]);
        // Given tasks count
        $tasksCount = Task::count();
        // Given notifications count
        $notificationsCount = Notification::count();

        // When somebody close operation (emit it with update to third status)
        $operation->update(['status' => 3]);

        // Then tasks and notifications count shouldn't be the same
        $this->assertNotEquals($tasksCount, Task::count());
        $this->assertNotEquals($notificationsCount, Notification::count());
        // Certificate Worker should have one task with status 43
        $certificateWorker = User::find(User::HARDCODED_PERSONS['certificateWorker']);
        $this->assertCount(1, $certificateWorker->tasks);
        $this->assertEquals(43, $certificateWorker->tasks->first()->status);
        // And notification about it
        $this->assertCount(1, $certificateWorker->notifications);
        $this->assertEquals(104, $certificateWorker->notifications->first()->type);
        $this->assertEquals("Новая задача «{$certificateWorker->tasks->first()->name}».", $certificateWorker->notifications->first()->name);
    }

    /** @test */
    public function if_certificate_worker_has_task_for_arrival_operation_but_after_he_upload_certificates_for_all_part_saves_task_should_be_solved(): void
    {
        // Given material accounting arrival operation
        $operation = factory(MaterialAccountingOperation::class)->create(['type' => 1]);
        // Given operation arrival part material without certificate
        $partSave = factory(MaterialAccountingOperationMaterials::class)->create(['operation_id' => $operation->id, 'type' => 9]);
        // Given tasks count
        $tasksCount = Task::count();
        // Given notifications count
        $notificationsCount = Notification::count();

        // When somebody close operation (emit it with update to third status)
        $operation->update(['status' => 3]);

        // Then tasks and notifications count shouldn't be the same
        $this->assertNotEquals($tasksCount, Task::count());
        $this->assertNotEquals($notificationsCount, Notification::count());
        // Certificate Worker should have one task with status 43
        $certificateWorker = User::find(User::HARDCODED_PERSONS['certificateWorker']);
        $this->assertCount(1, $certificateWorker->tasks);
        $task = $certificateWorker->tasks->first();
        $this->assertEquals(43, $task->status);
        // And notification about it
        $this->assertCount(1, $certificateWorker->notifications);
        $this->assertEquals(104, $certificateWorker->notifications->first()->type);
        $this->assertEquals("Новая задача «{$task->name}».", $certificateWorker->notifications->first()->name);

        // After certificate worker will upload certificate for this operation
        factory(MaterialAccountingMaterialFile::class)->create(['type' => 3, 'operation_id' => $operation->id, 'operation_material_id' => $partSave->id]);

        // Then task should be solved
        $this->assertEquals(1, $task->refresh()->is_solved);
        // And certificate worker should have notification about task solving
        $this->assertCount(2, $certificateWorker->refresh()->notifications);
        $this->assertEquals(3, $certificateWorker->notifications->last()->type);
        $this->assertEquals("Задача «{$task->name}» закрыта", $certificateWorker->notifications->last()->name);
    }

    /** @test */
    public function if_certificate_worker_has_task_for_moving_operation_but_after_he_upload_certificates_for_all_part_saves_task_should_be_solved(): void
    {
        // Given material accounting moving operation
        $operation = factory(MaterialAccountingOperation::class)->create(['type' => 4]);
        // Given operation arrival part material without certificate
        $partSave = factory(MaterialAccountingOperationMaterials::class)->create(['operation_id' => $operation->id, 'type' => 9]);
        // Given tasks count
        $tasksCount = Task::count();
        // Given notifications count
        $notificationsCount = Notification::count();

        // When somebody close operation (emit it with update to third status)
        $operation->update(['status' => 3]);

        // Then tasks and notifications count shouldn't be the same
        $this->assertNotEquals($tasksCount, Task::count());
        $this->assertNotEquals($notificationsCount, Notification::count());
        // Certificate Worker should have one task with status 43
        $certificateWorker = User::find(User::HARDCODED_PERSONS['certificateWorker']);
        $this->assertCount(1, $certificateWorker->tasks);
        $task = $certificateWorker->tasks->first();
        $this->assertEquals(43, $task->status);
        // And notification about it
        $this->assertCount(1, $certificateWorker->notifications);
        $this->assertEquals(104, $certificateWorker->notifications->first()->type);
        $this->assertEquals("Новая задача «{$task->name}».", $certificateWorker->notifications->first()->name);

        // After certificate worker will upload certificate for this operation
        factory(MaterialAccountingMaterialFile::class)->create(['type' => 3, 'operation_id' => $operation->id, 'operation_material_id' => $partSave->id]);

        // Then task should be solved
        $this->assertEquals(1, $task->refresh()->is_solved);
        // And certificate worker should have notification about task solving
        $this->assertCount(2, $certificateWorker->refresh()->notifications);
        $this->assertEquals(3, $certificateWorker->notifications->last()->type);
        $this->assertEquals("Задача «{$task->name}» закрыта", $certificateWorker->notifications->last()->name);
    }

    /** @test */
    public function if_certificate_worker_has_task_for_arrival_operation_but_after_he_upload_certificate_for_one_part_save_task_should_not_be_solved(): void
    {
        // Given material accounting arrival operation
        $operation = factory(MaterialAccountingOperation::class)->create(['type' => 1]);
        // Given operation arrival part material without certificate
        $partSave = factory(MaterialAccountingOperationMaterials::class)->create(['operation_id' => $operation->id, 'type' => 9]);
        // And another one
        factory(MaterialAccountingOperationMaterials::class)->create(['operation_id' => $operation->id, 'type' => 9]);
        // Given tasks count
        $tasksCount = Task::count();
        // Given notifications count
        $notificationsCount = Notification::count();

        // When somebody close operation (emit it with update to third status)
        $operation->update(['status' => 3]);

        // Then tasks and notifications count shouldn't be the same
        $this->assertNotEquals($tasksCount, Task::count());
        $this->assertNotEquals($notificationsCount, Notification::count());
        // Certificate Worker should have one task with status 43
        $certificateWorker = User::find(User::HARDCODED_PERSONS['certificateWorker']);
        $this->assertCount(1, $certificateWorker->tasks);
        $task = $certificateWorker->tasks->first();
        $this->assertEquals(43, $task->status);
        // And notification about it
        $this->assertCount(1, $certificateWorker->notifications);
        $this->assertEquals(104, $certificateWorker->notifications->first()->type);
        $this->assertEquals("Новая задача «{$task->name}».", $certificateWorker->notifications->first()->name);

        // After certificate worker will upload certificate for this operation
        factory(MaterialAccountingMaterialFile::class)->create(['type' => 3, 'operation_id' => $operation->id, 'operation_material_id' => $partSave->id]);

        // Then task shouldn't be solved
        $this->assertEquals(0, $task->refresh()->is_solved);
        // And certificate worker notifications count should stay the same
        $this->assertCount(1, $certificateWorker->refresh()->notifications);
    }

    /** @test */
    public function if_certificate_worker_has_task_for_moving_operation_but_after_he_upload_certificate_for_one_part_save_task_should_not_be_solved(): void
    {
        // Given material accounting moving operation
        $operation = factory(MaterialAccountingOperation::class)->create(['type' => 4]);
        // Given operation arrival part material without certificate
        $partSave = factory(MaterialAccountingOperationMaterials::class)->create(['operation_id' => $operation->id, 'type' => 9]);
        // And another one
        factory(MaterialAccountingOperationMaterials::class)->create(['operation_id' => $operation->id, 'type' => 9]);
        // Given tasks count
        $tasksCount = Task::count();
        // Given notifications count
        $notificationsCount = Notification::count();

        // When somebody close operation (emit it with update to third status)
        $operation->update(['status' => 3]);

        // Then tasks and notifications count shouldn't be the same
        $this->assertNotEquals($tasksCount, Task::count());
        $this->assertNotEquals($notificationsCount, Notification::count());
        // Certificate Worker should have one task with status 43
        $certificateWorker = User::find(User::HARDCODED_PERSONS['certificateWorker']);
        $this->assertCount(1, $certificateWorker->tasks);
        $task = $certificateWorker->tasks->first();
        $this->assertEquals(43, $task->status);
        // And notification about it
        $this->assertCount(1, $certificateWorker->notifications);
        $this->assertEquals(104, $certificateWorker->notifications->first()->type);
        $this->assertEquals("Новая задача «{$task->name}».", $certificateWorker->notifications->first()->name);

        // After certificate worker will upload certificate for this operation
        factory(MaterialAccountingMaterialFile::class)->create(['type' => 3, 'operation_id' => $operation->id, 'operation_material_id' => $partSave->id]);

        // Then task shouldn't be solved
        $this->assertEquals(0, $task->refresh()->is_solved);
        // And certificate worker notifications count should stay the same
        $this->assertCount(1, $certificateWorker->refresh()->notifications);
    }
}
