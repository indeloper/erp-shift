<?php


namespace Tests\Feature\Tech_accounting\OurTechnicTicket;

use App\Models\Department;
use App\Models\Group;
use App\Models\ProjectObject;
use App\Models\TechAcc\OurTechnic;
use App\Models\TechAcc\OurTechnicTicket;
use App\Models\TechAcc\Vehicles\OurVehicles;
use App\Models\User;
use App\Services\TechAccounting\TechnicTicketService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

abstract class OurTechnicTicketTestCase extends TestCase
{
    protected $warehouse_users;
    protected $rps;
    protected $rps_and_prorabs;
    protected $objects;
    protected $service;
    protected $logists;
    protected $logist;
    protected $prorabs;


    protected function setUp(): void
    {
        parent::setUp();

        OurTechnicTicket::query()->delete();

        $this->warehouse_users = Department::with('users')->find([12, 13])->pluck('users')->flatten();
        $this->rps = Group::with('users')->find([27, 13, 19])->pluck('users')->flatten();
        $this->prorabs = Group::with('users')->find([31, 14, 23])->pluck('users')->flatten();
        $this->rps_and_prorabs = Group::with('users')->find([27, 13, 19, 31, 14, 23])->pluck('users')->flatten();
        $this->logists = Group::with('users')->find(array_merge(Group::LOGIST, Group::MECHANICS))->pluck('users')->flatten();
        $this->logist = $this->logists->random();
        $this->objects = ProjectObject::query()->count() ? ProjectObject::all() : factory(ProjectObject::class, 10)->create();
        $this->service = new TechnicTicketService();

        $this->actingAs($this->rps->random());
        $this->withoutExceptionHandling();

    }


    /**
     * @param int $count
     * @param array $overrides
     * @param array $overrides_users
     * @return Collection
     */
    public function seedTicketsWithUsers($count = 1, $overrides = [], $overrides_users = [])
    {
        $type_map = (new User())->ticket_responsible_types;

        $users = array_merge([
            'resp_rp_user_id' => $this->rps->random()->id,
            'request_resp_user_id' => $this->rps_and_prorabs->random()->id,
            'recipient_user_id' => $this->warehouse_users->count() ? $this->warehouse_users->random()->id : $this->rps_and_prorabs->random()->id,
            'usage_resp_user_id' => $this->rps_and_prorabs->random()->id,
            'process_resp_user_id' => $this->logist->id,
            'author_user_id' => Auth::id(),
        ], $overrides_users);

        $tickets = factory(OurTechnicTicket::class, $count)->create($overrides)->each(function ($ticket) use ($users, $type_map) {
            foreach ($users as $type_name => $user_id) {
                $ticket->users()->attach($user_id, ['type' => array_search($type_name, $type_map)]);
            }
        });

        return $tickets;
    }

    protected function validFields ($overrides = [])
    {
        return array_merge([
            'our_technic_id' => factory(OurTechnic::class)->create()->id,
            'resp_rp_user_id' => $this->rps->random()->id,
            'ticket_resp_user_id' => $this->rps_and_prorabs->random()->id,
            'recipient_user_id' => $this->warehouse_users->count() ? $this->warehouse_users->random()->id : $this->rps_and_prorabs->random()->id,
            'usage_resp_user_id' => $this->rps_and_prorabs->random()->id,
            'process_resp_user_id' => $this->logist->id,
            'sending_object_id' => $this->objects->random()->id,
            'getting_object_id' => $this->objects->random()->id,
            'sending_from_date' => Carbon::now(),
            'sending_to_date' => Carbon::now()->addDays(2),
            'getting_from_date' => Carbon::now()->addDays(2),
            'getting_to_date' => Carbon::now()->addDays(5),
            'usage_from_date' => Carbon::now()->addDays(2),
            'usage_to_date' => Carbon::now()->addDays(5),
            'comment' => $this->faker->text(150),
            'vehicle_ids' => factory(OurVehicles::class, 2)->create()->pluck('id')->toArray(),
        ], $overrides);
    }
}
