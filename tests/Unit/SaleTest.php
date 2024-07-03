<?php

namespace Tests\Unit;

use App\Models\Contractors\Contractor;
use App\Models\Contractors\ContractorContact;
use App\Models\Project;
use App\Models\ProjectObject;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Session;
use Tests\TestCase;

class SaleTest extends TestCase
{
    public function test_true(): void
    {
        $this->assertTrue(true);
    }
    /**
     * A basic test example.
     *
     * @return void
     */

    //    public function setUp() :void
    //    {
    //        parent::setUp();
    //
    //        Session::start();
    //
    //        $this->actingAs(User::query()->first());
    //    }
    //
    //
    //    public function testPositiveCall()
    //    {
    //
    //        $response = $this->get('/tasks/make-test-call/3');
    //        $task_id = $response->original['data']['id'];
    //        $response = $this->get('/tasks/new_call/' . $task_id);
    //
    //        $this->contractor = factory(Contractor::class)->create();
    //        $this->contact = factory(ContractorContact::class)->create(['contractor_id' => $this->contractor->id]);
    //        $this->object = factory(ProjectObject::class)->create();
    //        $this->project = factory(Project::class)->create(['contractor_id' => $this->contractor, 'object_id' => $this->object]);
    //
    //        $response = $this->call('POST', '/tasks/close_call/' . $task_id , [
    //            'contractor_id' => $this->contractor->id,
    //            'contact_id' => $this->contact->id,
    //            'project_id' => $this->project->id,
    //            'final_note' => 'Положительный тестовый звонок',
    //            'status_result' => '1',
    //
    //            'contractor_full_name' => $this->contractor->full_name,
    //            'contractor_short_name' => $this->contractor->short_name,
    //            'contractor_inn' => $this->contractor->inn . '',
    //            'contractor_legal_address' => $this->contractor->legal_address,
    //            '_token' => csrf_token(),
    //        ]);
    //
    //        $response->assertRedirect('/tasks');
    //    }
    //
    //
    //    public function testQuestionnaireList()
    //    {
    //        $task = Task::orderBy('id', 'desc')->first();
    //
    //        $response = $this->call('POST', 'tasks/questionnaire/' . $task->questionnaire_token . '/store' , [
    //            'contact_name' => 'Иванов Тест',
    //            'contact_number' => '89837464748',
    //            'contact_email' => 'test@test.test',
    //            'is_soil_leader' => 1,
    //            'comment' => 'Тестовый опросный лист',
    //            '_token' => csrf_token(),
    //        ]);
    //
    //        $response->assertSee('Данные сохранены');
    //    }
    //
    //
    //    public function testPreOfferCreate()
    //    {
    //        $task = Task::orderBy('id', 'desc')->first();
    //
    //        $response = $this->call('POST', route('tasks::pre_offer::store_document', $task->id) , [
    //            'name' => 'Предварительное комерческое предложение',
    //            'document' => UploadedFile::fake()->create('pre_offer_test.pdf', 2000),
    //            '_token' => csrf_token(),
    //        ]);
    //
    //        $response = $this->call('POST', route('tasks::pre_offer::agree_pre_offer', $task->id) , [
    //            '_token' => csrf_token(),
    //        ]);
    //
    //        $response->assertRedirect('/tasks');
    //    }
    //
    //
    //    public function testPreOfferConfirm()
    //    {
    //        $task = Task::orderBy('id', 'desc')->first();
    //
    //        $response = $this->call('POST', route('tasks::pre_offer_confirm::store', $task->id) , [
    //            'final_note' => 'Тестовое согласование предварительного КП',
    //            'result' => 1,
    //            '_token' => csrf_token(),
    //        ]);
    //
    //        $response->assertRedirect('/tasks');
    //    }
    //
    //
    //    public function testValuation()
    //    {
    //        $task = Task::orderBy('id', 'desc')->first();
    //
    //        $response = $this->call('POST', route('tasks::valuation::store', $task->id) , [
    //            'final_note' => 'Тестовая потребность в оценке',
    //            'responsible_user_id' => User::where('group_id', 5)->first()->id,
    //            'expired_at' => Carbon::now()->addDays(1),
    //            '_token' => csrf_token(),
    //        ]);
    //
    //        $response->assertRedirect('/tasks');
    //    }
    //
    //
    //    public function testDeparture()
    //    {
    //        $task = Task::orderBy('id', 'desc')->first();
    //
    //        $response = $this->call('POST', route('tasks::departure::store', $task->id) , [
    //            'final_note' => 'Тестовый выезд на объект',
    //            'documents' => [UploadedFile::fake()->create('test_departure_1.pdf', 2000), UploadedFile::fake()->create('test_departure_2.pdf', 2000)],
    //            'status_result' => 2,
    //            '_token' => csrf_token(),
    //        ]);
    //
    //        $response->assertRedirect('/tasks');
    //    }
    //
    //
    //    public function testNeedOptimize()
    //    {
    //        $task = Task::orderBy('id', 'desc')->first();
    //
    //        $response = $this->call('POST', route('tasks::need_optimize::store', $task->id) , [
    //            'final_note' => 'Тестовая оптимизация',
    //            'responsible_user_id' => User::where('group_id', 17)->first()->id,
    //            'status_result' => 1,
    //            'expired_at' => Carbon::now()->addDays(2),
    //            '_token' => csrf_token(),
    //        ]);
    //
    //        $response->assertRedirect('/tasks');
    //    }
    //
    //    public function testOptimize()
    //    {
    //        $task = Task::orderBy('id', 'desc')->first();
    //
    //        $response = $this->call('POST', route('tasks::optimize::store', $task->id) , [
    //            'final_note' => 'Тестовая оптимизация',
    //            'documents' => [UploadedFile::fake()->create('test_optimize_1.pdf', 2000), UploadedFile::fake()->create('test_optimize_2.pdf', 2000)],
    //            '_token' => csrf_token(),
    //        ]);
    //
    //        $response->assertRedirect('/tasks');
    //    }
    //
    //
    //    public function testNeedCalc()
    //    {
    //        $task = Task::orderBy('id', 'desc')->first();
    //
    //        $response = $this->call('POST', route('tasks::need_calc::store', $task->id) , [
    //            'final_note' => 'Тестовая потребность в рассчете',
    //            'responsible_user_id' => User::where('group_id', 9)->first()->id,
    //            'expired_at' => Carbon::now()->addDays(2),
    //            '_token' => csrf_token(),
    //        ]);
    //
    //        $response->assertRedirect('/tasks');
    //    }
    //
    //
    //    public function testCalc()
    //    {
    //        $task = Task::orderBy('id', 'desc')->first();
    //
    //        $response = $this->call('POST', route('tasks::calc::store', $task->id) , [
    //            'final_note' => 'Тестовый рассчет',
    //            'documents' => [UploadedFile::fake()->create('test_calc_1.pdf', 2000), UploadedFile::fake()->create('test_calc_2.pdf', 2000)],
    //            '_token' => csrf_token(),
    //        ]);
    //
    //        $response->assertRedirect('/tasks');
    //    }
    //
    //
    //    public function testOffer()
    //    {
    //        $task = Task::orderBy('id', 'desc')->first();
    //
    //        $response = $this->call('POST', route('tasks::offer::store_document', $task->id) , [
    //            'name' => 'Предварительное комерческое предложение',
    //            'document' => UploadedFile::fake()->create('pre_offer_test.pdf', 2000),
    //            '_token' => csrf_token(),
    //        ]);
    //
    //        $response = $this->call('POST', route('tasks::offer::agree_pre_offer', $task->id) , [
    //            '_token' => csrf_token(),
    //        ]);
    //
    //        $response->assertRedirect('/tasks');
    //    }
    //
    //
    //    public function testOfferConfirm()
    //    {
    //        $task = Task::orderBy('id', 'desc')->first();
    //
    //        $response = $this->call('POST', route('tasks::offer_confirm::store', $task->id) , [
    //            'result_note' => 'Тестовая потребность в рассчете',
    //            'status_result' => 1,
    //            '_token' => csrf_token(),
    //        ]);
    //
    //        $response->assertRedirect('/tasks');
    //    }
    //
    //
    //    public function testNegativeCallWithProject()
    //    {
    //        $response = $this->get('/tasks/make-test-call/3');
    //        $task_id = $response->original['data']['id'];
    //        $response = $this->get('/tasks/new_call/' . $task_id);
    //
    //        $this->contractor = factory(Contractor::class)->create();
    //        $this->object = factory(ProjectObject::class)->create();
    //        $this->project = factory(Project::class)->create(['contractor_id' => $this->contractor, 'object_id' => $this->object]);
    //
    //        $response = $this->call('POST', '/tasks/close_call/' . $task_id , [
    //            'contractor_id' => $this->contractor->id,
    //            'project_id' => $this->project->id,
    //            'final_note' => 'Отрицательный тестовый звонок c проектом',
    //            'status_result' => '2',
    //
    //            'contractor_full_name' => $this->contractor->full_name,
    //            'contractor_short_name' => $this->contractor->short_name,
    //            'contractor_inn' => $this->contractor->inn . '',
    //            'contractor_legal_address' => $this->contractor->legal_address,
    //            '_token' => csrf_token(),
    //        ]);
    //
    //        $response->assertRedirect('/tasks');
    //    }
    //
    //
    //    public function testNegativeCallWithoutProject()
    //    {
    //        $response = $this->get('/tasks/make-test-call/3');
    //        $task_id = $response->original['data']['id'];
    //        $response = $this->get('/tasks/new_call/' . $task_id);
    //
    //        $response = $this->call('POST', '/tasks/close_call/' . $task_id , [
    //            'final_note' => 'Отрицательный тестовый звонок без проекта',
    //            'status_result' => '2',
    //            '_token' => csrf_token(),
    //        ]);
    //
    //        $response->assertRedirect('/tasks');
    //    }
    //
    //    public function testSecondaryCall()
    //    {
    //        $response = $this->get('/tasks/make-test-call/3');
    //        $task_id = $response->original['data']['id'];
    //        $response = $this->get('/tasks/new_call/' . $task_id);
    //
    //        $this->contractor = factory(Contractor::class)->create();
    //        $this->contact = factory(ContractorContact::class)->create(['contractor_id' => $this->contractor->id]);
    //        $this->object = factory(ProjectObject::class)->create();
    //        $this->project = factory(Project::class)->create(['contractor_id' => $this->contractor, 'object_id' => $this->object]);
    //
    //        $response = $this->call('POST', '/tasks/close_call/' . $task_id , [
    //            'contractor_id' => $this->contractor->id,
    //            'contact_id' => $this->contact->id,
    //            'project_id' => $this->project->id,
    //            'final_note' => 'Вторичный тестовый звонок',
    //            'status_result' => '3',
    //
    //            'contractor_full_name' => $this->contractor->full_name,
    //            'contractor_short_name' => $this->contractor->short_name,
    //            'contractor_inn' => $this->contractor->inn . '',
    //            'contractor_legal_address' => $this->contractor->legal_address,
    //            '_token' => csrf_token(),
    //        ]);
    //
    //        $response->assertRedirect('/tasks');
    //    }
}
