<?php

namespace Tests\Feature;

use App\Models\TechAcc\OurTechnicTicket;
use Tests\Feature\Tech_accounting\OurTechnicTicket\OurTechnicTicketTestCase;

class OurTechnicTicketCRUDTest extends OurTechnicTicketTestCase
{
    /** @test */
    public function it_can_store_ticket_and_give_it_type_three() //store
    {
        $request = $this->validFields();
        $this->post(route('building::tech_acc::our_technic_tickets.store'), $request);

        $this->assertEquals($request['our_technic_id'], OurTechnicTicket::first()->our_technic_id);
        $this->assertEquals(3, OurTechnicTicket::first()->type);
    }

    /** @test */
    public function it_gives_type_two_if_no_transfer_usage_was_provided() //store
    {
        $request = $this->validFields([
            'usage_from_date' => '',
            'usage_to_date' => '',
        ]);
        $this->post(route('building::tech_acc::our_technic_tickets.store'), $request)->assertSessionDoesntHaveErrors();

        $this->assertEquals(2, OurTechnicTicket::first()->type);
    }

    /** @test */
    public function it_gives_type_one_if_no_transfer_dates_was_provided()
    {
        $request = $this->validFields([
            'sending_from_date' => '',
            'sending_to_date' => '',
            'getting_to_date' => '',
            'getting_from_date' => '',
        ]);
        $this->post(route('building::tech_acc::our_technic_tickets.store'), $request);

        $this->assertEquals(1, OurTechnicTicket::first()->type);
    }

    /** @test */
    public function it_can_delete_specific_ticket() //destroy
    {
        $ticket = factory(OurTechnicTicket::class)->create();

        $this->delete(route('building::tech_acc::our_technic_tickets.destroy', $ticket->id))
            ->assertSessionDoesntHaveErrors();

        $this->assertNotContains($ticket->comment, OurTechnicTicket::all()->pluck('comment'));
    }

    /** @test */
    public function it_shows_all_tickets() //index
    {
        $this->seedTicketsWithUsers(13);

        $data = $this->get(route('building::tech_acc::our_technic_tickets.index'))->viewData('data');
        $this->assertCount(13, $data['tickets']);
    }
}
