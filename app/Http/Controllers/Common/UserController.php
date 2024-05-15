<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequests\{UserCreateRequest, UserUpdatePasswordRequest, UserUpdateRequest};
use App\Models\{Department,
    FileEntry,
    Group,
    GroupPermission,
    Permission,
    Project,
    ProjectResponsibleUser,
    User,
    UserPermission,
    UsersSetting};
use App\Models\Notifications\UserDisabledNotifications;
use App\Models\TechAcc\Defects\Defects;
use App\Models\Vacation\VacationsHistory;
use App\Traits\AdditionalFunctions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Artisan, Auth, DB, File, Hash, Storage};

class UserController extends Controller
{
    use AdditionalFunctions;

    public function index(Request $request)
    {
        $newRequest = $this->createNewRequest($request->toArray());
        $users = User::getAllUsers()->withoutGlobalScope('email')->filter($newRequest)->orderByRaw("CASE WHEN users.id IN (6,7) THEN 1 ELSE 2 END, users.last_name");

        if ($request->search) {
            $groups = Group::where('name', $request->search)
                ->orWhere('name', 'like', '%' . $request->search . '%')
                ->pluck('id')
                ->toArray();

            $departments = Department::where('name', $request->search)
                ->orWhere('name', 'like', '%' . $request->search . '%')
                ->pluck('id')
                ->toArray();

            $results = array_keys(array_filter(User::$companies, function ($item) use ($request) {
                return stristr(mb_strtolower($item), mb_strtolower($request->search));
            }));

            $users->where(function ($query) use ($request) {
                $query->where('last_name', 'like', '%' . $request->search . '%')
                    ->orWhere('first_name', 'like', '%' . $request->search . '%')
                    ->orWhere('patronymic', 'like', '%' . $request->search . '%')
                    ->orWhere(DB::raw("CONCAT(last_name, ' ', first_name, ' ', patronymic)"), 'LIKE', "%" . $request->search . "%");
            });

            if (!empty($groups)) {
                $users->orWhere(function ($query) use ($groups) {
                    $query->orWhereIn('users.group_id', $groups);
                });
            }

            if (!empty($departments)) {
                $users->orWhere(function ($query) use ($departments) {
                    $query->orWhereIn('users.department_id', $departments);
                });
            }

            if (!empty($results)) {
                $users->orWhere(function ($query) use ($results) {
                    $query->orWhereIn('users.company', $results);
                });
            }
        }

        return view('users.index', [
            'users' => $users->whereNotNull('is_deleted')->paginate(20),
            'companies' => User::$companies,
        ]);
    }


    public function sidebar(Request $request)
    {
        $request->session()->put('sidebar_mini', $request->sidebar_mini);

        return \GuzzleHttp\json_encode(true);
    }


    public function create()
    {
        return view('users.create', [
            'groups' => Group::all(),
            'departments' => Department::all(),
            'companies' => User::$companies,
        ]);
    }


    public function store(UserCreateRequest $request)
    {
        $user = new User();

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->patronymic = $request->patronymic ?? '';
        $user->email = $request->email;
        $user->birthday = $request->birthday;
        $user->status = $request->status;

        $user->department_id = Group::findOrFail($request->group_id)->department_id;
        $user->group_id = $request->group_id;
        $user->company = $request->company;

        $user->person_phone = preg_replace('~[\D]~', '', $request->person_phone);
        $user->work_phone = preg_replace('~[\D]~', '', $request->work_phone);

        $user->password = bcrypt($request->input('password'));

        if ($request->is_su) {
            $user->is_su = $request->is_su;
        }

        if ($request->user_image) {
            $this->addUserImage($request, $user);
        }

        $user->save();

        return redirect()->route('users::card', $user->id);
    }


    public function department(Request $request)
    {
        $groups = Group::where('department_id', $request->department_id)->get();

        return \GuzzleHttp\json_encode($groups);
    }


    public function card($id)
    {
        $user = User::withoutGlobalScope('email')->findOrFail($id);
        $user->load('group');
        $this->authorize('card', $user);

        if ($user->is_deleted) {
            abort(404);
        }
        $user->birthday =  $user->birthday ? (new Carbon($user->birthday))->format('d.m.Y') : 'Не указан';

        $project_ids = ProjectResponsibleUser::where('user_id', $id)->pluck('project_id')->toArray();
        $projects = Project::getAllProjects()->whereIn('projects.id', $project_ids)->get();

        $vacation = VacationsHistory::with('support_user')->where('vacation_user_id', $id)->where('is_actual', 1)->first();

        $group = Group::whereId($user->group_id)->with('users', 'group_permissions')->first();
        if (isset($group)) {
            $department = Department::find($group->department_id);
            $department->load('groups');
        }
        $permissions = Permission::all();
        if (isset($department)) {
            $departmentPermissions = $permissions->whereIn('id', $department->permission_ids($department->groups))->values();
            $groupPermissions = $group->permissions()->whereNotIn('permission_id', $department->permission_ids($department->groups))->values();
        } else {
            $departmentPermissions = [];
            $groupPermissions = [];
        }

        return view('users.card', [
            'user' => $user,
            'group' => Group::find($user->group_id),
            'department' => Department::find($user->department_id),
            'projects' => $projects->concat(Project::getAllProjects()->where('projects.user_id', $id)->get())->unique(),
            'vacation' => $vacation,
            'permissions' => Permission::all(),
            'department_perms' => $departmentPermissions,
            'group_permissions' => $groupPermissions,
            'companies' => User::$companies,
        ]);
    }


    public function edit($id)
    {
        $user = User::withoutGlobalScope('email')->findOrFail($id);

        $this->authorize('update', $user);

        if ($user->is_deleted) {
            abort(404);
        }

        return view('users.edit', [
            'groups' => Group::all(),
            'departments' => Department::all(),
            'user' => $user,
            'birthday' => $user->birthday ? (new Carbon($user->birthday))->format('m.d.Y') : 'Не указан',
            'companies' => User::$companies,
        ]);
    }


    public function update(UserUpdateRequest $request, $id)
    {
        $user = User::withoutGlobalScope('email')->findOrFail($id);

        $this->authorize('update', $user);

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->patronymic = $request->patronymic ? $request->patronymic : '';
        if (Auth::user()->can('users_create')) {
            $user->email = $request->email;
        }
        $user->company = $request->company ?? $user->company;
        $user->birthday = $request->birthday;
        $user->status = $request->status ?? $user->status;

        if ($request->group_id) {
            $user->department_id = Group::findOrFail($request->group_id)->department_id;
            $user->group_id = $request->group_id;
        }

        $user->person_phone = preg_replace('~[\D]~', '', $request->person_phone);
        $user->work_phone = preg_replace('~[\D]~', '', $request->work_phone);

        if($request->input('password')) {
            $user->password = bcrypt($request->input('password'));
        }

        $user->is_su = $request->is_su ?? $user->is_su;

        if ($request->user_image) {
            $this->addUserImage($request, $user);
        }

        $user->chat_id = $request->chat_id ?? $user->chat_id;

        $user->save();

        return redirect()->route('users::card', $user->id);
    }


    public function change_password(UserUpdatePasswordRequest $request, $id)
    {
        if (Auth::id() != $id) {
            abort(403);
        }

        if (Hash::check($request->get('old_password'), Auth::user()->password)) {
            $user = User::findOrFail($id);

            if($request->input('password')) {
                $user->password = bcrypt($request->input('password'));
            }

            $user->save();

            return redirect()->back();
        }

        return redirect()->back()->with('pass', 0);
    }


    public function to_vacation(Request $request, $id)
    {
        if ($id == $request->support_user_id) {
            return back()->with('bad_request', 'Вы выбрали одинаковых сотрудников для отпуска и замещения');
        } else if (VacationsHistory::where('vacation_user_id', $request->vacation_user_id)->where('is_actual', 1)->count()) {
            return back()->with('too_much_vacations', 'Сотрудник уже находится в отпуске');
        }

        DB::beginTransaction();

        // create vacation
        $vacation = VacationsHistory::create([
            'is_actual' => 1,
            'vacation_user_id' => $id,
            'support_user_id' => $request->support_user_id,
            'from_date' => Carbon::createFromFormat('d.m.Y', $request->from_date)->toDateString(),
            'by_date' => Carbon::createFromFormat('d.m.Y', $request->by_date)->toDateString(),
            'change_authority' => $request->has('change_authority') ? 1 : 0,
        ]);

        if (Carbon::createFromFormat('d.m.Y', $request->from_date)->lte(now())) {
            Artisan::call('users:check-vacations');
        }

        DB::commit();

        return redirect()->back();
    }


    public function from_vacation(Request $request, $id)
    {
        if (User::find($id)->in_vacation == 0) {
            return response()->json(false);
        }

        // find vacation
        $vacation = VacationsHistory::where('id', $request->vacation_id)->first();

        // logic
        $update = User::from_vacation($id, $vacation);

        return response()->json(true);
    }


    public function remove(Request $request, $id)
    {
        if ($id == $request->support_user_id) {
            return back()->with('bad_request', 'Вы выбрали одинаковых сотрудников для удаления и замещения');
        }

        DB::beginTransaction();

        //logic
        $remove = User::remove_user($id, $request->support_user_id);

        DB::commit();

        return redirect()->route('users::index');
    }


    public function department_permissions(Request $request)
    {
        $departaments = Department::with('users');

        if ($request->search) {
            $departaments->where('name', 'like', '%' . $request->search . '%');
        }

        return view('users.permissions.department_permissions', [
            'departments' => $departaments->get(),
            'permissions' => Permission::all()
        ]);
    }

    public function group_permissions(Request $request, $department_id)
    {
        $department = Department::findOrFail($department_id);
        $groups = Group::whereDepartmentId($department_id)->with('users', 'group_permissions');

        if ($request->search) {
            $groups->where('name', 'like', '%' . $request->search . '%');
        }

        $permissions = Permission::all();

        return view('users.permissions.group_permissions', [
            'department' => $department,
            'groups' => $groups->get(),
            'permissions' => $permissions,
            'department_perms' => $permissions->whereIn('id', $department->permission_ids($groups))->values()
        ]);
    }

    public function user_permissions(Request $request, $group_id)
    {
        $group = Group::whereId($group_id)->with(['users' => function($q) use ($request) {
            if ($request->search) {
                $q->where(function ($query) use ($request) {
                    $query->where('last_name', 'like', '%' . $request->search . '%')
                        ->orWhere('first_name', 'like', '%' . $request->search . '%')
                        ->orWhere('patronymic', 'like', '%' . $request->search . '%');
                });
            }
        }, 'group_permissions']);

        $group = $group->first();
        $department = Department::findOrFail($group->department_id);
        $department->load('groups');
        $permissions = Permission::all();

        return view('users.permissions.user_permissions', [
            'department' => $department,
            'group' => $group,
            'permissions' => $permissions,
            'group_permissions' => $group->permissions()->whereNotIn('permission_id', $department->permission_ids($department->groups))->values(),
            'department_perms' => $permissions->whereIn('id', $department->permission_ids($department->groups))->values()
        ]);
    }

    public function add_permissions(Request $request)
    {
        DB::beginTransaction();

        if ($request->type == 'user') {
            UserPermission::where('user_id', $request->user_id)->delete();

            foreach ($request->permission_ids as $id) {
                UserPermission::create([
                    'user_id' => $request->user_id,
                    'permission_id' => $id
                ]);
            }
        }
        else if ($request->type == 'group') {
            GroupPermission::where('group_id', $request->group_id)->delete();

            foreach ($request->permission_ids as $id) {
                GroupPermission::create([
                    'group_id' => $request->group_id,
                    'permission_id' => $id
                ]);
            }
        }
        else if ($request->type == 'department') {
            $department = Department::findOrFail($request->department_id);
            $department->load('groups');

            GroupPermission::whereIn('group_id', $department->groups->pluck('id'))
                ->whereIn('permission_id', array_merge($department->permission_ids($department->groups), $request->permission_ids))->delete();

            foreach ($request->permission_ids as $id) {
                foreach ($department->groups as $group) {
                    GroupPermission::create([
                        'group_id' => $group->id,
                        'permission_id' => $id
                    ]);
                }
            }
        }

        DB::commit();

        return response()->json(true);
    }


    public function addUserImage(Request $request, $user): void
    {
        $mime = $request->user_image->getClientOriginalExtension();
        $file_name = 'user-' . rand() . '.' . $mime;

        Storage::disk('user_images')->put($file_name, File::get($request->user_image));

        FileEntry::create(['filename' => $file_name, 'size' => $request->user_image->getSize(),
            'mime' => $request->user_image->getClientMimeType(), 'original_filename' => $request->user_image->getClientOriginalName(), 'user_id' => Auth::user()->id,]);

        $user->image = $file_name;
    }

    public function update_notifications()
    {
        $user = auth()->user();
        $userAllowedNotifications = $user->allowedNotifications();

        if (request('disableAll'))
            return $this->disableAllNotifications($user, $userAllowedNotifications);

        DB::beginTransaction();

        $disabledInTelegram = array_diff($userAllowedNotifications, request('in_telegram') ?? []);
        $disabledInSystem = array_diff($userAllowedNotifications, request('in_system') ?? []);
        $userDisabledTelegramNotifications = $user->disabledInTelegramNotifications()->pluck('notification_id')->toArray();
        $userDisabledSystemNotifications = $user->disabledInSystemNotifications()->pluck('notification_id')->toArray();
        $nowTurnedOnTelegramNotifications = array_intersect(request('in_telegram') ?? [], $userDisabledTelegramNotifications);
        $nowTurnedOnSystemNotifications = array_intersect(request('in_system') ?? [], $userDisabledSystemNotifications);

        $this->updateUserDisabledNotifications($nowTurnedOnSystemNotifications, $user, $nowTurnedOnTelegramNotifications, 1);
        $this->updateUserDisabledNotifications($disabledInSystem, $user, $disabledInTelegram);

//        dd($disabledInSystem, $disabledInTelegram, $nowTurnedOnTelegramNotifications, $nowTurnedOnSystemNotifications, request()->all());

        DB::commit();

        return back();
    }

    /**
     * @param array $systemNotifications
     * @param $user
     * @param array $telegramNotifications
     * @param int $on
     * @return void
     */
    public function updateUserDisabledNotifications(array $systemNotifications, $user, array $telegramNotifications, int $on = 0): void
    {
        foreach ($systemNotifications as $NotificationId) {
            UserDisabledNotifications::updateOrCreate(
                ['user_id' => $user->id, 'notification_id' => $NotificationId],
                ['in_system' => $on]
            );
        }

        foreach ($telegramNotifications as $NotificationId) {
            UserDisabledNotifications::updateOrCreate(
                ['user_id' => $user->id, 'notification_id' => $NotificationId],
                ['in_telegram' => $on]
            );
        }
    }

    public function disableAllNotifications($user, $userAllowedNotifications)
    {
        foreach ($userAllowedNotifications as $NotificationId) {
            UserDisabledNotifications::updateOrCreate(
                ['user_id' => $user->id, 'notification_id' => $NotificationId],
                ['in_telegram' => 0, 'in_system' => 0]
            );
        }

        return redirect(route('notifications::index'));
    }

    public function get_users_for_tech_tickets(Request $request, $users_json = [])
    {
        $users = User::forTechTickets($request->q, $request->group_ids)->where('id', '!=', $request->without ?? 0)->get();

        $authed_rp = json_decode($request->authed_rp, true);

        if ($authed_rp) {
            $users_json[] = ['code' => "{$authed_rp['code']}", 'label' => $authed_rp['label']];

            foreach ($users as $user) {
                if (!in_array($user->id, [1, $authed_rp['code']])) {
                    $users_json[] = ['code' => $user->id . '', 'label' => $user->full_name];
                }
            }
        } else {
            foreach ($users as $user) {
                if ($user->id != 1) {
                    $users_json[] = ['code' => $user->id . '', 'label' => $user->full_name];
                }
            }
        }


        return response()->json($users_json);
    }

    public function get_users_for_tech_select2(Request $request, $users_json = [])
    {
        $users = User::forTechTickets($request->q, $request->group_ids)->get();

        foreach ($users as $user) {
            if ($user->id != 1) {
                $users_json[] = [
                    'id' => $user->id,
                    'text' => trim($user->last_name . ' ' . $user->first_name . ' ' . $user->patronymic) . ', Должность: ' . $user->group->name,
                ];;
            }
        }

        return ['results' => $users_json];
    }

    public function get_authors_for_defects(Request $request, $users_json = [])
    {
        $newRequest = $this->createNewRequest($request->except('q'));
        $user_ids = Defects::filter($newRequest)->pluck('user_id')->unique()->toArray();
        $users = User::forDefects($request->q, $user_ids)->get();

        foreach ($users as $user) {
            if ($user->id != 1) {
                $users_json[] = ['code' => $user->id . '', 'label' => $user->full_name];
            }
        }

        return response()->json($users_json);
    }

    public function get_responsible_users_for_defects(Request $request, $users_json = [])
    {
        $newRequest = $this->createNewRequest($request->except('q'));
        $responsible_user_ids = Defects::filter($newRequest)->whereNotNull('responsible_user_id')->pluck('responsible_user_id')->unique()->toArray();
        $users = User::forDefects($request->q, $responsible_user_ids)->get();

        foreach ($users as $user) {
            if ($user->id != 1) {
                $users_json[] = ['code' => $user->id . '', 'label' => $user->full_name];
            }
        }

        return response()->json($users_json);
    }

    public function getUsersPaginated(Request $request)
    {
        $output = [];
        parse_str(parse_url($request->url)['query'] ?? '', $output);
        $newRequest = $this->createNewRequest($output);

        $filtered_users = User::filter($newRequest)->orderBy('updated_at')->paginate(15);

        return response()->json([
            'data' => [
                'users' => $filtered_users->items(),
                'users_count' => $filtered_users->total(),
            ],
        ]);
    }

    public function getSetting(Request $request){
        $codename = json_decode($request['data'])->codename;
        return (new UsersSetting)->getSetting($codename)->toJSON();
    }

    public function setSetting(Request $request){
        $codename = json_decode($request['data'])->codename;
        $value = json_decode($request['data'])->value;

        (new UsersSetting)->setSetting($codename, $value);
    }

    public function getActiveUsersForVacationCardFrontend()
    {
        $users = User::query()->active()->get();

        $results = [];
        foreach ($users as $user) {
            $results[] = [
                 'id' => $user->id,
                 'text' => trim($user->last_name . ' ' . $user->first_name . ' ' . $user->patronymic) . ', ' . $user->group_name,
             ];
        }

        return ['results' => $results];
    }

    public function getAvailableUsersForReplaceEmployeeDuringVacation(Request $request)
    {
        $users = User::where('group_id', User::find($request->userId)->group_id)->active()->get();

        $results = [];
        foreach ($users as $user) {
            $results[] = [
                 'id' => $user->id,
                 'text' => trim($user->last_name . ' ' . $user->first_name . ' ' . $user->patronymic) . ', ' . $user->group_name,
             ];
        }

        return ['results' => $results];
    }
}
