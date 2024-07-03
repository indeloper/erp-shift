<?php

namespace App\Console\Commands;

use App\Models\Messenger\Thread;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MoveThreadsCreatorsToNewLogic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'threads:creators_update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix some crutches from message package creators';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        DB::beginTransaction();

        foreach (Thread::get() as $thread) {
            $oldCrutchLogicCreatorId = $thread->creator()->id;
            // fix
            $thread->update(['creator_id' => $oldCrutchLogicCreatorId]);
        }

        DB::commit();

        $this->info('All crutches fixed!');
    }
}
