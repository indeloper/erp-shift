<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\FileEntry;
use App\Models\TechAcc\Defects\Defects;
use App\Models\TechAcc\OurTechnicTicket;
use App\Models\User;
use Tests\TestCase;

class CommentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::first());
    }

    /** @test */
    public function it_can_associate_ticket(): void
    {
        $ticket = OurTechnicTicket::factory()->create();

        $this->post(route('comments.store'), [
            'comment' => 'hi',
            'commentable_id' => $ticket->id,
            'commentable_type' => $ticket->class_name,
        ])->assertStatus(200);

        $this->assertEquals($ticket->id, Comment::latest()->first()->commentable->id);
        $this->assertEquals(get_class($ticket), get_class(Comment::latest()->first()->commentable));
    }

    /** @test */
    public function it_can_associate_defects(): void
    {
        $defect = Defects::factory()->create();

        $this->post(route('comments.store'), [
            'comment' => 'hi',
            'commentable_id' => $defect->id,
            'commentable_type' => $defect->class_name,
        ])->assertStatus(200);

        $this->assertEquals($defect->id, Comment::latest()->first()->commentable->id);
        $this->assertEquals(get_class($defect), get_class(Comment::latest()->first()->commentable));
    }

    /** @test */
    public function it_can_attach_files(): void
    {
        $files = FileEntry::factory()->count(5)->create();

        $this->post(route('comments.store'), [
            'comment' => $this->faker()->sentence,
            'file_ids' => $files->pluck('id'),
        ])->json('data.comment');

        $this->assertEquals(Comment::latest()->first()->documents->pluck('id'), $files->pluck('id'));
    }

    /** @test */
    public function it_deletes_files_with_comment(): void
    {
        $comment = Comment::factory()->create();
        $files = FileEntry::factory()->count(6)->create();
        $comment->documents()->saveMany($files);

        $this->delete(route('comments.destroy', $comment->id))->assertStatus(200);

        $this->assertCount(0, FileEntry::find($files->pluck('id')));
    }

    /** @test */
    public function it_can_update_comment(): void
    {
        $comment = Comment::factory()->create();
        $files = FileEntry::factory()->count(5)->create();
        $new_files = FileEntry::factory()->count(2)->create();
        $comment->files()->saveMany($files);

        $new_comment_text = $this->faker()->sentence;

        $updated_comment = $this->put(route('comments.update', $comment->id), [
            'comment' => $new_comment_text,
            'file_ids' => $new_files->pluck('id'),
            'deleted_file_ids' => $files->take(3)->pluck('id'),
        ])->assertStatus(200)->json('data.comment');

        $this->assertCount(5 - 3 + 2, Comment::latest()->first()->documents);
        $this->assertEquals($new_comment_text, $updated_comment['comment']);
    }
}
