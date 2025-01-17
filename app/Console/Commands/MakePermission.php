<?php

namespace App\Console\Commands;

use App\Models\Permission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class MakePermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:permission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'make permission';

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
            'codename' => 'max:255|unique:permissions',
            'name' => 'max:255|unique:permissions',
        ]);
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $request = [
            'codename' => $this->ask('What is codename?'),
            'name' => $this->ask('What is name (description)?'),
        ];

        if ($this->validate($request)) {
            $permission = new Permission();

            $permission->codename = $request['codename'];
            $permission->name = $request['name'];

            $permission->save();

            $this->info('permission saved');
        } else {
            $this->error('Something went wrong!');
        }
    }
}
