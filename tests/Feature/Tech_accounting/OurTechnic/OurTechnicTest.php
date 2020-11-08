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
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class OurTechnicTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected $defects_migration;

    public function setUp(): void
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

        if (!$isRun) {
            Artisan::call("migrate");
        }
    }

    /** @test */
    public function it_relates_to_a_category()
    {
        $technic_category = factory(TechnicCategory::class)->create();

        $technic = factory(OurTechnic::class)->create(['technic_category_id' => $technic_category->id]);

        $this->assertEquals($technic_category->name, $technic->category->name);
    }

    /** @test */
    public function it_can_have_a_characteristic()
    {
        $category = factory(TechnicCategory::class)->create();
        $category_characteristics = factory(CategoryCharacteristic::class, 2)->create();

        $category->addCharacteristic($category_characteristics->pluck('id'));

        $technic = factory(OurTechnic::class)->create(['technic_category_id' => $category->id]);

        $characteristics_data = [
            [
                'id' => $category_characteristics->first()->id,
                'value' => 60
            ],
        ];
        $technic->setCharacteristicsValue($characteristics_data);

        $this->assertEquals(60, $technic->category_characteristics->first()->data->value);
    }

    /** @test */
    public function it_can_have_multiple_characteristics()
    {
        $category = factory(TechnicCategory::class)->create();
        $category_characteristics = factory(CategoryCharacteristic::class, 2)->create();

        $category->addCharacteristic($category_characteristics->pluck('id'));

        $technic = factory(OurTechnic::class)->create(['technic_category_id' => $category->id]);

        $characteristics_data = [
                [
                    'id' => $category_characteristics->first()->id,
                    'value' => '80'
                ],
                [
                    'id' => $category_characteristics->last()->id,
                    'value' => 'fast'
                ],
        ];

        $technic->setCharacteristicsValue($characteristics_data);

        $this->assertEquals(80, $technic->category_characteristics->first()->data->value);
    }

    /** @test */
    public function it_can_get_start_location_object()
    {
        $object = factory(ProjectObject::class)->create();
        $technic = factory(OurTechnic::class)->create(['start_location_id' => $object->id]);

        $this->assertEquals($object->name, $technic->start_location->name);
    }

    /** @test */
    public function it_can_have_documents()
    {
        $technic = factory(OurTechnic::class)->create();

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
        $technic = factory(OurTechnic::class)->create();
        // And two defects
        factory(Defects::class, 2)->create(['defectable_id' => $technic->id]);

        // When we refresh technic
        $technic->refresh();

        // Then technic should have defects relation with count equal to 2
        $this->assertCount(2, $technic->defects);
    }

    /** @test */
    public function it_has_tickets()
    {
        $technic = factory(OurTechnic::class)->create();
        factory(OurTechnicTicket::class)->create(['our_technic_id' => $technic]);

        $this->assertNotEmpty($technic->tickets);
    }


    /** @test */
    public function it_is_vacated_when_it_has_tickets()
    {
        $technic = factory(OurTechnic::class)->create();
        factory(OurTechnicTicket::class)->create(['our_technic_id' => $technic]);

        $another_technic = factory(OurTechnic::class)->create();
        factory(OurTechnicTicket::class)->create([
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
        $technics = factory(OurTechnic::class, 5)->create();
        factory(OurTechnicTicket::class)->create([
            'our_technic_id' => $technics->first()->id
        ]);

        $technics->first()->refresh();

        $this->assertCount(4, OurTechnic::free()->get());
    }

    /** @test */
    public function it_shows_date_of_release()
    {
        $tech_in_use = factory(OurTechnic::class)->create();
        $ticket = factory(OurTechnicTicket::class)->create(['our_technic_id' => $tech_in_use]);

        $defected_tech = factory(OurTechnic::class)->create();
        factory(Defects::class)->create(['repair_end_date' => Carbon::now()->addDays(4)]);
        $defect = factory(Defects::class)->create(['repair_end_date' => Carbon::now()->addWeek()]);
        factory(Defects::class)->create(['repair_end_date' => Carbon::now()->addDays(2)]);
        $defected_tech->defects()->save($defect);

        $this->assertEquals($defect->repair_end_date->isoFormat('DD.MM.YYYY'), $defected_tech->release_date);
        $this->assertEquals($ticket->usage_to_date->isoFormat('DD.MM.YYYY'), $tech_in_use->release_date);
    }

    /** @test */
    public function if_tech_have_defect_date_more_that_ticket_usage_date_then_release_date_attr_shows_date_of_defect_release_not_usage()
    {
        // Given technic
        $tech = factory(OurTechnic::class)->create();
        // Given ticket for technic for one day
        $ticket = factory(OurTechnicTicket::class)->create(['our_technic_id' => $tech, 'usage_to_date' => now()->addDay()]);
        // Given defect for technic for one month
        $defect = factory(Defects::class)->create(['repair_end_date' => now()->addMonth()]);
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
        $tech = factory(OurTechnic::class)->create();
        // Given ticket for technic for one month
        $ticket = factory(OurTechnicTicket::class)->create(['our_technic_id' => $tech, 'usage_to_date' => now()->addMonth()]);
        // Given defect for technic
        $defect = factory(Defects::class)->create(['repair_end_date' => now()]);
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
        $tech = factory(OurTechnic::class)->create();
        // Given ticket for technic for one day
        $ticket = factory(OurTechnicTicket::class)->create(['our_technic_id' => $tech, 'usage_to_date' => now()->addMonth()]);

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
        $tech = factory(OurTechnic::class)->create();
        // Given defect for technic
        $defect = factory(Defects::class)->create(['repair_end_date' => now()]);
        $tech->defects()->save($defect);

        // Then
        // Human status attribute must be 'Ремонт'
        $this->assertEquals('Ремонт', $tech->refresh()->human_status);
        // And technic release date must be equal to ticket usage end date
        $this->assertEquals($defect->repair_end_date->isoFormat('DD.MM.YYYY'), $tech->release_date);
    }
}
