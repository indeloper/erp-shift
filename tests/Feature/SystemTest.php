<?php

namespace Tests\Feature;

use App\Models\FileEntry;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SystemTest extends TestCase
{
    /** @test */
    public function it_can_store_files_in_technic_storage(): void
    {
        Storage::fake('technics');

        $ivan = User::first();
        $this->actingAs($ivan);

        $original_filename = $this->faker()->words(3, true);
        $file = UploadedFile::fake()->image($original_filename.'.jpg');

        $response = $this->post(route('file_entry.store'), [
            'file' => $file,
        ])->assertSee('success')->json();

        $file_in_db = FileEntry::find($response['data']);

        $this->assertCount(1, $file_in_db);
        Storage::disk('technics')->assertExists($file_in_db->last()->filename);
    }

    /** @test */
    public function it_can_delete_files_from_technic_storage(): void
    {
        Storage::fake('technics');

        $ivan = User::first();

        $this->actingAs($ivan);

        $file = FileEntry::factory()->create();

        $this->delete(route('file_entry.destroy', $file->id));

        $this->assertDatabaseMissing('file_entries', ['filename' => $file->filename]);

    }

    /** @test */
    public function it_can_store_files_in_vehicles_storage(): void
    {
        Storage::fake('vehicles');

        $ivan = User::first();
        $this->actingAs($ivan);

        $original_filename = $this->faker()->words(3, true);
        $file = UploadedFile::fake()->image($original_filename.'.jpg');

        $response = $this->post(route('file_entry.store'), [
            'file' => $file,
        ])->assertSee('success')->json();

        $file_in_db = FileEntry::find($response['data']);

        $this->assertCount(1, $file_in_db);
        Storage::disk('technics')->assertExists($file_in_db->last()->filename);
    }

    /** @test */
    public function it_can_delete_files_from_vehicles_storage(): void
    {
        Storage::fake('vehicles');

        $ivan = User::first();

        $this->actingAs($ivan);

        $file = FileEntry::factory()->create();

        $this->delete(route('file_entry.destroy', $file->id));

        $this->assertDatabaseMissing('file_entries', ['filename' => $file->filename]);
    }

    /** @test */
    public function it_can_store_mp4_files_in_technic_storage(): void
    {
        Storage::fake('technics');

        $ivan = User::first();
        $this->actingAs($ivan);

        $original_filename = $this->faker()->words(3, true);
        $file = UploadedFile::fake()->create('video_test.mp4');

        $response = $this->post(route('file_entry.store'), [
            'file' => $file,
        ])->assertSee('success')->json();

        $file_in_db = FileEntry::find($response['data']);

        $this->assertCount(1, $file_in_db);
        Storage::disk('technics')->assertExists($file_in_db->last()->filename);
    }

    /** @test */
    public function it_triggers_artisan_command(): void
    {
        $this->withoutExceptionHandling();
        $this->actingAs(User::find(1));
        $dates = [
            'start_date' => $this->faker()->date('d.m.Y'),
            'start_time' => $this->faker()->time('H:i'),
            'finish_date' => $this->faker()->date('d.m.Y'),
            'finish_time' => $this->faker()->time('H:i'),
        ];

        extract($dates);
        Artisan::shouldReceive('call')
            ->once()
            ->with("send:notify {$start_date} {$start_time} {$finish_date} {$finish_time}");

        $this->post(route('admin.send_tech_update_notify'), $dates)->assertRedirect();
    }
}
