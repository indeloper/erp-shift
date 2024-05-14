<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Notification;
use App\Models\ProjectObject;
use App\Models\TechAcc\Defects\Defects;
use App\Models\TechAcc\OurTechnicTicket;
use App\Models\User;
use App\Traits\NotificationGenerator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class NotificationGeneratorTest extends TestCase
{
    use DatabaseTransactions;

    protected $trait_instance;

    protected $DEFECT;

    protected $TECHNIC;

    protected $CEO;

    protected $SUB_CEO;

    protected $TECHNIC_RESPONSIBLE_RP;

    protected $PRINCIPAL_MECHANIC;

    public function setUp(): void
    {
        parent::setUp();

        $this->trait_instance = new class
        {
            use NotificationGenerator;
        };
        $this->TECHNIC_RESPONSIBLE_RP = Group::find(23)->getUsers()->first() ?? factory(User::class)->create(['group_id' => 23]);
        $this->CEO = Group::find(5)->getUsers()->first() ?? factory(User::class)->create(['group_id' => 5]);
        $this->SUB_CEO = Group::find(6)->getUsers()->first() ?? factory(User::class)->create(['group_id' => 6]);
        $this->PRINCIPAL_MECHANIC = Group::find(47)->getUsers()->first() ?? factory(User::class)->create(['group_id' => 47]);
        $this->DEFECT = factory(Defects::class)->create();
        $this->TECHNIC = $this->DEFECT->defectable;
        $ticket = factory(OurTechnicTicket::class)->create(['our_technic_id' => $this->TECHNIC->id]);
        $ticket->users()->attach($this->TECHNIC_RESPONSIBLE_RP->id, ['type' => 1]);
    }

    public function deleteOurTechnicsTickets(): void
    {
        OurTechnicTicket::query()->delete();
    }

    /** @test */
    public function defect_create_notification_test()
    {
        // Given defect
        $defect = $this->DEFECT;

        // When we call generateDefectCreateNotification() method
        $this->trait_instance->generateDefectCreateNotification($defect);

        // Then ...
        // Defect must have 4 notifications
        $notifications = $defect->notifications;
        $this->assertCount(4, $notifications);
        // For CEO, SUB_CEO, PRINCIPAL_MECHANIC, TECHNIC_RESPONSIBLE_RP
        $this->assertEquals(
            [$this->CEO->id, $this->SUB_CEO->id, $this->PRINCIPAL_MECHANIC->id, $this->TECHNIC_RESPONSIBLE_RP->id],
            $notifications->pluck('user_id')->toArray()
        );
        // With text like this (for example we take CEO)
        $notification = $notifications->where('user_id', $this->CEO->id)->first();
        $this->assertEquals(
            "Новая заявка о неисправности №{$defect->id}, Автор заявки: {$defect->author->full_name}",
            $notification->name
        );
        // With proper type
        $this->assertEquals(65, $notification->type);
    }

    /** @test */
    public function defect_create_notification_second_test()
    {
        // Given defect without tickets
        $defect = $this->DEFECT;
        OurTechnicTicket::query()->delete();

        // When we call generateDefectCreateNotification() method
        $this->trait_instance->generateDefectCreateNotification($defect);

        // Then ...
        // Defect must have 3 notifications
        $notifications = $defect->notifications;
        $this->assertCount(3, $notifications);
        // For CEO, SUB_CEO, PRINCIPAL_MECHANIC
        $this->assertEquals(
            [$this->CEO->id, $this->SUB_CEO->id, $this->PRINCIPAL_MECHANIC->id],
            $notifications->pluck('user_id')->toArray()
        );
        // With text like this (for example we take CEO)
        $notification = $notifications->where('user_id', $this->CEO->id)->first();
        $this->assertEquals(
            "Новая заявка о неисправности №{$defect->id}, Автор заявки: {$defect->author->full_name}",
            $notification->name
        );
        // With proper type
        $this->assertEquals(65, $notification->type);
    }

    /** @test */
    public function defect_responsible_user_assignment_notification_test()
    {
        // Given defect
        $defect = $this->DEFECT;
        // Observer automatically create task and notification for task
        // by calling generateDefectResponsibleAssignmentNotification() inside hook
        // Given defect responsible user create task
        $task = $defect->tasks()->first();

        // Then ...
        // Task must have 1 notification
        $notification = $task->refresh()->notifications->first();
        $this->assertTrue(is_object($notification));
        // For defect author
        $this->assertEquals($this->PRINCIPAL_MECHANIC->id, $notification->user_id);
        // With text like this
        $this->assertEquals("Новая задача «{$task->name}».", $notification->name);
        // With proper type
        $this->assertEquals(66, $notification->type);
    }

    /** @test */
    public function defect_decline_notification_test_without_resp_user()
    {
        // Given defect
        $defect = $this->DEFECT->refresh();

        // When we call generateDefectDeclineNotification() method
        $this->trait_instance->generateDefectDeclineNotification($defect);

        // Then ...
        // Defect must have more than 4 notifications
        $notifications = $defect->notifications;
        $this->assertTrue($notifications->count() > 4);
        // For CEO, SUB_CEO, PRINCIPAL_MECHANIC, DEFECT_AUTHOR, TECHNIC_RESPONSIBLE_RP
        $this->assertEquals(
            array_unique([$this->CEO->id, $this->SUB_CEO->id, $this->PRINCIPAL_MECHANIC->id, $this->DEFECT->user_id, $this->TECHNIC_RESPONSIBLE_RP->id]),
            $notifications->pluck('user_id')->toArray()
        );
        // With text like this (for example we take CEO)
        $notification = $notifications->where('user_id', $this->CEO->id)->first();
        $this->assertEquals(
            "По заявке о неисправности №{$defect->id}, Автор заявки: {$defect->author->full_name}, неисправность не выявлена, заявка отклонена",
            $notification->name
        );
        // With proper type
        $this->assertEquals(73, $notification->type);
    }

    /** @test */
    public function defect_decline_notification_second_test_without_resp_user()
    {
        // Given defect without tickets
        $defect = $this->DEFECT;
        $this->deleteOurTechnicsTickets();

        // When we call generateDefectDeclineNotification() method
        $this->trait_instance->generateDefectDeclineNotification($defect);

        // Then ...
        // Defect must have more than 3 notifications
        $notifications = $defect->notifications;
        $this->assertTrue($notifications->count() >= 3);
        // For CEO, SUB_CEO, PRINCIPAL_MECHANIC, DEFECT_AUTHOR
        $this->assertEquals(
            array_unique([$this->CEO->id, $this->SUB_CEO->id, $this->PRINCIPAL_MECHANIC->id, $this->DEFECT->user_id]),
            $notifications->pluck('user_id')->toArray()
        );
        // With text like this (for example we take CEO)
        $notification = $notifications->where('user_id', $this->CEO->id)->first();
        $this->assertEquals(
            "По заявке о неисправности №{$defect->id}, Автор заявки: {$defect->author->full_name}, неисправность не выявлена, заявка отклонена",
            $notification->name
        );
        // With proper type
        $this->assertEquals(73, $notification->type);
    }

    /** @test */
    public function defect_decline_notification_test_with_resp_user()
    {
        // Given responsible user
        $responsible_user = User::inRandomOrder()->first() ?? factory(User::class)->create();
        // Given defect with responsible user
        $this->DEFECT->update(['responsible_user_id' => $responsible_user->id]);
        $defect = $this->DEFECT->refresh();

        // When we call generateDefectDeclineNotification() method
        $this->trait_instance->generateDefectDeclineNotification($defect);

        // Then ...
        // Defect must have more than 4 notifications
        $notifications = $defect->notifications;
        $this->assertTrue($notifications->count() >= 4);
        // For CEO, SUB_CEO, PRINCIPAL_MECHANIC, DEFECT_AUTHOR, TECHNIC_RESPONSIBLE_RP
        $this->assertEquals(
            [$this->CEO->id, $this->SUB_CEO->id, $this->PRINCIPAL_MECHANIC->id, $this->DEFECT->user_id, $this->TECHNIC_RESPONSIBLE_RP->id],
            $notifications->pluck('user_id')->toArray()
        );
        // With text like this (for example we take CEO)
        $notification = $notifications->where('user_id', $this->CEO->id)->first();
        $this->assertEquals(
            "По заявке о неисправности №{$defect->id}, Автор заявки: {$defect->author->full_name}, Исполнитель: {$defect->responsible_user->full_name}, неисправность не выявлена, заявка отклонена",
            $notification->name
        );
        // With proper type
        $this->assertEquals(73, $notification->type);
    }

    /** @test */
    public function defect_accept_notification_test()
    {
        // Given defect with responsible user
        $this->DEFECT->update([
            'responsible_user_id' => $this->DEFECT->user_id,
            'comment' => $this->faker->paragraph,
            'repair_start_date' => Carbon::createFromFormat('d.m.Y', '10.12.2019'),
            'repair_end_date' => Carbon::createFromFormat('d.m.Y', '11.12.2019'),
            'status' => Defects::IN_WORK,
        ]);
        $defect = $this->DEFECT->refresh();

        // When we call generateDefectAcceptNotification() method
        $this->trait_instance->generateDefectAcceptNotification($defect);

        // Then ...
        // Defect must have more than 4 notifications
        $notifications = $defect->notifications;
        $this->assertTrue($notifications->count() >= 4);
        // For CEO, SUB_CEO, PRINCIPAL_MECHANIC, DEFECT_AUTHOR, TECHNIC_RESPONSIBLE_RP
        $this->assertEquals(
            [$this->CEO->id, $this->SUB_CEO->id, $this->PRINCIPAL_MECHANIC->id, $this->DEFECT->user_id, $this->TECHNIC_RESPONSIBLE_RP->id],
            $notifications->pluck('user_id')->toArray()
        );
        // With text like this (for example we take CEO)
        $notification = $notifications->where('user_id', $this->CEO->id)->first();
        $this->assertEquals(
            "По заявке о неисправности №{$defect->id} был установлен период ремонта с {$defect->repair_start} по {$defect->repair_end}, Автор заявки: {$defect->author->full_name}, Исполнитель: {$defect->responsible_user->full_name}",
            $notification->name
        );
        // With proper type
        $this->assertEquals(74, $notification->type);
    }

    /** @test */
    public function defect_accept_notification_second_test()
    {
        // Given defect with responsible user, but without tickets
        $this->deleteOurTechnicsTickets();
        $this->DEFECT->update([
            'responsible_user_id' => $this->DEFECT->user_id,
            'comment' => $this->faker->paragraph,
            'repair_start_date' => Carbon::createFromFormat('d.m.Y', '10.12.2019'),
            'repair_end_date' => Carbon::createFromFormat('d.m.Y', '11.12.2019'),
            'status' => Defects::IN_WORK,
        ]);
        $defect = $this->DEFECT->refresh();

        // When we call generateDefectAcceptNotification() method
        $this->trait_instance->generateDefectAcceptNotification($defect);

        // Then ...
        // Defect must have more than 3 notifications
        $notifications = $defect->notifications;
        $this->assertTrue($notifications->count() >= 3);
        // For CEO, SUB_CEO, PRINCIPAL_MECHANIC, DEFECT_AUTHOR
        $this->assertEquals(
            array_unique([$this->CEO->id, $this->SUB_CEO->id, $this->PRINCIPAL_MECHANIC->id, $this->DEFECT->user_id]),
            $notifications->pluck('user_id')->toArray()
        );
        // With text like this (for example we take CEO)
        $notification = $notifications->where('user_id', $this->CEO->id)->first();
        $this->assertEquals(
            "По заявке о неисправности №{$defect->id} был установлен период ремонта с {$defect->repair_start} по {$defect->repair_end}, Автор заявки: {$defect->author->full_name}, Исполнитель: {$defect->responsible_user->full_name}",
            $notification->name
        );
        // With proper type
        $this->assertEquals(74, $notification->type);
    }

    /** @test */
    public function defect_no_principle_mechanic_notification_second_test()
    {
        // When we call generateDefectAcceptNotification() method
        $this->trait_instance->generateNoPrincipleMechanicNotification();

        // Then ...
        // We must have new notification
        $notification = Notification::get()->last();
        $this->assertTrue(is_object($notification));
        // For CEO
        $this->assertEquals($this->CEO->id, $notification->user_id);
        // With text like this
        $this->assertEquals('В системе отсутсвует сотрудник на позиции Главного Механика, без него учёт дефектов техники не будет работать', $notification->name);
    }

    /** @test */
    public function defect_responsible_user_store_notification_test()
    {
        // Given responsible user
        $responsible_user = User::inRandomOrder()->first() ?? factory(User::class)->create();
        // Given defect with responsible user
        $this->DEFECT->update(['responsible_user_id' => $responsible_user->id]);
        $defect = $this->DEFECT->refresh();

        // When we call generateDefectDeclineNotification() method
        $this->trait_instance->generateDefectResponsibleUserStoreNotification($defect);

        // Then ...
        // Defect must have more than 5 notifications
        $notifications = $defect->notifications;
        $this->assertTrue($notifications->count() > 5, 'There are {$defect->notifications->count()} notifications. More than 5 excepted.');
        // For CEO, SUB_CEO, PRINCIPAL_MECHANIC, DEFECT_AUTHOR, DEFECT_RESPONSIBLE_USER, TECHNIC_RESPONSIBLE_RP
        $this->assertEquals(
            [$this->CEO->id, $this->SUB_CEO->id, $this->PRINCIPAL_MECHANIC->id, $this->DEFECT->user_id, $responsible_user->id, $this->TECHNIC_RESPONSIBLE_RP->id],
            $notifications->pluck('user_id')->toArray()
        );
        // With text like this (for example we take CEO)
        $notification = $notifications->where('user_id', $this->CEO->id)->first();
        $this->assertEquals(
            "Назначен исполнитель на заявку о неисправности №{$defect->id}, Автор заявки: {$defect->author->full_name}, Исполнитель: {$defect->responsible_user->full_name}",
            $notification->name
        );
        // With proper type
        $this->assertEquals(67, $notification->type);
    }

    /** @test */
    public function defect_responsible_user_store_notification_second_test()
    {
        // Given responsible user
        $responsible_user = User::inRandomOrder()->first() ?? factory(User::class)->create();
        // Given defect with responsible user, but without tickets
        $this->DEFECT->update(['responsible_user_id' => $responsible_user->id]);
        $defect = $this->DEFECT->refresh();
        $this->deleteOurTechnicsTickets();

        // When we call generateDefectDeclineNotification() method
        $this->trait_instance->generateDefectResponsibleUserStoreNotification($defect);

        // Then ...
        // Defect must have more than 4 notifications
        $notifications = $defect->notifications;
        $this->assertTrue($notifications->count() > 4);
        // For CEO, SUB_CEO, PRINCIPAL_MECHANIC, DEFECT_AUTHOR, DEFECT_RESPONSIBLE_USER
        $this->assertEquals(
            array_unique([$this->CEO->id, $this->SUB_CEO->id, $this->PRINCIPAL_MECHANIC->id, $this->DEFECT->user_id, $responsible_user->id]),
            $notifications->pluck('user_id')->toArray()
        );
        // With text like this (for example we take CEO)
        $notification = $notifications->where('user_id', $this->CEO->id)->first();
        $this->assertEquals(
            "Назначен исполнитель на заявку о неисправности №{$defect->id}, Автор заявки: {$defect->author->full_name}, Исполнитель: {$defect->responsible_user->full_name}",
            $notification->name
        );
        // With proper type
        $this->assertEquals(67, $notification->type);
    }

    /** @test */
    public function defect_control_task_notification()
    {
        // Given user
        $user = $this->PRINCIPAL_MECHANIC;
        // Given defect
        $defect = $this->DEFECT;

        // When we make post request with data
        $response = $this->actingAs($user)->put(route('building::tech_acc::defects.select_responsible', $defect->id), [
            'user_id' => $user->id,
        ]);

        $defect->refresh();

        // Then ...
        // generateDefectControlTaskNotification() will call automatically
        // Given defect control task
        $task = $defect->active_tasks->first();

        // Then ...
        // Task must have 1 notification
        $notification = $task->refresh()->notifications->first();
        $this->assertTrue(is_object($notification));
        // For defect responsible user
        $this->assertEquals($user->id, $notification->user_id);
        // With text like this
        $this->assertEquals("Новая задача «{$task->name}».", $notification->name);
        // With proper type
        $this->assertEquals(75, $notification->type);
    }

    /** @test */
    public function defect_repair_dates_update_notification()
    {
        // Given user
        $responsible_user = $this->PRINCIPAL_MECHANIC;
        // Given new defect with responsible user without notifications
        $this->DEFECT->update(['responsible_user_id' => $responsible_user->id, 'status' => 3]);
        $defect = $this->DEFECT->refresh();

        // When we make put request with data
        $data = [
            'comment' => $this->faker->paragraph,
            'repair_start_date' => now()->format('d.m.Y'),
            'repair_end_date' => now()->addDay()->format('d.m.Y'),
        ];
        $response = $this->actingAs($responsible_user)->put(route('building::tech_acc::defects.update_repair_dates', $defect->id), $data);
        $defect->refresh();

        // Then ...
        // generateDefectRepairDatesUpdateNotification() will call automatically
        // Defect must have more than 3 notifications
        $notifications = $defect->notifications;
        $this->assertTrue($notifications->count() >= 3);
        // For CEO, SUB_CEO, PRINCIPAL_MECHANIC, DEFECT_AUTHOR, TECHIC_RESPONSIBLE_USER
        $this->assertEquals(
            array_unique([$this->CEO->id, $this->SUB_CEO->id, $this->PRINCIPAL_MECHANIC->id, $this->DEFECT->user_id,  $this->TECHNIC_RESPONSIBLE_RP->id]),
            $notifications->pluck('user_id')->toArray()
        );
        // With text like this (for example we take CEO)
        $notification = $notifications->where('user_id', $this->CEO->id)->first();
        $this->assertEquals(
            "По заявке о неисправности №{$defect->id} был изменен период ремонта, новый период: с {$defect->repair_start} по {$defect->repair_end}, Автор заявки: {$defect->author->full_name}, Исполнитель: {$defect->responsible_user->full_name}",
            $notification->name
        );
        // With proper type
        $this->assertEquals(77, $notification->type);
    }

    /** @test */
    public function defect_repair_dates_update_notification_second()
    {
        // Given user
        $responsible_user = $this->PRINCIPAL_MECHANIC;
        $user = User::inRandomOrder()->first() ?? factory(User::class)->create();
        // Given new defect with responsible user without notifications
        $this->DEFECT->update(['responsible_user_id' => $responsible_user->id, 'status' => 3]);
        $defect = $this->DEFECT->refresh();
        $this->deleteOurTechnicsTickets();

        // When we make put request with data
        $data = [
            'comment' => $this->faker->paragraph,
            'repair_start_date' => now()->format('d.m.Y'),
            'repair_end_date' => now()->addDay()->format('d.m.Y'),
        ];
        $response = $this->actingAs($responsible_user)->put(route('building::tech_acc::defects.update_repair_dates', $defect->refresh()->id), $data);
        $defect->refresh();

        // Then ...
        // generateDefectRepairDatesUpdateNotification() will call automatically
        // Defect must have more than 3 notifications
        $notifications = $defect->notifications;
        $this->assertTrue($notifications->count() >= 3);
        // For CEO, SUB_CEO, PRINCIPAL_MECHANIC, DEFECT_AUTHOR
        $this->assertEquals(
            array_unique([$this->CEO->id, $this->SUB_CEO->id, $this->PRINCIPAL_MECHANIC->id, $this->DEFECT->user_id]),
            $notifications->pluck('user_id')->toArray()
        );
        // With text like this (for example we take CEO)
        $notification = $notifications->where('user_id', $this->CEO->id)->first();
        $this->assertEquals(
            "По заявке о неисправности №{$defect->id} был изменен период ремонта, новый период: с {$defect->repair_start} по {$defect->repair_end}, Автор заявки: {$defect->author->full_name}, Исполнитель: {$defect->responsible_user->full_name}",
            $notification->name
        );
        // With proper type
        $this->assertEquals(77, $notification->type);
    }

    /** @test */
    public function defect_expire_notification_second()
    {
        // Given user
        $responsible_user = User::inRandomOrder()->first() ?? factory(User::class)->create();
        // Given new defect with responsible user without notifications
        $this->DEFECT->update(['responsible_user_id' => $responsible_user->id, 'status' => 3]);
        $defect = $this->DEFECT->refresh();
        $this->deleteOurTechnicsTickets();

        // When we call generateDefectExpireNotification() method
        $this->trait_instance->generateDefectExpireNotification($defect);
        $defect->refresh();

        // Then ...
        // Defect must have 2 notifications
        $notifications = $defect->notifications;
        $this->assertCount(2, $notifications);
        // For RESPONSIBLE_USER, PRINCIPAL_MECHANIC
        $this->assertEquals(
            array_unique([$responsible_user->id, $this->PRINCIPAL_MECHANIC->id]),
            $notifications->pluck('user_id')->toArray()
        );
        // With text like this (for example we take PRINCIPAL MECHANIC)
        $notification = $notifications->where('user_id', $this->PRINCIPAL_MECHANIC->id)->first();
        $this->assertEquals(
            "По заявке о неисправности №{$defect->id} в течение 24ч заканчивается период ремонта, Автор заявки: {$defect->author->full_name}, Исполнитель: {$defect->responsible_user->full_name}",
            $notification->name
        );
        // With proper type
        $this->assertEquals(78, $notification->type);
    }

    /** @test */
    public function repair_control_task_notification()
    {
        // Given user
        $user = $this->PRINCIPAL_MECHANIC;
        // Given new defect with responsible user
        $defect = factory(Defects::class)->create(['responsible_user_id' => $user->id, 'status' => 2]);

        // When we make put request with data
        $data = [
            'comment' => $this->faker->paragraph,
            'repair_start_date' => now()->format('d.m.Y'),
            'repair_end_date' => now()->addDay()->format('d.m.Y'),
        ];
        $response = $this->actingAs($user)->put(route('building::tech_acc::defects.accept', $defect->refresh()->id), $data);

        // Then ...
        // Now we must have new task with type 35 and for defect responsible user
        $new_task = $defect->active_tasks->first();
        // Task should have one notification
        $this->assertCount(1, $new_task->notifications);
        // Notification should have type 79 and some text inside
        $notification = $new_task->notifications->first();
        $this->assertEquals(79, $notification->type);
        $this->assertEquals("Новая задача «{$new_task->name}».", $notification->name);
    }

    /** @test */
    public function defect_repair_end_notification()
    {
        // Given user
        $user = $this->PRINCIPAL_MECHANIC;
        // Given new defect with responsible user
        $this->DEFECT->update([
            'responsible_user_id' => $this->DEFECT->user_id,
            'comment' => $this->faker->paragraph,
            'repair_start_date' => Carbon::createFromFormat('d.m.Y', '10.12.2019'),
            'repair_end_date' => Carbon::createFromFormat('d.m.Y', '11.12.2019'),
            'status' => Defects::IN_WORK,
        ]);
        $defect = $this->DEFECT->refresh();

        // When we make put request with data
        $data = [
            'comment' => $this->faker->paragraph,
            'start_location_id' => ProjectObject::inRandomOrder()->first()->id ?? factory(ProjectObject::class)->create()->id,
        ];
        $response = $this->actingAs($user)->put(route('building::tech_acc::defects.end_repair', $defect->refresh()->id), $data);

        // Then ...
        // Method generateDefectRepairEndNotification() calls automatically
        // Defect must have more than 3 notifications
        $notifications = $defect->notifications;
        $this->assertTrue($notifications->count() >= 3);
        // For CEO, SUB_CEO, PRINCIPAL_MECHANIC and DEFECT AUTHOR
        $this->assertEquals(
            array_unique([$this->CEO->id, $this->SUB_CEO->id, $this->PRINCIPAL_MECHANIC->id, $defect->user_id, $this->TECHNIC_RESPONSIBLE_RP->id]),
            $notifications->pluck('user_id')->toArray()
        );
        // With text like this (for example we take CEO)
        $notification = $notifications->where('user_id', $this->CEO->id)->first();
        $this->assertEquals(
            "По заявке о неисправности №{$defect->id} работы окончены, местоположение техники: {$defect->defectable->refresh()->start_location->location}, Исполнитель: {$defect->responsible_user->full_name}",
            $notification->name
        );
        // With proper type
        $this->assertEquals(80, $notification->type);
    }

    /** @test */
    public function defect_destroy_notification()
    {
        // Given user
        $user = $this->PRINCIPAL_MECHANIC;
        // Given new defect with responsible user
        $this->DEFECT->update(['user_id' => $this->PRINCIPAL_MECHANIC->id]);
        $defect = $this->DEFECT->refresh();

        // When we make delete request
        $response = $this->actingAs($user)->delete(route('building::tech_acc::defects.destroy', $defect->refresh()->id));

        // Then ...
        // Method generateDefectDeleteNotification() calls automatically
        // Defect must have more than 3 notifications
        $notifications = $defect->notifications;
        $this->assertTrue($notifications->count() >= 3);
        // For CEO, SUB_CEO, PRINCIPAL_MECHANIC and TECHINC_RESPONSIBLE
        $this->assertEquals(
            array_unique([$this->CEO->id, $this->SUB_CEO->id, $this->PRINCIPAL_MECHANIC->id, $this->TECHNIC_RESPONSIBLE_RP->id]),
            $notifications->pluck('user_id')->toArray()
        );
        // With text like this (for example we take CEO)
        $notification = $notifications->where('user_id', $this->CEO->id)->first();
        $this->assertEquals(
            "Автор заявки {$defect->author->full_name} удалил заявку о неисправности №{$defect->id}.",
            $notification->name
        );
        // With proper type
        $this->assertEquals(81, $notification->type);
    }

    /** @test */
    public function our_technic_ticket_close_notification()
    {
        // Given technic ticket
        $ticket = factory(OurTechnicTicket::class)->create();

        // When we call close() function
        $ticket->close();

        // Then ...
        // Method generateOurTechnicTicketCloseNotifications() calls automatically
        // Ticket must have notifications for all PRINCIPLE MECHANICS and maybe for user with id = 56
        $notifications = $ticket->refresh()->notifications;
        $this->assertEquals(User::whereGroupId(47)->count() + (User::find(User::HARDCODED_PERSONS['router']) ? 1 : 0), $notifications->count());
        // With text like this (for example we take any PRINCIPLE MECHANIC)
        $notification = $notifications->where('user_id', $this->PRINCIPAL_MECHANIC->id)->first();
        $this->assertEquals(
            "Работы с техникой {$ticket->our_technic->category_name} {$ticket->our_technic->name}, инвентарный номер: {$ticket->our_technic->inventory_number} закончились на объекте: {$ticket->our_technic->start_location->location}.",
            $notification->name
        );
        // With proper type
        $this->assertEquals(86, $notification->type);
    }

    /** @test */
    public function our_technic_ticket_use_extension_notifications()
    {
        // Given ticket
        $ourTechnicTicket = factory(OurTechnicTicket::class)->create();

        // When we call this method
        $this->trait_instance->generateOurTechnicTicketUseExtensionNotifications($ourTechnicTicket);

        // Then ...
        // Ticket must have notifications for all PRINCIPLE MECHANICS and maybe for user with id = 56
        $notifications = $ourTechnicTicket->refresh()->notifications;
        $this->assertEquals(User::whereGroupId(47)->count() + (User::find(User::HARDCODED_PERSONS['router']) ? 1 : 0), $notifications->count());
        // With text like this (for example we take any PRINCIPLE MECHANIC)
        $notification = $notifications->where('user_id', $this->PRINCIPAL_MECHANIC->id)->first();
        $this->assertEquals(
            "На объекте: {$ourTechnicTicket->our_technic->start_location->location} изменилась дата окончания использования техники {$ourTechnicTicket->our_technic->category_name} {$ourTechnicTicket->our_technic->name}, инвентарный номер: {$ourTechnicTicket->our_technic->inventory_number}.",
            $notification->name
        );
        // With proper type
        $this->assertEquals(87, $notification->type);
    }

    /** @test */
    public function generate_birthday_today_notification()
    {
        Notification::query()->delete();
        User::query()->delete();
        // Given three users with birthdays
        $user1 = factory(User::class)->create(['birthday' => now()->subYear()->subWeek()->format('d.m.Y')]);
        $user2 = factory(User::class)->create(['birthday' => now()->subYears(2)->subWeek()->format('d.m.Y')]);
        $user3 = factory(User::class)->create(['birthday' => now()->subYears(2)->subMonth()->format('d.m.Y')]);

        // When we call generateBirthdayTodayNotifications()
        $this->trait_instance->generateBirthdayTodayNotifications(collect([$user3]));

        // Then ...
        // Two notifications should be generated
        $notifications = Notification::get();
        $this->assertCount(2, $notifications);
        // This notifications is for $user1 and $user2
        $this->assertEquals([$user1->id, $user2->id], $notifications->pluck('user_id')->toArray());
        // With proper type
        $this->assertEquals(89, $notifications->first()->type);
        // And text
        $this->assertEquals("Сегодня празднует свой день рождения {$user3->full_name}!", $notifications->first()->name);
    }

    /** @test */
    public function generate_birthday_today_notification_do_nothing_if_no_users_passed()
    {
        Notification::query()->delete();
        User::query()->delete();
        // Given three users with birthdays
        $user1 = factory(User::class)->create(['birthday' => now()->subYear()->subWeek()->format('d.m.Y')]);
        $user2 = factory(User::class)->create(['birthday' => now()->subYears(2)->subWeek()->format('d.m.Y')]);
        $user3 = factory(User::class)->create(['birthday' => now()->subYears(2)->subMonth()->format('d.m.Y')]);

        // When we call generateBirthdayTodayNotifications()
        $this->trait_instance->generateBirthdayTodayNotifications(collect([]));

        // Then nothing should happen
        $notifications = Notification::get();
        $this->assertEmpty($notifications);
    }

    /** @test */
    public function generate_birthday_next_week_notification()
    {
        Notification::query()->delete();
        User::query()->delete();
        // Given three users with birthdays
        $user1 = factory(User::class)->create(['birthday' => now()->subYear()->subWeek()->format('d.m.Y')]);
        $user2 = factory(User::class)->create(['birthday' => now()->subYears(2)->subWeek()->format('d.m.Y')]);
        $user3 = factory(User::class)->create(['birthday' => now()->subYears(2)->subMonth()->format('d.m.Y')]);
        $birthdayDate = now()->addDays(7)->format('d.m.Y');

        // When we call generateBirthdayNextWeekNotifications()
        $this->trait_instance->generateBirthdayNextWeekNotifications(collect([$user3]));

        // Then ...
        // Two notifications should be generated
        $notifications = Notification::get();
        $this->assertCount(2, $notifications);
        // This notifications is for $user1 and $user2
        $this->assertEquals([$user1->id, $user2->id], $notifications->pluck('user_id')->toArray());
        // With proper type
        $this->assertEquals(88, $notifications->first()->type);
        // And text
        $this->assertEquals("{$birthdayDate} празднует свой день рождения {$user3->full_name}!", $notifications->first()->name);
    }

    /** @test */
    public function generate_birthday_next_week_notification_do_nothing_if_no_users_passed()
    {
        Notification::query()->delete();
        User::query()->delete();
        // Given three users with birthdays
        $user1 = factory(User::class)->create(['birthday' => now()->subYear()->subWeek()->format('d.m.Y')]);
        $user2 = factory(User::class)->create(['birthday' => now()->subYears(2)->subWeek()->format('d.m.Y')]);
        $user3 = factory(User::class)->create(['birthday' => now()->subYears(2)->subMonth()->format('d.m.Y')]);

        // When we call generateBirthdayNextWeekNotifications()
        $this->trait_instance->generateBirthdayNextWeekNotifications(collect([]));

        // Then nothing should happen
        $notifications = Notification::get();
        $this->assertEmpty($notifications);
    }
}
