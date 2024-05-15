<?php

namespace Tests\Feature;

use App\Models\FileEntry;
use App\Models\ProjectObject;
use App\Models\TechAcc\CategoryCharacteristic;
use App\Models\TechAcc\Defects\Defects;
use App\Models\TechAcc\OurTechnic;
use App\Models\TechAcc\OurTechnicTicket;
use App\Models\TechAcc\TechnicCategory;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OurTechnicTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected $defects_migration;

    protected function setUp(): void
    {
        parent::setUp();

        $this->defects_migration = '2019_12_11_121059_create_defects_table';
        $this->runMigrationIfItWasntMigrated();
    }

    public function runMigrationIfItWasntMigrated(): void
    {
        $is_defects_migration_runned = DB::table('migrations')
            ->where('migration', $this->defects_migration)
            ->exists();

        $isRun = boolval($is_defects_migration_runned);

        if (! $isRun) {
            Artisan::call('migrate');
        }
    }

    /** @test */
    public function it_relates_to_a_category()
    {
        $technic_category = TechnicCategory::factory()->create();

        $technic = OurTechnic::factory()->create(['technic_category_id' => $technic_category->id]);

        $this->assertEquals($technic_category->name, $technic->category->name);
    }

    /** @test */
    public function it_can_have_a_characteristic()
    {
        $category = TechnicCategory::factory()->create();
        $category_characteristics = CategoryCharacteristic::factory()->count(2)->create();

        $category->addCharacteristic($category_characteristics->pluck('id'));

        $technic = OurTechnic::factory()->create(['technic_category_id' => $category->id]);

        $characteristics_data = [
            [
                'id' => $category_characteristics->first()->id,
                'value' => 60,
            ],
        ];
        $technic->setCharacteristicsValue($characteristics_data);

        $this->assertEquals(60, $technic->category_characteristics->first()->data->value);
    }

    /** @test */
    public function it_can_have_multiple_characteristics()
    {
        $category = TechnicCategory::factory()->create();
        $category_characteristics = CategoryCharacteristic::factory()->count(2)->create();

        $category->addCharacteristic($category_characteristics->pluck('id'));

        $technic = OurTechnic::factory()->create(['technic_category_id' => $category->id]);

        $characteristics_data = [
            [
                'id' => $category_characteristics->first()->id,
                'value' => '80',
            ],
            [
                'id' => $category_characteristics->last()->id,
                'value' => 'fast',
            ],
        ];

        $technic->setCharacteristicsValue($characteristics_data);

        $this->assertEquals(80, $technic->category_characteristics->first()->data->value);
    }

    /** @test */
    public function it_can_get_start_location_object()
    {
        $object = ProjectObject::factory()->create();
        $technic = OurTechnic::factory()->create(['start_location_id' => $object->id]);

        $this->assertEquals($object->name, $technic->start_location->name);
    }

    /** @test */
    public function it_can_have_documents()
    {
        $technic = OurTechnic::factory()->create();

        $file = FileEntry::create([
            'filename' => $this->faker()->sentence(),
            'size' => $this->faker()->randomNumber(4),
            'mime' => $this->faker()->mimeType,
            'original_filename' => $this->faker()->words(3, true),
            'user_id' => 1,
        ]);

        $technic->documents()->save($file);

        $this->assertCount(1, $technic->documents);
    }

    /** @test */
    public function it_can_have_defects()
    {
        // Given technic
        $technic = OurTechnic::factory()->create();
        // And two defects
        Defects::factory()->count(2)->create(['defectable_id' => $technic->id]);

        // When we refresh technic
        $technic->refresh();

        // Then technic should have defects relation with count equal to 2
        $this->assertCount(2, $technic->defects);
    }

    /** @test */
    public function it_has_tickets()
    {
        $technic = OurTechnic::factory()->create();
        OurTechnicTicket::factory()->create(['our_technic_id' => $technic]);

        $this->assertNotEmpty($technic->tickets);
    }

    /** @test */
    public function it_is_vacated_when_it_has_tickets()
    {
        $technic = OurTechnic::factory()->create();
        OurTechnicTicket::factory()->create(['our_technic_id' => $technic]);

        $another_technic = OurTechnic::factory()->create();
        OurTechnicTicket::factory()->create([
            'our_technic_id' => $technic,
            'status' => 3,
        ]);

        $this->assertTrue($technic->isVacated());
        $this->assertFalse($another_technic->isVacated());
    }

    /** @test */
    public function it_can_return_only_free_technic()
    {
        // Get rid out of other technics
        OurTechnic::query()->delete();
        $technics = OurTechnic::factory()->count(5)->create();
        OurTechnicTicket::factory()->create([
            'our_technic_id' => $technics->first()->id,
        ]);

        $technics->first()->refresh();

        $this->assertCount(4, OurTechnic::free()->get());
    }

    /** @test */
    public function it_shows_date_of_release()
    {
        $tech_in_use = OurTechnic::factory()->create();
        $ticket = OurTechnicTicket::factory()->create(['our_technic_id' => $tech_in_use]);

        $defected_tech = OurTechnic::factory()->create();
        Defects::factory()->create(['repair_end_date' => Carbon::now()->addDays(4)]);
        $defect = Defects::factory()->create(['repair_end_date' => Carbon::now()->addWeek()]);
        Defects::factory()->create(['repair_end_date' => Carbon::now()->addDays(2)]);
        $defected_tech->defects()->save($defect);

        $this->assertEquals($defect->repair_end_date->isoFormat('DD.MM.YYYY'), $defected_tech->release_date);
        $this->assertEquals($ticket->usage_to_date->isoFormat('DD.MM.YYYY'), $tech_in_use->release_date);
    }

    /** @test */
    public function if_tech_have_defect_date_more_that_ticket_usage_date_then_release_date_attr_shows_date_of_defect_release_not_usage()
    {
        // Given technic
        $tech = OurTechnic::factory()->create();
        // Given ticket for technic for one day
        $ticket = OurTechnicTicket::factory()->create(['our_technic_id' => $tech, 'usage_to_date' => now()->addDay()]);
        // Given defect for technic for one month
        $defect = Defects::factory()->create(['repair_end_date' => now()->addMonth()]);
        $tech->defects()->save($defect);

        // Then
        // Human status attribute must be 'Ремонт'
        $this->assertEquals('Ремонт', $tech->refresh()->human_status);
        // And technic release date must be equal to defect repair end date
        $this->assertEquals($defect->repair_end_date->isoFormat('DD.MM.YYYY'), $tech->release_date);
    }

    /** @test */
    public function if_tech_have_defect_date_less_that_ticket_usage_date_then_release_date_attr_shows_date_of_usage_release_not_defect()
    {
        // Given technic
        $tech = OurTechnic::factory()->create();
        // Given ticket for technic for one month
        $ticket = OurTechnicTicket::factory()->create(['our_technic_id' => $tech, 'usage_to_date' => now()->addMonth()]);
        // Given defect for technic
        $defect = Defects::factory()->create(['repair_end_date' => now()]);
        $tech->defects()->save($defect);

        // Then
        // Human status attribute must be 'Ремонт'
        $this->assertEquals('Ремонт', $tech->refresh()->human_status);
        // And technic release date must be equal to ticket usage end date
        $this->assertEquals($ticket->usage_to_date->isoFormat('DD.MM.YYYY'), $tech->release_date);
    }

    /** @test */
    public function if_tech_dont_have_defect_date_but_have_usage_then_release_date_attr_shows_date_of_usage_release()
    {
        // Given technic
        $tech = OurTechnic::factory()->create();
        // Given ticket for technic for one day
        $ticket = OurTechnicTicket::factory()->create(['our_technic_id' => $tech, 'usage_to_date' => now()->addMonth()]);

        // Then
        // Human status attribute must be 'В работе'
        $this->assertEquals('В работе', $tech->refresh()->human_status);
        // And technic release date must be equal to ticket usage end date
        $this->assertEquals($ticket->usage_to_date->isoFormat('DD.MM.YYYY'), $tech->release_date);
    }

    /** @test */
    public function if_tech_dont_have_usage_date_but_have_defect_then_release_date_attr_shows_date_of_defect_release()
    {
        // Given technic
        $tech = OurTechnic::factory()->create();
        // Given defect for technic
        $defect = Defects::factory()->create(['repair_end_date' => now()]);
        $tech->defects()->save($defect);

        // Then
        // Human status attribute must be 'Ремонт'
        $this->assertEquals('Ремонт', $tech->refresh()->human_status);
        // And technic release date must be equal to ticket usage end date
        $this->assertEquals($defect->repair_end_date->isoFormat('DD.MM.YYYY'), $tech->release_date);
    }
}
