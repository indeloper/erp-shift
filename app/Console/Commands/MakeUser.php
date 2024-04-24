<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class MakeUser extends Command
{

    protected $signature = 'make:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add user';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function validate($request)
    {
        return Validator::make($request, [
            'email' => 'email|max:255|unique:users',
            'password' => 'min:6',
        ]);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $request = [
            'email' => $this->ask('What is email?'),
            'password' => $this->secret('What is the password?'),
            'is_su' => $this->confirm('Is super user? [y|N]'),
        ];

        if ($this->validate($request)){
            $user = new User();

            $user->email = $request['email'];
            $user->password = bcrypt($request['password']);
            $user->is_su = $request['is_su'];

            $user->save();

            $this->info('user saved');
        }else{
            $this->error('Something went wrong!');
        }

    }
}
