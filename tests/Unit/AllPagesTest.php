<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class AllPagesTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware;

    /**
     * A basic test example.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $user = User::first();
        $this->user = $user;
        $this->actingAs($user);
    }

    public function testTasksIndex(): void
    {
        $response = $this->get(route('contractors::index'));
        $response->assertStatus(200);
    }

    public function test_projects_index(): void
    {
        $response = $this->get(route('projects::index'));
        $response->assertStatus(200);
    }

    public function test_objects_index(): void
    {
        $response = $this->get(route('objects::index'));
        $response->assertStatus(200);
    }

    public function test_tasks_index(): void
    {
        $response = $this->get(route('contracts::index'));
        $response->assertStatus(200);
    }

    public function test_project_documents(): void
    {
        $response = $this->get(route('project_documents::index'));
        $response->assertStatus(200);
    }

    public function test_commercial_offers(): void
    {
        $response = $this->get(route('commercial_offers::index'));
        $response->assertStatus(200);
    }

    public function test_users_index(): void
    {
        $response = $this->get(route('users::index'));
        $response->assertStatus(200);
    }

    //    public function test_document_templates()
    //    {
    //        $response = $this->get('/document_templates');
    //        $response->assertStatus(200);
    //    }

    public function test_notifications(): void
    {
        $response = $this->get(route('notifications::index'));
        $response->assertStatus(200);
    }
}
