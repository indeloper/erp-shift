<?php

namespace App\Console\Commands;

use App\Models\{Task, User};
use App\Models\Contractors\{Contractor, ContractorContact};
use Illuminate\Console\Command;

class MakeTestCall extends Command
{
    protected $signature = 'test:call';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make test call';

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
        $request = [
            'random_contact' => $this->confirm('Is contact phone number? [y|N]'),
        ];

        if (!$request['random_contact']) {
            $request = [
                'random_contractor' => $this->confirm('Is contractor phone number? [y|N]'),
            ];
        }

        $call = new Task();

        $call->name = 'Обработка входящего звонка';

        if (isset($request['random_contact'])) {
            $call->incoming_phone = ContractorContact::inRandomOrder()->where('phone_number', '!=', null)->first()->phone_number;
        }
        else {
            $call->incoming_phone =
                $request['random_contractor'] ? rand(79000000000, 79999999999)
                    : Contractor::inRandomOrder()->where('phone_number', '!=', null)->first()->phone_number;
        }

        $call->internal_phone = User::inRandomOrder()->first()->work_phone;
        $call->responsible_user_id = User::where('work_phone', $call->internal_phone)->first()->id;

        $call->expired_at = $this->addHours(2);

        $call->save();

        $this->info('test call created');
    }
}
