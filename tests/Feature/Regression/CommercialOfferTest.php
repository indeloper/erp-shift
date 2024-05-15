<?php

namespace Tests\Feature\Regression;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CommercialOfferTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function it_creates_uploaded_com_offer(): void
    {
        Storage::fake();
        $this->actingAs(User::find(27));
        $project = Project::first();
        $com_offer_count = $project->com_offers()->count();
        $request = [
            'is_tongue' => 0,
            'com_offer_id_tongue' => 'new',
            'com_offer_id_pile' => 'new',
            'option' => 'pup',
            'negotiation_type' => '1',
            'duplicate' => false,
            'commercial_offer' => UploadedFile::fake()->create('com_offer.pdf'),
        ];

        $response = $this->post(route('projects::commercial_offer::upload', $project->id), $request);

        $response->assertRedirect(route('projects::card', $project->id));
        $this->assertEquals(1, $project->com_offers()->count() - $com_offer_count);

        //P.S. there is more complex logic in upload_modal.blade
    }
}
