<?php

namespace App\Http\Controllers\System;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use App\Events\MessageDeleted;
use App\Events\MessageStored;
use App\Events\MessageUpdated;
use App\Http\Controllers\Controller;
use App\Models\FileEntry;
use App\Models\Messenger\Message;
use App\Models\Messenger\MessageFile;
use App\Models\Messenger\MessageForwards;
use App\Models\Messenger\Participant;
use App\Models\Messenger\Thread;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class MessagesController extends Controller
{
    /**
     * Shows a message threads.
     */
    public function index(Request $request): View
    {
        // All threads that user is participating in
        $threads = Thread::forUser(Auth::id());
        $archived_threads = Thread::forUserOnlyTrashed(Auth::id());

        if ($request->has('search')) {
            $threads->where('subject', 'like', '%'.$request->search.'%')
                ->orWhereHas('users', function ($query) use ($request) {
                    $query->where('last_name', 'like', '%'.$request->search.'%')
                        ->orWhere('first_name', 'like', '%'.$request->search.'%')
                        ->orWhere('patronymic', 'like', '%'.$request->search.'%');
                });
        }

        return view('messages.index', [
            'threads' => $threads->distinct('id')->latest('updated_at')->paginate(20),
            'archived_threads' => $archived_threads->distinct('id')->latest('updated_at')->paginate(20),
        ]);
    }

    /**
     * Shows a message thread.
     */
    public function thread($id): View
    {
        $thread = Thread::findOrFail($id);

        // Abort if user not in participant list
        if (! $thread->hasParticipant(Auth::id())) {
            abort(403);
        }

        $userId = Auth::id();
        $users = User::whereNotIn('id', $thread->participantsUserIds($userId))->get();
        $thread->markAsRead($userId);

        return view('messages.thread',
            compact('thread', 'users')
        );
    }

    /**
     * Stores a new message thread.
     */
    public function thread_store(Request $request): RedirectResponse
    {
        DB::beginTransaction();

        // create Thread
        $thread = Thread::create([
            'subject' => $request->name,
            'creator_id' => Auth::id(),
        ]);

        // create Message
        $message = Message::create([
            'thread_id' => $thread->id,
            'user_id' => Auth::id(),
            'body' => $request->message,
        ]);

        // create Sender
        $sender = Participant::create([
            'thread_id' => $thread->id,
            'user_id' => Auth::id(),
            'last_read' => new Carbon,
        ]);

        // create Recipients
        if ($request->has('users_id')) {
            $thread->addParticipant($request->users_id);
        }

        DB::commit();

        // push message
        $this->oooPushIt($message);

        return redirect()->route('messages::index');
    }

    /**
     * Update thread name and participants.
     */
    public function thread_update(Request $request, $thread_id): RedirectResponse
    {
        DB::beginTransaction();

        // create Thread
        $thread = Thread::findOrFail($thread_id);
        $thread->update(['subject' => $request->name]);

        // update Recipients
        if ($request->has('users_id')) {
            $old_participants = $thread->participantsUserIds();
            // find removed users => remove them, find new users => add them
            $removed_users = array_diff($old_participants, $request->users_id);

            // remove excess users
            $thread->removeParticipant($removed_users);

            // add users
            $thread->addParticipant($request->users_id);
        }

        DB::commit();

        return redirect()->route('messages::index', ['thread' => $thread_id]);
    }

    /**
     * Adds a new message to a current thread.
     *
     * @return mixed
     */
    public function message_store(Request $request, $id)
    {
        DB::beginTransaction();

        $thread = Thread::findOrFail($id);

        // create new Message
        $message = Message::create([
            'thread_id' => $thread->id,
            'user_id' => Auth::id(),
            'body' => $request->get('message') ?? ' ',
            'has_relation' => $request->has('forward_messages_id') ?? 0,
        ]);

        // check for uploaded files and upload it
        if ($request->has('message_files')) {
            $this->upload($request->file('message_files'), $message);
        }

        if ($request->has('forward_messages_id')) {
            $this->createForwardRelation($request->forward_messages_id, $message);
        }

        //        // Add replier as a participant
        //        $participant = Participant::firstOrCreate([
        //            'thread_id' => $thread->id,
        //            'user_id' => Auth::id(),
        //        ]);
        //        $participant->last_read = new Carbon;
        //        $participant->save();
        $message->refresh();

        DB::commit();

        $html_for_sender = view('messages.partials.message', ['message' => $message])->render();
        $listItem = view('messages.partials.thread-list-item', ['thread' => $thread, 'noCount' => true])->render();

        // push message
        $this->oooPushIt($message);

        if (request()->ajax()) {
            return response()->json([
                'html' => $html_for_sender,
                'list_item' => $listItem,
                'thread' => $thread->id,
            ]);
        }

        return redirect()->route('messages::index', ['thread' => $id]);
    }

    /**
     * Send the new message to Pusher in order to notify users.
     */
    protected function oooPushIt(Message $message, Event $event = MessageStored::class): void
    {
        $thread = $message->thread;
        $sender = $message->user;
        $data = [
            'div_class' => 'thread_'.$thread->id,
            'message_id' => $message->id,
        ];
        $recipients = $thread->participantsUserIds();
        if (count($recipients) > 0) {
            foreach ($recipients as $recipient) {
                if ($recipient == $sender->id) {
                    continue;
                }

                // trigger event
                event(new $event($recipient, $data, User::find($recipient)->unreadMessagesCount()));
            }
        }
    }

    /**
     * Mark a specific thread as read, for ajax use.
     */
    public function read($id)
    {
        $thread = Thread::find($id);
        if (! $thread) {
            abort(404);
        }
        $thread->markAsRead(Auth::id());
    }

    /**
     * Get the number of unread threads, for ajax use.
     *
     * @return array
     */
    public function unread()
    {
        $count = Auth::user()->unreadMessagesCount();

        return ['msg_count' => $count];
    }

    /**
     * Remove user from thread, for ajax use.
     *
     * @param  $id
     * @return true
     */
    public function leave_thread(Request $request): JsonResponse
    {
        DB::beginTransaction();

        $thread = Thread::findOrFail($request->thread_id);

        // only participants can leave thread
        if (! $thread->hasParticipant(Auth::id())) {
            abort(403);
        }

        // remove Auth user from thread
        $thread->removeParticipant(Auth::id());

        DB::commit();

        return response()->json(true);
    }

    /**
     * Remove creator from thread
     * add new one.
     *
     * @param  $id
     * @return true
     */
    public function creator_leave(Request $request, $thread_id): RedirectResponse
    {
        DB::beginTransaction();

        $thread = Thread::findOrFail($thread_id);

        // update thread creator
        $thread->update(['creator_id' => $request->substitute_user_id]);

        // only participants can leave thread
        if (! $thread->hasParticipant(Auth::id())) {
            abort(403);
        }

        // remove Auth user from thread
        $thread->removeParticipant(Auth::id());

        DB::commit();

        return redirect()->route('messages::index');
    }

    /**
     * Return removed user in thread, for ajax use.
     *
     * @return true
     */
    public function join_thread(Request $request): JsonResponse
    {
        DB::beginTransaction();

        $thread = Thread::findOrFail($request->thread_id);

        // only trashed participants can join thread
        if (! $thread->hasTrashedParticipant(Auth::id())) {
            abort(403);
        }

        // add Auth user in thread
        $thread->addParticipant(Auth::id());

        DB::commit();

        return response()->json([
            'route' => route('messages::index', ['thread' => $thread->id]),
        ]);
    }

    /**
     * Update message in thread, for ajax use.
     */
    public function update_message(Request $request): JsonResponse
    {
        DB::beginTransaction();

        $message = Message::findOrFail($request->edited_message_id);

        // check for uploaded files and upload it
        if ($request->has('message_files')) {
            $this->upload($request->file('message_files'), $message, 'edit');
        }

        // update message
        $message->update(['body' => $request->message]);
        $message->refresh();

        $html_for_sender = view('messages.partials.message', ['message' => $message])->render();

        DB::commit();

        // push updated message
        $this->oooPushIt($message, MessageUpdated::class);

        return response()->json([
            'message' => $message->id,
            'html' => $html_for_sender,
        ]);
    }

    /**
     * Delete message from thread, for ajax use.
     *
     * @return message id
     */
    public function delete_message(Request $request): JsonResponse
    {
        DB::beginTransaction();

        foreach ($request->messages as $message) {
            $message = Message::findOrFail($message);

            // push deleted message
            $this->oooPushIt($message, MessageDeleted::class);

            $message->files->each(function ($message_file) {
                Storage::disk('message_files')->delete($message_file->file_name);
            });
            $message->files()->delete();
            $message->related_messages()->delete();
            $message->delete();
        }

        DB::commit();

        return response()->json($request->messages);
    }

    /**
     * Save files for message, for ajax use.
     *
     * @return void
     */
    public function upload(iterable $requestFiles, Message $message, string $type = 'regular')
    {
        $existed_files = $message->files->pluck('original_name')->toArray();

        foreach ($requestFiles as $requestFile) {
            $name = $requestFile->getClientOriginalName();
            // if we edit message, let's check existed files
            if ($type == 'edit' and in_array($name, $existed_files)) {
                continue;
            }

            $file = new MessageFile();

            $mime = $requestFile->getClientOriginalExtension();

            $file_name = 'message_'.$message->id.'-'.uniqid().'-file-'.uniqid().'.'.$mime;

            Storage::disk('message_files')->put($file_name, File::get($requestFile));

            FileEntry::create([
                'filename' => $file::FILE_PATH.$file_name,
                'size' => $requestFile->getSize(),
                'mime' => $mime,
                'original_filename' => $name,
                'user_id' => Auth::id(),
            ]);

            $file->file_name = $file_name;
            $file->original_name = $name;
            $file->path = $file::FILE_PATH;
            $file->message_id = $message->id;
            $file->type = in_array($mime, $file::PICTURES) ? 2 : 1;
            $file->user_id = Auth::id();

            $file->save();
        }
    }

    /**
     * Add forward relations for message, for ajax use.
     *
     * @return void
     */
    public function createForwardRelation(iterable $forward_message_ids, Message $message)
    {
        // add relations
        foreach ($forward_message_ids as $forward_message_id) {
            MessageForwards::create([
                'message_id' => $message->id,
                'forwarded_message_id' => $forward_message_id,
            ]);
        }
    }

    /**
     * Return message files list, for ajax use.
     */
    public function message_files(Request $request): JsonResponse
    {
        $message = Message::findOrFail($request->message_id);

        $rendered_file_template = view('messages.partials.filelist', ['message' => $message])->render();

        return response()->json([
            'status' => 'OK',
            'html' => $rendered_file_template,
            'message' => $message->id,
        ]);
    }

    /**
     * Delete file from message, for ajax use.
     */
    public function message_files_delete(Request $request): JsonResponse
    {
        DB::beginTransaction();

        $message_file = MessageFile::findOrFail($request->message_file_id);

        // remove file from server
        Storage::disk('message_files')->delete($message_file->file_name);
        $message = $message_file->message;
        $message_file->delete();
        $message->refresh();

        DB::commit();

        $html_for_sender = view('messages.partials.message', ['message' => $message])->render();

        // push updated message
        $this->oooPushIt($message, MessageUpdated::class);

        return response()->json([
            'html' => $html_for_sender,
            'message' => $message->id,
        ]);
    }

    /**
     * Render related message modal content, for ajax use.
     */
    public function related_messages(Request $request): JsonResponse
    {
        $message = Message::findOrFail($request->message_id);

        //        dd($message->related_messages[0]->forwarded_message->related_messages[0]->forwarded_message->related_messages[0]->forwarded_message);

        $html = view('messages.partials.related_messages', ['related_messages' => $message->related_messages])->render();

        return response()->json([
            'html' => $html,
        ]);
    }

    /**
     * Render message, for ajax use.
     */
    public function message_render(Request $request): JsonResponse
    {
        $message = Message::findOrFail($request->message_id);
        $html = view('messages.partials.message', ['message' => $message, 'noControl' => true])->render();
        $listItem = view('messages.partials.thread-list-item', ['thread' => $message->thread])->render();

        return response()->json([
            'html' => $html,
            'list_item' => $listItem,
            'thread' => $message->thread->id,
        ]);
    }

    /**
     * Return message info, for ajax use.
     */
    public function message_info(Request $request): JsonResponse
    {
        $message = Message::findOrFail($request->message_id);

        return response()->json([
            'sender_name' => $message->user->full_name,
            'thread_url' => route('messages::index', ['thread' => $message->thread->id]),
            'thread_subject' => $message->thread->subject,
            'text' => substr($message->body, 0, 50),
        ]);
    }

    /**
     * Send messages from one thread to other.
     *
     * @return mixed
     */
    public function send_messages(Request $request)
    {
        DB::beginTransaction();

        $thread = Thread::findOrFail($request->thread_id);

        // create new Message
        $message = Message::create([
            'thread_id' => $thread->id,
            'user_id' => Auth::id(),
            'body' => $request->get('message') ?? ' ',
            'has_relation' => $request->has('forward_messages_id') ?? 0,
        ]);

        // check for uploaded files and upload it
        if ($request->has('message_files')) {
            $this->upload($request->file('message_files'), $message);
        }

        if ($request->has('forward_messages_id')) {
            $this->createForwardRelation($request->forward_messages_id, $message);
        }

        //        // Add replier as a participant
        //        $participant = Participant::firstOrCreate([
        //            'thread_id' => $thread->id,
        //            'user_id' => Auth::id(),
        //        ]);
        //        $participant->last_read = new Carbon;
        //        $participant->save();

        DB::commit();

        // push message
        $this->oooPushIt($message);

        if (request()->ajax()) {
            return response()->json([
                'route' => route('messages::index', ['thread' => $request->thread_id]),
            ]);
        }

        return redirect()->route('messages::index', ['thread' => $request->thread_id]);
    }

    public function get_users(Request $request)
    {
        $users = User::getAllUsers()->where('status', 1);

        if ($request->q) {
            $users = $users->where(DB::raw('CONCAT(last_name, " ", first_name, " ", patronymic)'), 'like', '%'.$request->q.'%');
        }

        $users = $users->where('users.id', '!=', 1)->take(6)->get();

        $results = [];
        foreach ($users as $user) {
            $results[] = [
                'id' => $user->id,
                'text' => trim($user->last_name.' '.$user->first_name.' '.$user->patronymic),
            ];
        }

        return ['results' => $results];
    }

    public function load_thread(Request $request): JsonResponse
    {
        $thread = Thread::findOrFail($request->thread_id);
        $thread->markAsRead(Auth::id());
        $html_for_sender = view('messages.partials.thread', ['thread' => $thread])->render();

        return response()->json([
            'html' => $html_for_sender,
        ]);
    }

    public function thread_message_count(Request $request): JsonResponse
    {
        $message = Message::findOrFail($request->message_id);

        return response()->json([
            'messages_count' => $message->thread->userUnreadMessagesCount(Auth::id()),
        ]);
    }
}
