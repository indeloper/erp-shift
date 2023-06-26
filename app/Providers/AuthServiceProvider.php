<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Project;
use App\Models\TechAcc\OurTechnicTicket;
use App\Models\Permission;
use App\Models\TechAcc\OurTechnicTicketReport;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankOperation;
use Laravel\Passport\Passport;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use App\Policies\UserPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\OurTechnicTicketActionsPolicy;
use App\Policies\OurTechnicTicketReportPolicy;
use App\Policies\FuelTankPolicy;
use App\Policies\FuelTankOperationPolicy;
use Illuminate\Http\Request;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Project::class => ProjectPolicy::class,
        OurTechnicTicket::class => OurTechnicTicketActionsPolicy::class,
        OurTechnicTicketReport::class => OurTechnicTicketReportPolicy::class,
        FuelTank::class => FuelTankPolicy::class,
        FuelTankOperation::class => FuelTankOperationPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();

        //allow everything for super admin
        Gate::before(function ($user, $ability, $arguments) {
            if ($user->is_su) {
                return true;
            }
            if (isset($arguments[0])) {
                $short_name = (new \ReflectionClass($arguments[0]))->getShortName();
                $permission = $ability . '.' . $short_name;
                $is_authed = $user->hasPermission($permission);
                if ($is_authed) {
                    return true;
                }
            }
        });

        // load check user permission
        foreach (Permission::all() AS $permission) {
            $ability = $permission->codename;
            Gate::define($ability, function($user) use ($ability) {
                return $user->hasPermission($ability);
            });
        }

   }
}
