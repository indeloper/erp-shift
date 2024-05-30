<?php

namespace Tests\Feature;

use App\Models\CommercialOffer\CommercialOffer;
use App\Models\Project;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProjectImportanceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Project::query()->delete();
    }

    /** @test */
    public function a_standard_project_is_not_important(): void
    {
        // Given a fresh standard project
        $project = Project::factory()->create();

        // Then it shouldn't be important
        $this->assertEquals(0, $project->is_important);
    }

    /** @test */
    public function a_project_can_be_important_after_creating(): void
    {
        // Given a fresh important project
        $project = Project::factory()->create(['is_important' => 1]);

        // Then it should be important
        $this->assertEquals(1, $project->is_important);
    }

    /** @test */
    public function a_standard_project_become_important_after_importance_toggling(): void
    {
        // Given a fresh standard project
        $project = Project::factory()->create();

        // When we call this function on unimportant project
        $project->importanceToggler();

        // Then project become important
        $this->assertEquals(1, $project->is_important);
    }

    /** @test */
    public function a_important_project_become_not_important_after_importance_toggling(): void
    {
        // Given a fresh important project
        $project = Project::factory()->create(['is_important' => 1]);

        // When we call this function on important project
        $project->importanceToggler();

        // Then project become unimportant
        $this->assertEquals(0, $project->is_important);
    }

    /** @test */
    public function a_important_projects_comes_first_not_important_after(): void
    {
        // Given a fresh unimportant project
        $project1 = Project::factory()->create();
        // And another unimportant one
        $project2 = Project::factory()->create();
        // And important one
        $project3 = Project::factory()->create(['is_important' => 1]);

        // When we use this scope (which include ordering based on importance)
        $projectCollection = Project::getAllProjects()->get();

        // Then first project from collection should be our third project
        $this->assertEquals($project3->id, $projectCollection->first()->id);
    }

    /** @test */
    public function a_important_projects_comes_first_not_important_after_one_more_time(): void
    {
        // Given a fresh unimportant project
        $project1 = Project::factory()->create();
        // And one important
        $project2 = Project::factory()->create(['is_important' => 1]);
        // And one unimportant
        $project3 = Project::factory()->create();
        // And one more important
        $project4 = Project::factory()->create(['is_important' => 1]);
        // And one more important
        $project5 = Project::factory()->create(['is_important' => 1]);

        // When we use this scope (which include ordering based on importance)
        $projectCollection = Project::getAllProjects()->get();

        // Then three first projects from collection should be important
        $this->assertEquals([1, 1, 1, 0, 0], $projectCollection->pluck('is_important')->toArray());
    }

    /** @test */
    public function a_important_project_become_not_important_after_one_and_only_CO_branch_move_to_agreed_with_customer_status(): void
    {
        // Given a fresh important project
        $project = Project::factory()->create(['is_important' => 1]);

        // Given a fresh commercial offer for our project
        $commercialOffer = CommercialOffer::factory()->create(['project_id' => $project->id]);

        // When all commercial offers branches move in Agreed With Customer status
        $commercialOffer->update(['status' => 4]);
        // When we refresh our project
        $project->refresh();

        // Then project should be unimportant
        $this->assertEquals(0, $project->is_important);
    }

    /** @test */
    public function a_important_project_become_not_important_after_first_CO_in_three_branches_move_to_agreed_with_customer_status(): void
    {
        // Given a fresh important project
        $project = Project::factory()->create(['is_important' => 1]);

        // Given a three commercial offers for our project
        $commercialOffer1 = CommercialOffer::factory()->create(['project_id' => $project->id, 'option' => 1]);
        $commercialOffer2 = CommercialOffer::factory()->create(['project_id' => $project->id, 'option' => 2]);
        $commercialOffer3 = CommercialOffer::factory()->create(['project_id' => $project->id, 'option' => 3]);

        // When all commercial offers branches move in Agreed With Customer status
        $commercialOffer1->update(['status' => 4]);
        $commercialOffer2->update(['status' => 4]);
        $commercialOffer3->update(['status' => 4]);
        // When we refresh our project
        $project->refresh();

        // Then project should be unimportant
        $this->assertEquals(0, $project->is_important);
    }

    /** @test */
    public function a_important_project_become_not_important_after_CO_in_three_branches_move_to_agreed_with_customer_status(): void
    {
        // Given a fresh important project
        $project = Project::factory()->create(['is_important' => 1]);

        // Given a four commercial offers for our project (one of them have second version)
        $commercialOffer1 = CommercialOffer::factory()->create(['project_id' => $project->id, 'status' => 3, 'option' => 1]);
        $commercialOffer1_1 = CommercialOffer::factory()->create(['project_id' => $project->id, 'version' => 2, 'option' => 1]);
        $commercialOffer2 = CommercialOffer::factory()->create(['project_id' => $project->id, 'option' => 2]);
        $commercialOffer3 = CommercialOffer::factory()->create(['project_id' => $project->id, 'option' => 3]);

        // When all commercial offers branches move in Agreed With Customer status
        $commercialOffer1_1->update(['status' => 4]);
        $commercialOffer2->update(['status' => 4]);
        $commercialOffer3->update(['status' => 4]);
        // When we refresh our project
        $project->refresh();

        // Then project should be unimportant
        $this->assertEquals(0, $project->is_important);
    }

    /** @test */
    public function project_importance_dont_change_after_one_CO_in_three_branches_is_not_agreed_and_others_are_in_agreed_with_customer_status(): void
    {
        // Given a fresh important project
        $project = Project::factory()->create(['is_important' => 1]);

        // Given a four commercial offers for our project (one of them have second version)
        $commercialOffer1 = CommercialOffer::factory()->create(['project_id' => $project->id, 'status' => 3, 'option' => 1]);
        $commercialOffer1_1 = CommercialOffer::factory()->create(['project_id' => $project->id, 'version' => 2, 'option' => 1]);
        $commercialOffer2 = CommercialOffer::factory()->create(['project_id' => $project->id, 'option' => 2]);
        $commercialOffer3 = CommercialOffer::factory()->create(['project_id' => $project->id, 'option' => 3]);

        // When part of commercial offers branches move in Agreed With Customer status
        $commercialOffer2->update(['status' => 4]);
        $commercialOffer3->update(['status' => 4]);
        // When we refresh our project
        $project->refresh();

        // Then project should stay important
        $this->assertEquals(1, $project->is_important);
    }

    /** @test */
    public function project_importance_influence_on_his_CO_in_nice_statuses(): void
    {
        // Given a fresh important project
        $project = Project::factory()->create(['is_important' => 1]);
        // Given a commercial offer for our project in status "Work"
        $commercialOffer = CommercialOffer::factory()->create(['project_id' => $project->id, 'option' => 1, 'status' => 1]);

        // Then commercial offer should be colored
        $this->assertEquals(1, $commercialOffer->isNeedToBeColored());
    }

    /** @test */
    public function project_importance_not_influence_on_his_CO_not_in_nice_statuses(): void
    {
        // Given a fresh important project
        $project = Project::factory()->create(['is_important' => 1]);
        // Given a commercial offer for our project in status "Agreed With Customer" (end of lifeline)
        $commercialOffer = CommercialOffer::factory()->create(['project_id' => $project->id, 'option' => 1, 'status' => 4]);

        // Then commercial offer shouldn't be colored
        $this->assertEquals(0, $commercialOffer->isNeedToBeColored());
    }
}
