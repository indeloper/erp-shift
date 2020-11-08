<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use App\Services\System\NotificationService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TgNotificationUrlTest extends TestCase
{
    /** @test */
    public function it_can_decode_url()
    {
        $this->withoutExceptionHandling();
        $url = 'http://sk/tasks';
        $notif = Notification::create();

        $service = new NotificationService();

        $encoded_url = $service->encodeNotificationUrl($notif->id, $url);

        $this->actingAs(User::first());

        $this->get($encoded_url)->assertRedirect($url);
        $this->assertEquals(1, Notification::find($notif->id)->is_seen);
    }

    /** @test */
    public function it_can_detect_and_encode_url_in_message()
    {
        $service = new NotificationService();
        $notif = Notification::create();
        $message = "Заявка на технику №96 согласована и ожидает назначения на рейс. Автор заявки: Бургутин Ю. А. \n Ссылка на заявку: https://sk.restpa.ru/building/tech_acc/our_technic_tickets?ticket_id=96";

        $new_message = $service->replaceUrl($message, $notif->id);

        dd($new_message);
    }
}
