<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates emails for users without them';

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
     *
     * @return mixed
     */
    public function handle()
    {
        DB::beginTransaction();
        $users_without_email = User::query()
            ->withoutGlobalScope('email')
            ->whereNull('email')
            ->get();

        foreach ($users_without_email as $user) {
            $email = Str::random(10).'@sk-resta.ru';
            while (User::where('email', $email)->exists()) {
                $email = Str::random(10).'@sk-resta.ru';
            }

            $user->email = $email;
            $user->save();
        }
        DB::commit();
        $this->info($users_without_email->count().' emails where generated');
    }
}
