<?php

namespace Tests\Feature;

use App\Models\{Comment,
    FileEntry,
    Group,
    Notification,
    ProjectObject,
    Task,
    TechAcc\Defects\Defects,
    TechAcc\FuelTank\FuelTank,
    TechAcc\OurTechnic,
    User};

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
class DefectsTest extends TestCase
{
    use DatabaseTransactions;

    protected $user_that_can;
    protected $user_that_cannot;
    protected $principle;

    const GROUPS_WITH_PERMISSION = [
        3, 14, 19, 23, 27,
        31, 46, 47, 48,
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->user_that_can =
            User::whereIn('group_id', self::GROUPS_WITH_PERMISSION)->where('is_deleted', 0)->first() ??
            factory(User::class)->create(['group_id' => 47]);
        $this->user_that_cannot = User::whereNotIn('group_id', self::GROUPS_WITH_PERMISSION)->where('is_deleted', 0)->first();
        $this->principle = User::whereGroupId(47)->where('is_deleted', 0)->first() ?? factory(User::class)->create(['group_id' => 47]);

        Defects::query()->delete();
    }

    /**
     * Function find or create necessary user
     * @return User
     */
    public function findOrNewUserFromGroupFortySeven()
    {
        return Group::find(47)->getUsers()->first() ?? factory(User::class)->create(['group_id' => 47, 'department_id' => 13]);
    }

    /** @test */
    public function we_can_create_defect()
    {
        // When we create fresh defect
        $defect = factory(Defects::class)->create();

        // Then this defect should be exemplar of Defects class
        $this->assertTrue(get_class($defect) == Defects::class);
    }

    /** @test */
    public function we_can_delete_defect()
    {
        // Given fresh defect
        $defect = factory(Defects::class)->create();

        // When we delete defect
        $defect->delete();
        $defect->fresh();

        // Then defect must be deleted
        $this->assertTrue($defect->trashed());
    }

    /** @test */
    public function defect_must_have_author_relation()
    {
        // Given fresh vehicle
        $defect = factory(Defects::class)->create();

        // Then author relation should be exemplar of User class
        $this->assertTrue(get_class($defect->author) == User::class);
    }

    /** @test */
    public function defect_must_have_defectable_relation()
    {
        // Given fresh vehicle
        $defect = factory(Defects::class)->create();

        // Then defectable relation should be exemplar of OurTechnic class
        $this->assertTrue(get_class($defect->defectable) == OurTechnic::class);
    }

    /** @test */
    public function defect_must_have_one_comment_after_creation()
    {
        // Given fresh vehicle
        $defect = factory(Defects::class)->create();

        // Then comment relation should have length equal to 1
        $this->assertCount(1, $defect->refresh()->comments);
        // And comment author should same as defect author
        $this->assertEquals($defect->author->id, $defect->comments->first()->author->id);
    }

    /** @test */
    public function a_user_from_group_with_permissions_must_have_create_permission()
    {
        // Given user from group with permissions
        $user = $this->user_that_can;

        // Then user must have permission
        $this->assertTrue($user->hasPermission('tech_acc_defects_create'));
    }

    /** @test */
    public function a_user_not_from_group_with_permissions_must_not_have_create_permission()
    {
        // Given user not from group with permissions
        $user = $this->user_that_cannot;

        // Then user must have permission
        $this->assertFalse($user->hasPermission('tech_acc_defects_create'));
    }

    /** @test */
    public function user_without_permission_cant_make_store_post_request()
    {
        // Given cannot user
        $user = $this->user_that_cannot;

        // When we make post request with data
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.store'), []);

        // Then 403 must thrown
        $response->assertForbidden();
    }

    /** @test */
    public function user_with_permission_can_make_store_post_request()
    {
        // pre-create user from group 47
        $this->findOrNewUserFromGroupFortySeven();
        // Given can user
        $user = $this->user_that_can;
        // And notifications count
        $old_notifications_count = Notification::count();

        // When we make post request with data
        $data = [
            'defectable_id' => factory(OurTechnic::class)->create()->id,
            'defectable_type' => 1,
            'description' => $this->faker->paragraph
        ];
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.store'), $data);

        // Then everything should be OK
        $response->assertOk();

        unset($data['defectable_type']);
        // And we must see new example in database
        $createdRow = Defects::get()->last();
        $this->assertEquals($data, [
            'defectable_id' => $createdRow->defectable_id,
            'description' => $createdRow->description,
        ]);
        // And category relation should return same category, that we send
        $this->assertEquals($data['defectable_id'], $createdRow->defectable->id);
        // And user relation should return same user, that we send
        $this->assertEquals($user->id, $createdRow->author->id);

        // Then notifications should be generated
        $new_notifications_count = Notification::count();
        $this->assertTrue($new_notifications_count > $old_notifications_count);

        // And notifications relation count should raise
        $this->assertTrue($createdRow->notifications->count() > 0);

        // And tasks relation should have count equal to one
        // Because every time after defect creation generates defect responsible user assignment task
        $this->assertCount(1, $createdRow->tasks);
    }

    /** @test */
    public function defect_can_have_documents()
    {
        // Given defect
        $defect = factory(Defects::class)->create();

        // Given random file
        $file = FileEntry::create([
            'filename' => $this->faker()->sentence(),
            'size' => $this->faker()->randomNumber(4),
            'mime' => 'random',
            'original_filename' => $this->faker()->words(3, true),
            'user_id' => $this->user_that_can->id,
        ]);

        // When we store file entry
        $defect->documents()->save($file);

        // Then document relation should have count equal to one
        $this->assertCount(1, $defect->documents);
    }

    /** @test */
    public function defect_can_have_photos()
    {
        // Given defect
        $defect = factory(Defects::class)->create();

        // Given photo and dummy file
        $file1 = FileEntry::create([
            'filename' => $this->faker()->sentence(),
            'size' => $this->faker()->randomNumber(4),
            'mime' => 'image/png',
            'original_filename' => $this->faker()->words(3, true),
            'user_id' => $this->user_that_can->id,
        ]);

        $file2 = FileEntry::create([
            'filename' => $this->faker()->sentence(),
            'size' => $this->faker()->randomNumber(4),
            'mime' => 'random',
            'original_filename' => $this->faker()->words(3, true),
            'user_id' => $this->user_that_can->id,
        ]);

        // When we store file entry
        $defect->documents()->save($file1);
        $defect->documents()->save($file2);

        // Then document relation should have count equal to one
        $this->assertCount(1, $defect->photos);
    }

    /** @test */
    public function defect_can_have_videos()
    {
        // Given defect
        $defect = factory(Defects::class)->create();

        // Given video and dummy file
        $file1 = FileEntry::create([
            'filename' => $this->faker()->sentence(),
            'size' => $this->faker()->randomNumber(4),
            'mime' => 'video/mp4',
            'original_filename' => $this->faker()->words(3, true),
            'user_id' => $this->user_that_can->id,
        ]);

        $file2 = FileEntry::create([
            'filename' => $this->faker()->sentence(),
            'size' => $this->faker()->randomNumber(4),
            'mime' => 'random',
            'original_filename' => $this->faker()->words(3, true),
            'user_id' => $this->user_that_can->id,
        ]);

        // When we store file entry
        $defect->documents()->save($file1);
        $defect->documents()->save($file2);

        // Then document relation should have count equal to one
        $this->assertCount(1, $defect->videos);
    }

    /** @test */
    public function form_request_validation_work_with_empty_data()
    {
        // Given can user
        $user = $this->user_that_can;

        // When we make post request without data
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.store'), []);

        // Then in session should be errors
        $response->assertSessionHasErrors('defectable_id');
        $response->assertSessionHasErrors('defectable_type');
        $response->assertSessionHasErrors('description');
    }

    /** @test */
    public function form_request_validation_work_with_too_long_description()
    {
        // Given can user
        $user = $this->user_that_can;

        // When we make post request with data
        $data = [
            'defectable_id' => factory(OurTechnic::class)->create()->id,
            'defectable_type' => 1,
            'description' => $this->faker->paragraph . $this->faker->paragraph . $this->faker->paragraph,
        ];
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.store'), $data);

        // Then in session should be errors
        $response->assertSessionHasErrors('description');
    }

    /** @test */
    public function form_request_validation_work_with_wrong_type()
    {
        // Given can user
        $user = $this->user_that_can;

        // When we make post request with data
        $data = [
            'defectable_id' => factory(OurTechnic::class)->create()->id,
            'defectable_type' => 3,
            'description' => $this->faker->paragraph,
        ];
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.store'), $data);

        // Then in session should be errors
        $response->assertSessionHasErrors('defectable_type');
    }

    /** @test */
    public function defect_must_have_status()
    {
        // Given defect
        $defect = factory(Defects::class)->create();

        // Then defect should have status equal to 1
        $this->assertEquals(1, $defect->status);
    }

    /** @test */
    public function defect_must_have_status_name()
    {
        // Given defect
        $defect = factory(Defects::class)->create();

        // Then defect should have status_name equal to 'Новая заявка"
        $this->assertEquals('Новая заявка', $defect->status_name);
    }

    /** @test */
    public function defect_can_have_responsible_user_relation()
    {
        // Given defect
        $defect = factory(Defects::class)->create(['responsible_user_id' => $this->user_that_can->id]);

        // Then defect responsible_user relation should be exemplar of User class
        $this->assertEquals(User::class, get_class($defect->responsible_user));
    }

    /** @test */
    public function standard_defect_dont_have_responsible_user_relation()
    {
        // Given defect
        $defect = factory(Defects::class)->create();

        // Then defect responsible_user relation should return null
        $this->assertEquals(null, $defect->responsible_user);
    }

    /** @test */
    public function standard_defect_dont_have_repair_dates()
    {
        // Given defect
        $defect = factory(Defects::class)->create();

        // Then defect repair dates property should return null
        $this->assertEquals(null, $defect->repair_start_date);
        $this->assertEquals(null, $defect->repair_end_date);
    }

    /** @test */
    public function defect_repair_dates_must_be_carbon_objects()
    {
        // Given defect
        $defect = factory(Defects::class)->create(['repair_start_date' => now(), 'repair_end_date' => now()->addDays(2)]);

        // Then defect repair dates property should return null
        $this->assertEquals(Carbon::class, get_class($defect->repair_start_date));
        $this->assertEquals(Carbon::class, get_class($defect->repair_end_date));
    }

    /** @test */
    public function defect_notificationable_relation()
    {
        // Given defect
        $defect = factory(Defects::class)->create();
        // Given notification
        $notification = Notification::create(['name' => 'test', 'user_id' => 1]);
        // Add notification in relation
        $defect->notifications()->save($notification);
        // Refresh models
        $notification->refresh();
        $defect->refresh();

        // Then defect notifications relation should return collection with length equal to 1
        $this->assertCount(1, $defect->notifications);
        // And notification notificationable relation should return defect
        $this->assertEquals($notification->notificationable->id, $defect->id);
    }

    /** @test */
    public function defect_taskable_relation()
    {
        // Given task
        $task = factory(Task::class)->create();
        // Given defect
        $defect = factory(Defects::class)->create();

        $old_count = $defect->tasks->count();

        // When we save task to relation
        $defect->tasks()->save($task);

        $new_count = $defect->refresh()->tasks->count();

        // Then defect tasks relation should have raise by 1
        $this->assertTrue($new_count - $old_count == 1);
        // And relation task should be equal to given task
        $this->assertEquals([$task->id, $task->name], [$defect->tasks()->first()->id, $defect->tasks()->first()->name]);
        // And task taskable relation should return defect
        $this->assertEquals($task->taskable->id, $defect->id);
    }

    /** @test */
    public function defect_comments_relation()
    {
        // Given defect
        $defect = factory(Defects::class)->create();
        // Given comment
        $comment = Comment::create([
            'comment' => 'plain comment',
            'author_id' => $this->user_that_can->id,
        ]);

        // When we save comment to relation
        $defect->comments()->save($comment);

        // Then defect comments relation should have length equal to 2
        $this->assertCount(2, $defect->comments);
        // And relation comment should be equal to given comment
        $this->assertEquals([$comment->id, $comment->comment], [$defect->comments->last()->id, $defect->comments->last()->comment]);
        // And task commentable relation should return defect
        $this->assertEquals($comment->commentable->id, $defect->id);
    }

    /** @test */
    public function user_from_group_47_must_have_permission()
    {
        // Given user
        $user = Group::find(47)->getUsers()->first() ?? factory(User::class)->create(['group_id' => 47, 'department_id' => 11]);

        // Then user must have permission
        $this->assertTrue(boolval($user->hasPermission('tech_acc_defects_responsible_user_assignment')));
    }

    /** @test */
    public function user_not_from_group_47_must_not_have_permission()
    {
        // Given user
        $user = User::where('group_id', '!=', 47)->where('id', '!=', 1)->inRandomOrder()->first();

        // Then user must not have permission
        $this->assertFalse(boolval($user->hasPermission('tech_acc_defects_responsible_user_assignment')));
    }

    /** @test */
    public function user_not_from_group_47_cant_make_post_request_for_responsible_user_assignment()
    {
        // pre-create user from group 47
        $this->findOrNewUserFromGroupFortySeven();

        // Given defect
        $defect = factory(Defects::class)->create();
        // Given defect responsible user create task
        $task = $defect->tasks()->first();

        // When we make post request with data
        $user = User::where('group_id', '!=', 47)->where('id', '!=', 1)->inRandomOrder()->first();
        $response = $this->actingAs($user)->put(route('building::tech_acc::defects.select_responsible', $defect->id), []);

        // Then use should have 403
        $response->assertForbidden();
    }

    /** @test */
    public function defect_responsible_user_assignment()
    {
        // pre-create user from group 47
        $user = $this->findOrNewUserFromGroupFortySeven();

        // Given defect
        $defect = factory(Defects::class)->create();
        // Given defect responsible user create task
        $task = $defect->tasks()->first();

        // When we make post request with data
        $response = $this->actingAs($user)->put(route('building::tech_acc::defects.select_responsible', $defect->id), [
            'user_id' => $user->id
        ]);

        $defect->refresh();
        $task->refresh();

        // Then ...
        // Everything should be OK
        $response->assertRedirect($defect->card_route());
        // Defect should have responsible user relation
        $this->assertEquals(User::class, get_class($defect->responsible_user));
        $this->assertEquals($user->id, $defect->responsible_user->id);
        // Defect should move to DIAGNOSTICS status
        $this->assertEquals(Defects::DIAGNOSTICS, $defect->status);
        // Task should be solved
        $this->assertTrue(boolval($task->is_solved));
        // Defect should have comments
        $this->assertTrue($defect->comments->count() > 0);
        // We should have new task
        $this->assertCount(1, $defect->active_tasks);
        // With 33 status
        $this->assertEquals(33, $defect->active_tasks->first()->status);
    }

    /** @test */
    public function defect_responsible_user_assignment_request_work()
    {
        // pre-create user from group 47
        $user = $this->findOrNewUserFromGroupFortySeven();

        // Given defect
        $defect = factory(Defects::class)->create();
        // Given defect responsible user create task
        $task = $defect->tasks()->first();

        // When we make post request with data
        $response = $this->actingAs($user)->put(route('building::tech_acc::defects.select_responsible', $defect->id), []);

        // Then session should have errors
        $response->assertSessionHasErrors('user_id');
    }

    /** @test */
    public function responsible_user_can_make_decline_put_request()
    {
        // Given user
        $user = $this->findOrNewUserFromGroupFortySeven();
        // Given new defect with responsible user
        $defect = factory(Defects::class)->create(['responsible_user_id' => $user->id, 'status' => 2]);
        // Given notifications count
        $old_notifications_count = Notification::count();

        // When we make put request with data
        $data = ['comment' => $this->faker->paragraph];
        $response = $this->actingAs($user)->put(route('building::tech_acc::defects.decline', $defect->refresh()->id), $data);

        // Then ...
        // everything should be OK
        $response->assertOk();

        $defect->refresh();
        // And our defect should have status equal to 5
        $this->assertEquals(5, $defect->status);

        // And notifications should be generated
        $new_notifications_count = Notification::count();
        $this->assertTrue($new_notifications_count > $old_notifications_count);

        // And notifications relation count should raise
        $this->assertTrue($defect->notifications->count() > 0);

        // And comments (activity) relation count should raise
        $this->assertTrue($defect->comments->count() > 0);

        // All defect tasks should be closed
        $this->assertCount(0, $defect->active_tasks);
    }

    /** @test */
    public function defect_decline_request_work()
    {
        // Given user
        $user = $this->findOrNewUserFromGroupFortySeven();
        // Given new defect with responsible user
        $defect = factory(Defects::class)->create(['responsible_user_id' => $user->id, 'status' => 2]);

        // When we make put request without data
        $data = ['comment' => ''];
        $response = $this->actingAs($user)->put(route('building::tech_acc::defects.decline', $defect->refresh()->id), $data);

        // Then session should have errors
        $response->assertSessionHasErrors('comment');
    }

    /** @test */
    public function responsible_user_can_make_accept_put_request()
    {
        // Given user
        $user = $this->findOrNewUserFromGroupFortySeven();
        // Given new defect with responsible user
        $defect = factory(Defects::class)->create(['responsible_user_id' => $user->id, 'status' => 2]);
        // Given notifications count
        $old_notifications_count = Notification::count();

        // When we make put request with data
        $data = [
            'comment' => $this->faker->paragraph,
            'repair_start_date' => now()->format('d.m.Y'),
            'repair_end_date' => now()->addDay()->format('d.m.Y'),
        ];
        $response = $this->actingAs($user)->put(route('building::tech_acc::defects.accept', $defect->refresh()->id), $data);

        // Then ...
        // everything should be OK
        $response->assertOk();

        $defect->refresh();
        // And our defect should have status equal to 3
        $this->assertEquals(3, $defect->status);

        // And notifications should be generated
        $new_notifications_count = Notification::count();
        $this->assertTrue($new_notifications_count > $old_notifications_count);

        // And notifications relation count should raise
        $this->assertTrue($defect->notifications->count() > 0);

        // And comments (activity) relation count should raise
        $this->assertTrue($defect->comments->count() > 0);

        // Now we must have new task
        $this->assertCount(1, $defect->active_tasks);

        // With type 35 and for defect responsible user
        $new_task = $defect->active_tasks->first();
        $this->assertEquals(35, $new_task->status);
        $this->assertEquals($user->id, $new_task->responsible_user_id);
        // Task should have one notification
        $this->assertCount(1, $new_task->notifications);
    }

    /** @test */
    public function defect_accept_request_work()
    {
        // Given user
        $user = $this->findOrNewUserFromGroupFortySeven();
        // Given new defect with responsible user
        $defect = factory(Defects::class)->create(['responsible_user_id' => $user->id, 'status' => 2]);

        // When we make put request without data
        $data = [
            'comment' => '',
            'repair_start_date' => '',
            'repair_end_date' => '',
        ];
        $response = $this->actingAs($user)->put(route('building::tech_acc::defects.accept', $defect->refresh()->id), $data);

        // Then session should have errors
        $response->assertSessionHasErrors('comment');
        $response->assertSessionHasErrors('repair_start_date');
        $response->assertSessionHasErrors('repair_end_date');
    }

    /** @test */
    public function defect_accept_request_work_dates()
    {
        // Given user
        $user = $this->findOrNewUserFromGroupFortySeven();
        // Given new defect with responsible user
        $defect = factory(Defects::class)->create(['responsible_user_id' => $user->id, 'status' => 2]);

        // When we make put request without data
        $data = [
            'comment' => $this->faker->word,
            'repair_start_date' => '14.12.2019',
            'repair_end_date' => '10.12.2019',
        ];
        $response = $this->actingAs($user)->put(route('building::tech_acc::defects.accept', $defect->refresh()->id), $data);

        // Then session should have errors
        $response->assertSessionHasErrors('repair_end_date');
    }

    /** @test */
    public function after_defect_comment_create_some_notification_should_be_generated()
    {
        $user = $this->user_that_can;
        // Given defect
        $defect = factory(Defects::class)->create(['responsible_user_id' => $user->id, 'status' => 2]);

        // When we add comment to defect
        $defect->comments()->create([
            'comment' => 'bla-bla',
            'author_id' => $user->id
        ]);

        // Then ...
        $defect->refresh();
        // Defect notifications count should be greater than 0
        $this->assertTrue($defect->notifications->count() > 0);
        // Comment creator shouldn't have notification about comment
        $this->assertCount(0, $defect->notifications()->whereUserId($user->id)->get());
        // Defect notifications should have comment inside
        $notification = $defect->notifications->first();
        $this->assertTrue(boolval(strpos($notification->name, 'bla-bla')));
        // With proper type
        $this->assertEquals(76, $notification->type);
    }

    /** @test */
    public function this_model_hook_works_only_with_defect()
    {
        // Given notifications count
        $notification_count = Notification::count();
        // Given comment
        $comment = factory(Comment::class)->create();
        // Given new notifications count
        $new_notification_count = Notification::count();

        // Then we have no new notifications
        $this->assertTrue($notification_count == $new_notification_count);
    }

    /** @test */
    public function responsible_user_can_make_update_repair_dates_put_request()
    {
        // Given user
        $user = $this->findOrNewUserFromGroupFortySeven();
        // Given new defect with responsible user
        $defect = factory(Defects::class)->create(['responsible_user_id' => $user->id, 'status' => 3]);
        // Given notifications count
        $old_notifications_count = Notification::count();

        // When we make put request with data
        $data = [
            'comment' => $this->faker->paragraph,
            'repair_start_date' => now()->format('d.m.Y'),
            'repair_end_date' => now()->addDay()->format('d.m.Y'),
        ];
        $response = $this->actingAs($user)->put(route('building::tech_acc::defects.update_repair_dates', $defect->refresh()->id), $data);

        // Then ...
        // everything should be OK
        $response->assertOk();

        $defect->refresh();
        // And our defect should have status equal to 3
        $this->assertEquals(3, $defect->status);
        // And notifications should be generated
        $new_notifications_count = Notification::count();
        $this->assertTrue($new_notifications_count > $old_notifications_count);
        // And notifications relation count should raise
        $this->assertTrue($defect->notifications->count() > 0);
        // And comments (activity) relation count should raise
        $this->assertTrue($defect->comments->count() > 0);
        // And comment should be like this
        $this->assertEquals($defect->comments()->get()->last()->comment,
            "@user({$defect->responsible_user->id}) изменил сроки ремонта по заявке на дефект. Новый период: с {$defect->repair_start} по {$defect->repair_end}. Комментарий: {$data['comment']}");
        // And dates should update
        $this->assertEquals($data['repair_start_date'], $defect->repair_start);
        $this->assertEquals($data['repair_end_date'], $defect->repair_end);
    }

    /** @test */
    public function soon_expire_scope()
    {
        // Given user
        $user = $this->user_that_can;
        // Clear all defects
        Defects::query()->delete();
        // Given four defects
        // One that will expire soon
        $defect1 = factory(Defects::class)->create(['responsible_user_id' => $user->id, 'status' => 3, 'repair_start_date' => now()->subDay(), 'repair_end_date' => now()]);
        // Second with normal dates
        $defect2 = factory(Defects::class)->create(['responsible_user_id' => $user->id, 'status' => 3, 'repair_start_date' => now()->subDay(), 'repair_end_date' => now()->addDays(1)]);
        // Third is closed
        $defect3 = factory(Defects::class)->create(['responsible_user_id' => $user->id, 'status' => 4, 'repair_start_date' => now()->subDay(), 'repair_end_date' => now()->addDays(2)]);
        // Fourth is in diagnosis
        $defect4 = factory(Defects::class)->create(['responsible_user_id' => $user->id, 'status' => 2]);

        // When we make a query with scope
        $result = Defects::soonExpire()->get();

        // Then ...
        // Result must have count equal to one
        $this->assertCount(1, $result);
        // Result must return $defect1, because it will expire soon
        $this->assertEquals($result->first()->id, $defect1->id);
    }

    /** @test */
    public function responsible_user_can_make_repair_end_put_request()
    {
        // Given user
        $user = $this->findOrNewUserFromGroupFortySeven();
        // Given new defect with responsible user
        $defect = factory(Defects::class)->create(['responsible_user_id' => $user->id, 'status' => 3]);
        // Given notifications count
        $old_notifications_count = Notification::count();

        // When we make put request with data
        $data = [
            'comment' => $this->faker->paragraph,
            'start_location_id' => ProjectObject::inRandomOrder()->first()->id ?? factory(ProjectObject::class)->create()->id,
        ];
        $response = $this->actingAs($user)->put(route('building::tech_acc::defects.end_repair', $defect->id), $data);

        // Then ...
        // everything should be OK
        $response->assertOk();

        $defect->refresh();
        // And our defect should have status equal to 3
        $this->assertEquals(4, $defect->status);
        // And notifications should be generated
        $new_notifications_count = Notification::count();
        $this->assertTrue($new_notifications_count > $old_notifications_count);
        // And notifications relation count should raise
        $this->assertTrue($defect->notifications->count() > 0);
        // And comments (activity) relation count should raise
        $this->assertTrue($defect->comments->count() > 0);
        // And comment should be like this
        $this->assertEquals($defect->comments()->get()->last()->comment,
            "@user({$defect->responsible_user->id}) завершил ремонт по заявке на дефект. Комментарий: {$data['comment']}");
        // And dates should update
        $this->assertTrue(now()->gte($defect->repair_end_date));
    }

    /** @test */
    public function responsible_user_can_make_repair_end_put_request_work()
    {
        // Given user
        $user = $this->findOrNewUserFromGroupFortySeven();
        // Given new defect with responsible user
        $defect = factory(Defects::class)->create(['responsible_user_id' => $user->id, 'status' => 3]);
        // Given notifications count
        $old_notifications_count = Notification::count();

        // When we make put request with data
        $data = [];
        $response = $this->actingAs($user)->put(route('building::tech_acc::defects.end_repair', $defect->id), $data);

        // Then session should have errors
        $response->assertSessionHasErrors(['comment', 'start_location_id']);
    }

    /** @test */
    public function not_author_of_defect_cant_delete_defect()
    {
        // Given user
        $user = $this->findOrNewUserFromGroupFortySeven();
        // Given defect
        $defect = factory(Defects::class)->create(['user_id' => $user->id]);

        // When non author make this delete request
        $non_author = factory(User::class)->create();
        $response = $this->actingAs($non_author)->delete(route('building::tech_acc::defects.destroy', $defect->id));

        // Then this user must have 403
        $response->assertForbidden();
    }

    /** @test */
    public function author_of_defect_cant_delete_defect_if_it_not_new()
    {
        // Given user
        $author = $this->findOrNewUserFromGroupFortySeven();
        // Given defect
        $defect = factory(Defects::class)->create(['user_id' => $author->id, 'status' => Defects::IN_WORK]);

        // When non author make this delete request
        $response = $this->actingAs($author)->delete(route('building::tech_acc::defects.destroy', $defect->id));

        // Then this user must have 403
        $response->assertForbidden();
    }

    /** @test */
    public function author_of_defect_can_delete_defect_if_it_new()
    {
        // Given user
        $author = $this->findOrNewUserFromGroupFortySeven();
        // Given defect
        $defect = factory(Defects::class)->create(['user_id' => $author->id, 'status' => Defects::NEW]);
        // Given old notifications count
        $old_notifications_count = Notification::count();

        // When non author make this delete request
        $response = $this->actingAs($author)->delete(route('building::tech_acc::defects.destroy', $defect->id));

        // Then ...
        // Everything should be OK
        $response->assertOk();
        // Defect must move in DELETED status
        $defect->refresh();
        $this->assertTrue($defect->isDeleted());
        // Defect tasks must be solved
        $this->assertCount(0, $defect->active_tasks);
        // And notifications should be generated
        $new_notifications_count = Notification::count();
        $this->assertTrue($new_notifications_count > $old_notifications_count);
        // And notifications relation count should raise
        $this->assertTrue($defect->notifications->count() > 0);
        // And comments (activity) relation count should raise
        $this->assertTrue($defect->comments->count() > 0);
        // And comment should be like this
        $this->assertEquals($defect->comments()->get()->last()->comment, "@user({$defect->author->id}) удалил заявку на неисправность.");
    }

    /** @test */
    public function defect_filter_work_without_any_filters_and_defects()
    {
        // Given no defects
        // Given user
        $user = $this->user_that_cannot;

        // When we make get request
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.paginated', []))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains nothing
        $this->assertCount(0, $response['data']['defects']);
    }

    /** @test */
    public function defect_filter_work_without_any_filters()
    {
        // Given three defects
        $defects = factory(Defects::class, 3)->create();
        // Given user
        $user = $this->principle;

        // When we make get request
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.paginated', []))->json();

        // Then ...
        // In response we must have array
        $this->assertTrue(is_array($response));
        // This array must have data key
        $this->assertTrue(array_key_exists('data', $response));
        // Data key must return array
        $this->assertTrue(is_array($response['data']));
        // This array must contains three defects
        $this->assertCount(3, $response['data']['defects']);
    }

    /** @test */
    public function defect_filter_can_return_all_defects()
    {
        // Given three defects
        $defects = factory(Defects::class, 2)->create();
        $defect = factory(Defects::class)->create(['status' => Defects::CLOSED]);
        // Given user
        $user = $this->principle;

        // When we make get request with show_all
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.paginated', ['url' => route('building::tech_acc::defects.index') . '?show_active=true']))->json();

        // Then ...
        // This array must contains three defects
        $this->assertCount(3, $response['data']['defects']);
    }

    /** @test */
    public function defect_filter_status()
    {
        // Given three defects
        $defect1 = factory(Defects::class)->create(['status' => Defects::DIAGNOSTICS]);
        $defect2 = factory(Defects::class)->create(['status' => Defects::NEW]);
        $defect3 = factory(Defects::class)->create(['status' => Defects::CLOSED]);
        // Given user
        $user = $this->principle;

        // When we make get request with status
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.paginated', ['url' => route('building::tech_acc::defects.index') . '?status=диагно']))->json();

        // Then ...
        // This array must contains one defect
        $this->assertCount(1, $response['data']['defects']);
        // An this defect is $defect1
        $this->assertEquals($defect1->id, $response['data']['defects'][0]['id']);
    }

    /** @test */
    public function defect_filter_author()
    {
        // Given three defects
        $defect1 = factory(Defects::class)->create(['user_id' => 1]);
        $defect2 = factory(Defects::class)->create(['user_id' => 1]);
        $defect3 = factory(Defects::class)->create(['user_id' => 2]);
        // Given user
        $user = $this->principle;

        // When we make get request with status
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.paginated', ['url' => route('building::tech_acc::defects.index') . '?user_id=1']))->json();

        // Then ...
        // This array must contains two defects
        $this->assertCount(2, $response['data']['defects']);
        // And this defect is $defect1, $defect2
        $this->assertEquals([$defect1->id, $defect2->id], [$response['data']['defects'][0]['id'], $response['data']['defects'][1]['id']]);
    }

    /** @test */
    public function defect_filter_authors()
    {
        // Given two users
        $results = User::inRandomOrder()->get();
        $user1 = $results->first()->id;
        $user2 = $results->last()->id;
        // Given four defects
        $defect1 = factory(Defects::class)->create(['user_id' => $user1]);
        $defect2 = factory(Defects::class)->create(['user_id' => $user1]);
        $defect3 = factory(Defects::class)->create(['user_id' => $user2]);
        $defect4 = factory(Defects::class)->create(['user_id' => 2]);
        // Given user
        $user = $this->principle;

        // When we make get request with status
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.paginated', ['url' => route('building::tech_acc::defects.index') . "?user_id%5B0%5D={$user1}&user_id%5B1%5D={$user2}"]))->json();

        // Then ...
        // This array must contains three defects
        $this->assertCount(3, $response['data']['defects']);
        // And this defect is $defect1, $defect2, $defect3
        $this->assertEquals([$defect1->id, $defect2->id, $defect3->id], [$response['data']['defects'][0]['id'], $response['data']['defects'][1]['id'], $response['data']['defects'][2]['id']]);
    }

    /** @test */
    public function defect_filter_responsible_user()
    {
        // Given three defects
        $defect1 = factory(Defects::class)->create(['responsible_user_id' => 1]);
        $defect2 = factory(Defects::class)->create(['responsible_user_id' => 1]);
        $defect3 = factory(Defects::class)->create(['responsible_user_id' => 2]);
        // Given user
        $user = $this->principle;

        // When we make get request with status
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.paginated', ['url' => route('building::tech_acc::defects.index') . "?responsible_user_id=2"]))->json();

        // Then ...
        // This array must contains one defect
        $this->assertCount(1, $response['data']['defects']);
        // And this defect is $defect3
        $this->assertEquals($defect3->id, $response['data']['defects'][0]['id']);
    }

    /** @test */
    public function defect_filter_responsible_users()
    {
        // Given three defects
        $defect1 = factory(Defects::class)->create(['responsible_user_id' => 1]);
        $defect2 = factory(Defects::class)->create(['responsible_user_id' => 3]);
        $defect3 = factory(Defects::class)->create(['responsible_user_id' => 2]);
        // Given user
        $user = $this->principle;

        // When we make get request with status
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.paginated', ['url' => route('building::tech_acc::defects.index') . "?responsible_user_id%5B0%5D=1&responsible_user_id%5B1%5D=2"]))->json();

        // Then ...
        // This array must contains two defects
        $this->assertCount(2, $response['data']['defects']);
        // And this defect is $defect3
        $this->assertEquals([$defect1->id, $defect3->id], [$response['data']['defects'][0]['id'], $response['data']['defects'][1]['id']]);
    }

    /** @test */
    public function defect_filter_responsible_user_one_more_time()
    {
        // Given three defects
        $defect1 = factory(Defects::class)->create();
        $defect2 = factory(Defects::class)->create();
        $defect3 = factory(Defects::class)->create();
        // Given user
        $user = $this->principle;

        // When we make get request with status
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.paginated', ['url' => route('building::tech_acc::defects.index') . "?responsible_user_id%5B0%5D=2"]))->json();

        // Then ...
        // This array must contains nothing
        $this->assertCount(0, $response['data']['defects']);
    }

    /** @test */
    public function defect_filter_repair_start_date()
    {
        // Given three defects
        $defect1 = factory(Defects::class)->create(['repair_start_date' => now()->subDay()]);
        $defect2 = factory(Defects::class)->create(['repair_start_date' => now()->subDays(2)]);
        $defect3 = factory(Defects::class)->create(['repair_start_date' => $time = now()]);
        // Given user
        $user = $this->principle;

        // When we make get request with date
        $date = now()->subDay()->format('d.m.Y');
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.paginated', ['url' => route('building::tech_acc::defects.index') . "?repair_start_date={$date}"]))->json();

        // Then ...
        // This array must contains two defects
        $this->assertCount(2, $response['data']['defects']);
        // And this defect is $defect1, $defect3
        $this->assertEquals([$defect1->id, $defect3->id], [$response['data']['defects'][0]['id'], $response['data']['defects'][1]['id']]);
    }

    /** @test */
    public function defect_filter_repair_end_date()
    {
        // Given three defects
        $defect1 = factory(Defects::class)->create(['repair_end_date' => now()->subDay()]);
        $defect2 = factory(Defects::class)->create(['repair_end_date' => now()->subDays(2)]);
        $defect3 = factory(Defects::class)->create(['repair_end_date' => $time = now()]);
        // Given user
        $user = $this->principle;

        // When we make get request with date
        $date = now()->subDay()->format('d.m.Y');
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.paginated', ['url' => route('building::tech_acc::defects.index') . "?repair_end_date={$date}"]))->json();

        // Then ...
        // This array must contains two defects
        $this->assertCount(2, $response['data']['defects']);
        // And this defect is $defect1, $defect2
        $this->assertEquals([$defect1->id, $defect2->id], [$response['data']['defects'][0]['id'], $response['data']['defects'][1]['id']]);
    }

    /** @test */
    public function defect_filter_repair_dates_together()
    {
        // Given three defects
        $defect1 = factory(Defects::class)->create(['repair_start_date' => now()->subDay(), 'repair_end_date' => now()->addDays(10)]);
        $defect2 = factory(Defects::class)->create(['repair_start_date' => now()->subDays(2), 'repair_end_date' => now()->addDays(8)]);
        $defect3 = factory(Defects::class)->create(['repair_start_date' => now(), 'repair_end_date' => now()->addDays(7)]);
        // Given user
        $user = $this->principle;

        // When we make get request with dates
        $date1 = now()->subDays(2)->format('d.m.Y');
        $date2 = now()->addDays(8)->format('d.m.Y');
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.paginated', ['url' => route('building::tech_acc::defects.index') . "?repair_start_date={$date1}&repair_end_date={$date2}"]))->json();

        // Then ...
        // This array must contains two defects
        $this->assertCount(2, $response['data']['defects']);
        // And this defect is $defect2, $defect3
        $this->assertEquals([$defect2->id, $defect3->id], [$response['data']['defects'][0]['id'], $response['data']['defects'][1]['id']]);
    }

    /** @test */
    public function defect_filter_repair_dates_together_reverse()
    {
        // Given three defects
        $defect1 = factory(Defects::class)->create(['repair_start_date' => now()->subDay(), 'repair_end_date' => now()->addDays(10)]);
        $defect2 = factory(Defects::class)->create(['repair_start_date' => now()->subDays(2), 'repair_end_date' => now()->addDays(8)]);
        $defect3 = factory(Defects::class)->create(['repair_start_date' => now(), 'repair_end_date' => now()->addDays(7)]);
        // Given user
        $user = $this->principle;

        // When we make get request with dates
        $date1 = now()->subDays(2)->format('d.m.Y');
        $date2 = now()->addDays(8)->format('d.m.Y');
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.paginated', ['url' => route('building::tech_acc::defects.index') . "?repair_end_date={$date2}&repair_start_date={$date1}"]))->json();

        // Then ...
        // This array must contains two defects
        $this->assertCount(2, $response['data']['defects']);
        // And this defect is $defect2, $defect3
        $this->assertEquals([$defect2->id, $defect3->id], [$response['data']['defects'][0]['id'], $response['data']['defects'][1]['id']]);
    }


    /** @test */
    public function defect_filter_brand()
    {
        // Given three technics
        $technic1 = factory(OurTechnic::class)->create(['brand' => 'brand1']);
        $technic2 = factory(OurTechnic::class)->create(['brand' => 'brand2']);
        $technic3 = factory(OurTechnic::class)->create(['brand' => 'brand3']);
        // Given three defects
        $defect1 = factory(Defects::class)->create(['defectable_id' => $technic1->id]);
        $defect2 = factory(Defects::class)->create(['defectable_id' => $technic2->id]);
        $defect3 = factory(Defects::class)->create(['defectable_id' => $technic3->id]);

        // Given user
        $user = $this->principle;

        // When we make get request with brand
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.paginated', ['url' => route('building::tech_acc::defects.index') . "?brand=brand3"]))->json();

        // Then ...
        // This array must contains one defect
        $this->assertCount(1, $response['data']['defects']);
        // And this defect is $defect3
        $this->assertEquals($defect3->id, $response['data']['defects'][0]['id']);
    }

    /** @test */
    public function defect_filter_brand_second()
    {
        // Given three technics
        $technic1 = factory(OurTechnic::class)->create(['brand' => 'brand1']);
        $technic2 = factory(OurTechnic::class)->create(['brand' => 'brand2']);
        $technic3 = factory(OurTechnic::class)->create(['brand' => 'brand3']);
        // Given three defects
        $defect1 = factory(Defects::class)->create(['defectable_id' => $technic1->id]);
        $defect2 = factory(Defects::class)->create(['defectable_id' => $technic2->id]);
        $defect3 = factory(Defects::class)->create(['defectable_id' => $technic3->id]);

        // Given user
        $user = $this->principle;

        // When we make get request with brand
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.paginated', ['filters' => ['brand'], 'values' => ['bra']]))->json();

        // Then ...
        // This array must contains three defects
        $this->assertCount(3, $response['data']['defects']);
        $this->assertEquals(
            [$defect1->id, $defect2->id, $defect3->id],
            [$response['data']['defects'][0]['id'], $response['data']['defects'][1]['id'], $response['data']['defects'][2]['id']]
        );
    }

    /** @test */
    public function defect_filter_model()
    {
        // Given three technics
        $technic1 = factory(OurTechnic::class)->create(['model' => 'model1']);
        $technic2 = factory(OurTechnic::class)->create(['model' => 'model2']);
        $technic3 = factory(OurTechnic::class)->create(['model' => 'model3']);

        // Given three defects
        $defect1 = factory(Defects::class)->create(['defectable_id' => $technic1->id]);
        $defect2 = factory(Defects::class)->create(['defectable_id' => $technic2->id]);
        $defect3 = factory(Defects::class)->create(['defectable_id' => $technic3->id]);

        // Given user
        $user = $this->principle;

        // When we make get request with model
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.paginated', ['url' => route('building::tech_acc::defects.index') . "?model=model1"]))->json();

        // Then ...
        // This array must contains one defect
        $this->assertCount(1, $response['data']['defects']);
        // And this defect is $defect1
        $this->assertEquals($defect1->id, $response['data']['defects'][0]['id']);
    }

    /** @test */
    public function defect_filter_owner()
    {
        // Given three technics
        $technic1 = factory(OurTechnic::class)->create(['owner' => 'ООО «СТРОЙМАСТЕР»']);
        $technic2 = factory(OurTechnic::class)->create(['owner' => 'ООО «ГОРОД»']);
        $technic3 = factory(OurTechnic::class)->create(['owner' => 'ООО «ГОРОД»']);

        // Given three defects
        $defect1 = factory(Defects::class)->create(['defectable_id' => $technic1->id]);
        $defect2 = factory(Defects::class)->create(['defectable_id' => $technic2->id]);
        $defect3 = factory(Defects::class)->create(['defectable_id' => $technic3->id]);

        // Given user
        $user = $this->principle;

        // When we make get request with owner
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.paginated', ['url' => route('building::tech_acc::defects.index') . "?owner=ООО%20«СТРОЙМАСТЕР»"]))->json();

        // Then ...
        // This array must contains one defect
        $this->assertCount(1, $response['data']['defects']);
        // And this defect is $defect1
        $this->assertEquals($defect1->id, $response['data']['defects'][0]['id']);
    }

    /** @test */
    public function defect_filter_inventory_number()
    {
        // Given three technics
        $technic1 = factory(OurTechnic::class)->create(['inventory_number' => '159987']);
        $technic2 = factory(OurTechnic::class)->create(['inventory_number' => '156987']);
        $technic3 = factory(OurTechnic::class)->create(['inventory_number' => '777777']);

        // Given three defects
        $defect1 = factory(Defects::class)->create(['defectable_id' => $technic1->id]);
        $defect2 = factory(Defects::class)->create(['defectable_id' => $technic2->id]);
        $defect3 = factory(Defects::class)->create(['defectable_id' => $technic3->id]);

        // Given user
        $user = $this->principle;

        // When we make get request with inventory_number
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.paginated', ['url' => route('building::tech_acc::defects.index') . "?inventory_number=98"]))->json();

        // Then ...
        // This array must contains two defects
        $this->assertCount(2, $response['data']['defects']);
        // And this defect is $defect1, $defect2
        $this->assertEquals(
            [$defect1->id, $defect2->id],
            [$response['data']['defects'][0]['id'], $response['data']['defects'][1]['id']]
        );
    }

    /** @test */
    public function defect_filter_inventory_number_array()
    {
        // Given three technics
        $technic1 = factory(OurTechnic::class)->create(['inventory_number' => '159987']);
        $technic2 = factory(OurTechnic::class)->create(['inventory_number' => '15986']);
        $technic3 = factory(OurTechnic::class)->create(['inventory_number' => '777777']);

        // Given three defects
        $defect1 = factory(Defects::class)->create(['defectable_id' => $technic1->id]);
        $defect2 = factory(Defects::class)->create(['defectable_id' => $technic2->id]);
        $defect3 = factory(Defects::class)->create(['defectable_id' => $technic3->id]);

        // Given user
        $user = $this->principle;

        // When we make get request with inventory_number
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.paginated', ['url' => route('building::tech_acc::defects.index') . "?inventory_number%5B0%5D=986&inventory_number%5B1%5D=77"]))->json();

        // Then ...
        // This array must contains two defects
        $this->assertCount(2, $response['data']['defects']);
        // And this defect is $defect1, $defect2
        $this->assertEquals(
            [$defect2->id, $defect3->id],
            [$response['data']['defects'][0]['id'], $response['data']['defects'][1]['id']]
        );
    }

    /** @test */
    public function defect_filter_inventory_number_and_owner()
    {
        // Given three technics
        $technic1 = factory(OurTechnic::class)->create(['inventory_number' => '159987', 'owner' => 'ООО «СТРОЙМАСТЕР»']);
        $technic2 = factory(OurTechnic::class)->create(['inventory_number' => '156987', 'owner' => 'ООО «ГОРОД»']);
        $technic3 = factory(OurTechnic::class)->create(['inventory_number' => '777777', 'owner' => 'ООО «ГОРОД»']);

        // Given three defects
        $defect1 = factory(Defects::class)->create(['defectable_id' => $technic1->id]);
        $defect2 = factory(Defects::class)->create(['defectable_id' => $technic2->id]);
        $defect3 = factory(Defects::class)->create(['defectable_id' => $technic3->id]);

        // Given user
        $user = $this->principle;

        // When we make get request with inventory number and owner
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.paginated', ['url' => route('building::tech_acc::defects.index') . "?owner=РО&inventory_number=98"]))->json();

        // Then ...
        // This array must contains two defects
        $this->assertCount(2, $response['data']['defects']);
        // And this defect is $defect1, $defect2
        $this->assertEquals(
            [$defect1->id, $defect2->id],
            [$response['data']['defects'][0]['id'], $response['data']['defects'][1]['id']]
        );
    }

    /** @test */
    public function defect_filter_inventory_number_and_owner_wrong_query()
    {
        // Given three technics
        $technic1 = factory(OurTechnic::class)->create(['inventory_number' => '159987', 'owner' => 'ООО «СТРОЙМАСТЕР»']);
        $technic2 = factory(OurTechnic::class)->create(['inventory_number' => '156987', 'owner' => 'ООО «ГОРОД»']);
        $technic3 = factory(OurTechnic::class)->create(['inventory_number' => '777777', 'owner' => 'ООО «ГОРОД»']);

        // Given three defects
        $defect1 = factory(Defects::class)->create(['defectable_id' => $technic1->id]);
        $defect2 = factory(Defects::class)->create(['defectable_id' => $technic2->id]);
        $defect3 = factory(Defects::class)->create(['defectable_id' => $technic3->id]);

        // Given user
        $user = $this->principle;

        // When we make get request with inventory number and owner
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.paginated', ['url' => route('building::tech_acc::defects.index') . "?owner=ПРОСТОЧТОБЫНЕНАШЛО&inventory_number=98"]))->json();

        // Then ...
        // This array must contains nothing
        $this->assertCount(0, $response['data']['defects']);
    }

    /** @test */
    public function defect_filter_defectable_work_with_technic()
    {
        // Given three technics
        $technic1 = factory(OurTechnic::class)->create(['inventory_number' => '159987', 'owner' => 'ООО «СТРОЙМАСТЕР»']);
        $technic2 = factory(OurTechnic::class)->create(['inventory_number' => '156987', 'owner' => 'ООО «ГОРОД»']);
        $technic3 = factory(OurTechnic::class)->create(['inventory_number' => '777777', 'owner' => 'ООО «ГОРОД»']);

        // Given three defects
        $defect1 = factory(Defects::class)->create(['defectable_id' => $technic1->id]);
        $defect2 = factory(Defects::class)->create(['defectable_id' => $technic2->id]);
        $defect3 = factory(Defects::class)->create(['defectable_id' => $technic3->id]);

        // Given user
        $user = $this->principle;

        // When we make get request with defectable
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.paginated', ['url' => route('building::tech_acc::defects.index') . "?defectable={$technic1->id}%7C1"]))->json();

        // Then ...
        // This array must contains one defect
        $this->assertCount(1, $response['data']['defects']);
        // And this defect is $defect1, $defect2
        $this->assertEquals(
            [$defect1->id],
            [$response['data']['defects'][0]['id']]
        );
    }

    /** @test */
    public function defect_filter_defectable_work_with_fuel_tank()
    {
        // Given three fuelTanks
        $fuelTank1 = factory(FuelTank::class)->create();
        $fuelTank2 = factory(FuelTank::class)->create();
        $fuelTank3 = factory(FuelTank::class)->create();
        // Given user
        $user = $this->user_that_cannot;
        // Given three defects
        $defect1 = factory(Defects::class)->create(['defectable_id' => $fuelTank1->id, 'defectable_type' => FuelTank::class, 'user_id' => $user->id]);
        $defect2 = factory(Defects::class)->create(['defectable_id' => $fuelTank2->id, 'defectable_type' => FuelTank::class]);
        $defect3 = factory(Defects::class)->create(['defectable_id' => $fuelTank3->id, 'defectable_type' => FuelTank::class]);

        // When we make get request with defectable
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.paginated', ['url' => route('building::tech_acc::defects.index') . "?defectable={$fuelTank1->id}%7C2"]))->json();

        // Then ...
        // This array must contains one defect
        $this->assertCount(1, $response['data']['defects']);
        // And this defect is $defect1, $defect2
        $this->assertEquals(
            [$defect1->id],
            [$response['data']['defects'][0]['id']]
        );
    }

    /** @test */
    public function defect_filter_defectable_work_with_array()
    {
        // Given two fuelTanks and one Techinc
        $technic1 = factory(OurTechnic::class)->create(['inventory_number' => '159987', 'owner' => 'ООО «СТРОЙМАСТЕР»']);
        $fuelTank2 = factory(FuelTank::class)->create();
        $fuelTank3 = factory(FuelTank::class)->create();

        // Given three defects
        $defect1 = factory(Defects::class)->create(['defectable_id' => $technic1->id, 'defectable_type' => OurTechnic::class]);
        $defect2 = factory(Defects::class)->create(['defectable_id' => $fuelTank2->id, 'defectable_type' => FuelTank::class]);
        $defect3 = factory(Defects::class)->create(['defectable_id' => $fuelTank3->id, 'defectable_type' => FuelTank::class]);
        // Given user
        $user = $this->principle;

        // When we make get request with defectable
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.paginated', ['url' => route('building::tech_acc::defects.index') . "?defectable%5B0%5D={$technic1->id}%7C1&defectable%5B1%5D={$fuelTank2->id}%7C2"]))->json();

        // Then ...
        // This array must contains two defects
        $this->assertCount(2, $response['data']['defects']);
        // And this defect is $defect1, $defect2
        $this->assertEquals(
            [$defect1->id, $defect2->id],
            [$response['data']['defects'][0]['id'], $response['data']['defects'][1]['id']]
        );
    }

    /** @test */
    public function defect_status_filter_works_with_statuses_array()
    {
        // Given three defects
        $defect1 = factory(Defects::class)->create(['status' => Defects::DIAGNOSTICS]);
        $defect2 = factory(Defects::class)->create(['status' => Defects::NEW]);
        $defect3 = factory(Defects::class)->create(['status' => Defects::CLOSED]);
        // Given user
        $user = $this->principle;

        // When we make post request with status
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.paginated', ['url' => route('building::tech_acc::defects.index') . '?status%5B0%5D=Новая%20заявка&status%5B1%5D=Диагностика']))->json();

        // Then ...
        // This array must contains two defects
        $this->assertCount(2, $response['data']['defects']);
        // An this defect is $defect1
        $this->assertEquals([$defect1->id, $defect2->id], [$response['data']['defects'][0]['id'], $response['data']['defects'][1]['id']]);
    }

    /** @test */
    public function defect_filter_tank_number()
    {
        // Given three fuelTanks
        $fuelTank1 = factory(FuelTank::class)->create();
        $fuelTank2 = factory(FuelTank::class)->create();
        $fuelTank3 = factory(FuelTank::class)->create();

        // Given three defects
        $defect1 = factory(Defects::class)->create(['defectable_id' => $fuelTank1->id, 'defectable_type' => FuelTank::class]);
        $defect2 = factory(Defects::class)->create(['defectable_id' => $fuelTank2->id, 'defectable_type' => FuelTank::class]);
        $defect3 = factory(Defects::class)->create(['defectable_id' => $fuelTank3->id, 'defectable_type' => FuelTank::class]);
        // Given user
        $user = $this->principle;

        // When we make get request with defectable
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.paginated', ['url' => route('building::tech_acc::defects.index') . "?tank_number={$fuelTank1->tank_number}"]))->json();

        // Then ...
        // This array must contains one defect
        $this->assertCount(1, $response['data']['defects']);
        // And this defect is $defect1
        $this->assertEquals(
            [$defect1->id],
            [$response['data']['defects'][0]['id']]
        );
    }

    /** @test */
    public function defect_filter_tank_number_array()
    {
        // Given three fuelTanks
        $fuelTank1 = factory(FuelTank::class)->create();
        $fuelTank2 = factory(FuelTank::class)->create();
        $fuelTank3 = factory(FuelTank::class)->create();

        // Given three defects
        $defect1 = factory(Defects::class)->create(['defectable_id' => $fuelTank1->id, 'defectable_type' => FuelTank::class]);
        $defect2 = factory(Defects::class)->create(['defectable_id' => $fuelTank2->id, 'defectable_type' => FuelTank::class]);
        $defect3 = factory(Defects::class)->create(['defectable_id' => $fuelTank3->id, 'defectable_type' => FuelTank::class]);
        // Given user
        $user = $this->principle;

        // When we make get request with defectable
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.paginated', ['url' => route('building::tech_acc::defects.index') . "?tank_number%5B0%5D={$fuelTank1->tank_number}&tank_number%5B1%5D={$fuelTank2->tank_number}"]))->json();

        // Then ...
        // This array must contains two defects
        $this->assertCount(2, $response['data']['defects']);
        // And this defects is $defect1, $defect2
        $this->assertEquals(
            [$defect1->id, $defect2->id],
            [$response['data']['defects'][0]['id'], $response['data']['defects'][1]['id']]
        );
    }

    /** @test */
    public function defect_filter_work_only_with_filters()
    {
        // Given three defects
        $defect1 = factory(Defects::class)->create();
        $defect2 = factory(Defects::class)->create();
        $defect3 = factory(Defects::class)->create();
        // Given user
        $user = $this->principle;

        // When we make get request with defectable
        $response = $this->actingAs($user)->post(route('building::tech_acc::defects.paginated', ['url' => route('building::tech_acc::defects.index') . "?anything=nothing"]))->json();

        // Then this array must contains three defects
        $this->assertCount(3, $response['data']['defects']);
    }

    /** @test */
    public function defects_author_getter_return_authors_without_any_params()
    {
        // Given three defects
        $defect1 = factory(Defects::class)->create(['user_id' => $this->user_that_can->id]);
        $defect2 = factory(Defects::class)->create(['user_id' => $this->user_that_cannot->id]);
        $defect3 = factory(Defects::class)->create(['user_id' => $this->user_that_can->id]);

        // When we make get request
        $response = $this->actingAs($this->user_that_can)->get(route('users::get_authors_for_defects'))->json();

        // Then ...
        // We must have two users in response
        $this->assertCount(2, $response);
        // And this user must be user that can and that cannot
        $this->assertEquals([$this->user_that_cannot->id, $this->user_that_can->id], [$response[0]['code'], $response[1]['code']]);
    }

    /** @test */
    public function defects_author_getter_return_authors_by_name()
    {
        // Given three defects
        $defect1 = factory(Defects::class)->create(['user_id' => $this->user_that_can->id]);
        $defect2 = factory(Defects::class)->create(['user_id' => $this->user_that_cannot->id]);
        $defect3 = factory(Defects::class)->create(['user_id' => $this->user_that_can->id]);

        // When we make get request
        $response = $this->actingAs($this->user_that_can)->get(route('users::get_authors_for_defects', ['q' => $this->user_that_can->first_name]))->json();

        // Then ...
        // We must have one user in response
        $this->assertCount(1, $response);
        // And this user must be user that can
        $this->assertEquals($this->user_that_can->id, $response[0]['code']);
    }

    /** @test */
    public function defects_author_getter_can_return_for_all_defects()
    {
        // Given three defects
        $defect1 = factory(Defects::class)->create(['user_id' => $this->user_that_can->id, 'status' => Defects::CLOSED]);
        $defect2 = factory(Defects::class)->create(['user_id' => $this->user_that_cannot->id]);
        $defect3 = factory(Defects::class)->create(['user_id' => $this->user_that_can->id, 'status' => Defects::CLOSED]);

        // When we make get request
        $response = $this->actingAs($this->user_that_can)->get(route('users::get_authors_for_defects', ['show_active' => false]))->json();

        // Then ...
        // We must have one user in response
        $this->assertCount(2, $response);
        // And this user must be user that can and that cannot
        $this->assertEquals([$this->user_that_cannot->id, $this->user_that_can->id], [$response[0]['code'], $response[1]['code']]);
    }

    /** @test */
    public function defects_responsible_user_getter_return_responsible_users_without_any_params()
    {
        // Given three defects
        $defect1 = factory(Defects::class)->create(['responsible_user_id' => $this->user_that_can->id]);
        $defect2 = factory(Defects::class)->create(['responsible_user_id' => $this->user_that_cannot->id]);
        $defect3 = factory(Defects::class)->create(['responsible_user_id' => $this->user_that_can->id]);

        // When we make get request
        $response = $this->actingAs($this->user_that_can)->get(route('users::get_responsible_users_for_defects'))->json();

        // Then ...
        // We must have two users in response
        $this->assertCount(2, $response);
        // And this user must be user that can
        $this->assertEquals([$this->user_that_cannot->id, $this->user_that_can->id], [$response[0]['code'], $response[1]['code']]);
    }

    /** @test */
    public function defects_responsible_user_getter_return_responsible_users_by_name()
    {
        // Given three defects
        $defect1 = factory(Defects::class)->create(['responsible_user_id' => $this->user_that_can->id]);
        $defect2 = factory(Defects::class)->create(['responsible_user_id' => $this->user_that_cannot->id]);
        $defect3 = factory(Defects::class)->create(['responsible_user_id' => $this->user_that_can->id]);

        // When we make get request
        $response = $this->actingAs($this->user_that_can)->get(route('users::get_responsible_users_for_defects', ['q' => $this->user_that_can->first_name]))->json();

        // Then ...
        // We must have one user in response
        $this->assertCount(1, $response);
        // And this user must be user that can
        $this->assertEquals($this->user_that_can->id, $response[0]['code']);
    }

    /** @test */
    public function defects_responsible_user_getter_can_return_responsible_users_for_all_defects()
    {
        // Given three defects
        $defect1 = factory(Defects::class)->create(['responsible_user_id' => $this->user_that_can->id, 'status' => Defects::CLOSED]);
        $defect2 = factory(Defects::class)->create(['responsible_user_id' => $this->user_that_cannot->id]);
        $defect3 = factory(Defects::class)->create(['responsible_user_id' => $this->user_that_can->id, 'status' => Defects::DECLINED]);

        // When we make get request
        $response = $this->actingAs($this->user_that_can)->get(route('users::get_responsible_users_for_defects', ['show_active' => false]))->json();

        // Then ...
        // We must have one user in response
        $this->assertCount(2, $response);
        // And this user must be user that can and that cannot
        $this->assertEquals([$this->user_that_cannot->id, $this->user_that_can->id], [$response[0]['code'], $response[1]['code']]);
    }

    /** @test */
    public function defects_responsible_user_getter_can_return_responsible_users_for_all_defects_which_have_responsible_users()
    {
        // Given three defects
        $defect1 = factory(Defects::class)->create(['status' => Defects::CLOSED]);
        $defect2 = factory(Defects::class)->create();
        $defect3 = factory(Defects::class)->create(['responsible_user_id' => $this->user_that_can->id, 'status' => Defects::DECLINED]);

        // When we make get request
        $response = $this->actingAs($this->user_that_can)->get(route('users::get_responsible_users_for_defects', ['show_active' => false]))->json();

        // Then ...
        // We must have one user in response
        $this->assertCount(1, $response);
        // And this user must be user that can
        $this->assertEquals($this->user_that_can->id, $response[0]['code']);
    }

    /** @test */
    public function users_who_have_permissions_can_see_all_defects()
    {
        // Given three defects
        $defect1 = factory(Defects::class)->create();
        $defect2 = factory(Defects::class)->create();
        $defect3 = factory(Defects::class)->create();

        // When we use Defect::filter()
        // as principle
        $this->actingAs($this->principle);
        $results = Defects::filter(request())->permissionCheck()->get();

        // Then ...
        // Result must contains three defects
        $this->assertCount(3, $results);
        $this->assertEquals([$defect1->id, $defect2->id, $defect3->id], $results->pluck('id')->toArray());
    }

    /** @test */
    public function users_who_dont_have_permissions_can_see_only_related_defects()
    {
        // Given three defects
        $defect1 = factory(Defects::class)->create();
        $defect2 = factory(Defects::class)->create();
        $defect3 = factory(Defects::class)->create();

        // When we use Defect::filter()
        // as user that cannot
        $this->actingAs($this->user_that_cannot);
        $results = Defects::filter(request())->permissionCheck()->get();

        // Then ...
        // Result must contains nothing
        $this->assertEmpty($results);
    }

    /** @test */
    public function users_who_dont_have_permissions_can_see_only_related_defects_second_time()
    {
        // Given three defects
        // In one defect user is author, in another user is responsible
        $defect1 = factory(Defects::class)->create(['user_id' => $this->user_that_cannot]);
        $defect2 = factory(Defects::class)->create();
        $defect3 = factory(Defects::class)->create(['responsible_user_id' => $this->user_that_cannot]);

        // When we use Defect::filter()
        // as user that cannot
        $this->actingAs($this->user_that_cannot);
        $results = Defects::filter(request())->permissionCheck()->get();

        // Then ...
        // Result must contains two defects
        $this->assertCount(2, $results);
        $this->assertEquals([$defect1->id, $defect3->id], $results->pluck('id')->toArray());
    }
}
