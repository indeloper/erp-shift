<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Models\User;

class AllPagesTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware;

    /**
     * A basic test example.
     *
     * @return void
     */

    public function setUp() :void
    {
        parent::setUp();

        $user = User::first();
        $this->user = $user;
        $this->actingAs($user);
    }

    public function testTasksIndex()
    {
        $response = $this->get(route('contractors::index'));
        $response->assertStatus(200);
    }

    public function test_projects_index()
    {
        $response = $this->get(route('projects::index'));
        $response->assertStatus(200);
    }

    public function test_objects_index()
    {
        $response = $this->get(route('objects::index'));
        $response->assertStatus(200);
    }

    public function test_tasks_index()
    {
        $response = $this->get(route('contracts::index'));
        $response->assertStatus(200);
    }

    public function test_project_documents()
    {
        $response = $this->get(route('project_documents::index'));
        $response->assertStatus(200);
    }

    public function test_commercial_offers()
    {
        $response = $this->get(route('commercial_offers::index'));
        $response->assertStatus(200);
    }

    public function test_users_index()
    {
        $response = $this->get(route('users::index'));
        $response->assertStatus(200);
    }

//    public function test_document_templates()
//    {
//        $response = $this->get('/document_templates');
//        $response->assertStatus(200);
//    }

    public function test_notifications()
    {
        $response = $this->get(route('notifications::index'));
        $response->assertStatus(200);
    }
}
