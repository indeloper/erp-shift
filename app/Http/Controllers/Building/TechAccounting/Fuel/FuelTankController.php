<?php

namespace App\Http\Controllers\Building\TechAccounting\Fuel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\StandardEntityResourceController;
use App\Models\Comment;
use App\Models\Company\Company;
use App\Models\Group;
use App\Models\Notification;
use App\Models\ProjectObject;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankFlow;
use App\Models\TechAcc\FuelTank\FuelTankMovement;
use App\Models\TechAcc\FuelTank\FuelTankTransferHystory;
use App\Models\User;
use App\Services\Common\FileSystemService;
use App\Services\SystemService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FuelTankController extends StandardEntityResourceController
{
    public function __construct()
    {
        $this->baseModel = new FuelTank;
        $this->routeNameFixedPart = 'building::tech_acc::fuel::tanks::';
        $this->sectionTitle = 'Топливные емкости';
        $this->baseBladePath = resource_path() . '/views/tech_accounting/fuel/tanks/objects';

        $this->isMobile =
            is_dir($this->baseBladePath . '/mobile')
            && SystemService::determineClientDeviceType($_SERVER["HTTP_USER_AGENT"]) === 'mobile';

        $this->componentsPath =
            $this->isMobile
                ?
                $this->baseBladePath . '/mobile/components'
                : $this->baseBladePath . '/desktop/components';

        $this->components = (new FileSystemService)->getBladeTemplateFileNamesInDirectory($this->componentsPath, $this->baseBladePath);
        $this->modulePermissionsGroups = [17];

    }

    public function index(Request $request)
    {
        $options = json_decode($request['data']);

        $searchValueQuery = '';

        if ($this->isMobile && $options->searchValue) {
            $searchValueQuery = $options->searchValue;
            unset($options->searchValue);
            unset($options->searchOperation);
        }

        $userId = Auth::user()->id;

        $entities = $this->baseModel
            ->dxLoadOptions($options)
            ->when(!User::find($userId)->hasPermission('watch_any_fuel_tanks'), function ($query) use ($userId) {
                return $query->where('responsible_id', $userId);
            })
            ->selectRaw(
                '
                    fuel_tanks.id,
                    fuel_tanks.explotation_start,
                    fuel_tanks.company_id,
                    fuel_tanks.tank_number,
                    fuel_tanks.object_id,
                    fuel_tanks.responsible_id,
                    fuel_tanks.fuel_level,
                    fuel_tanks.awaiting_confirmation,
                    fuel_tanks.comment_movement_tmp,
                    (SELECT MAX(`event_date`) from `fuel_tank_flows` where `fuel_tank_flows`.`fuel_tank_id` = `fuel_tanks`.`id`)
                    as max_event_date,
                    (SELECT `previous_object_id` from `fuel_tank_transfer_hystories` where id = (SELECT MAX(`id`) from `fuel_tank_transfer_hystories` where `fuel_tank_transfer_hystories`.`fuel_tank_id` = `fuel_tanks`.`id` and `fuel_tank_transfer_hystories`.`fuel_tank_flow_id` is null))
                    as previous_object_id,
                    (SELECT `previous_responsible_id` from `fuel_tank_transfer_hystories` where id = (SELECT MAX(`id`) from `fuel_tank_transfer_hystories` where `fuel_tank_transfer_hystories`.`fuel_tank_id` = `fuel_tanks`.`id` and `fuel_tank_transfer_hystories`.`fuel_tank_flow_id` is null))
                    as previous_responsible_id
                '
            )
            ->when($this->isMobile, function ($query) {
                return $query->with('object', 'responsible');
            })
            ->when(!empty($searchValueQuery), function ($query) use ($searchValueQuery) {
                $objectIds = ProjectObject::where('short_name', 'LIKE', '%' . $searchValueQuery . '%')->pluck('id')->toArray();
                $tankIds = FuelTank::where('tank_number', 'LIKE', '%' . $searchValueQuery . '%')->pluck('id')->toArray();
                return $query
                    ->whereIn('object_id', $objectIds)
                    ->orWhereIn('id', $tankIds);
            })
            ->get();

        return json_encode(array(
            "data" => $entities
        ),
        JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

    }

    public function afterStore($tank, $data, $dataToStore)
    {
        FuelTankTransferHystory::create([
            'author_id' => Auth::user()->id,
            'fuel_tank_id' => $tank->id,
            'object_id' => $tank->object_id,
            'responsible_id' => $tank->responsible_id,
            'fuel_level' => 0,
            'event_date' => $data['event_date'] ?? now()
        ]);
    }

    public function beforeUpdate($tank, $data)
    {
        FuelTankTransferHystory::create([
            'author_id' => Auth::user()->id,
            'fuel_tank_id' => $tank->id,
            'previous_object_id' => $tank->object_id,
            'object_id' => $data['object_id'] ?? $tank->object_id ?? null,
            'previous_responsible_id' => $tank->responsible_id,
            'responsible_id' => $data['responsible_id'] ?? $tank->responsible_id ?? null,
            'fuel_level' => $tank->fuel_level,
            'event_date' => $data['event_date'] ?? now(),
            'tank_moving_confirmation' => true
        ]);

        if (empty($data['responsible_id'])) {
            $data['awaiting_confirmation'] = false;
        } else {
            $data['awaiting_confirmation'] = true;
            $this->notifyNewTankResponsible($tank);
        }

        return [
            'data' => $data,
            // 'ignoreDataKeys' => []
        ];
    }

    public function getFuelTanksResponsibles()
    {
        return User::query()->active()
            ->whereIn('group_id', Group::FOREMEN)
            ->orWhere('group_id', 43)
                ->select(['id', 'user_full_name'])
                ->orderBy('last_name')
                ->get();
    }

    public function getCompanies() {
        $companies = Company::all();
        return response()->json($companies, 200, [], JSON_UNESCAPED_UNICODE|JSON_NUMERIC_CHECK);
    }

    public function getProjectObjects(Request $request)
    {
        $options = json_decode($request['data']);

        $objects = (new ProjectObject)
            ->where('is_participates_in_material_accounting', 1)
            ->whereNotNull('short_name')
            ->orderBy('short_name')
            ->get()
            ->toArray();

        return json_encode(
            $objects
        ,
        JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function validateTankNumberUnique(Request $request)
    {

        if(!$request->id) {
            return json_encode([
                'result' => !FuelTank::where(
                    'tank_number', $request->value
                )->exists()
            ],
            JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        } else {
            return json_encode([
                'result' => !FuelTank::where([
                    ['id', '<>', $request->id],
                    ['tank_number', $request->value]
                ])->exists()
            ],
            JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        }
    }

    public function moveFuelTank(Request $request)
    {
        $data = json_decode($request->data);
        $tank = $this->baseModel::findOrFail($data->id);

        if($tank->object_id == $data->object_id && $tank->responsible_id == $data->responsible_id)
        {
            return json_encode(['message'=>'Отказ. Попытка создать новую запись с текущими параметрами.']);
        }

        FuelTankTransferHystory::create([
            'author_id' => Auth::user()->id,
            'fuel_tank_id' => $tank->id,
            'previous_object_id' => $tank->object_id,
            'object_id' => $data->object_id,
            'previous_responsible_id' => $tank->responsible_id,
            'responsible_id' => $data->responsible_id,
            'fuel_level' => $tank->fuel_level,
            'event_date' => $data->event_date,
            'tank_moving_confirmation' => (int)$tank->responsible_id === (int)$data->responsible_id
        ]);

        if(!empty($data->comment_movement_tmp)) {
            Comment::create([
                'commentable_id' => $tank->id,
                'commentable_type' => 'App\Models\TechAcc\FuelTank\FuelTank',
                'comment' => $data->comment_movement_tmp,
                'author_id' => Auth::user()->id,

            ]);
        }

        if((int)$tank->responsible_id !== (int)$data->responsible_id) {
            $needNotification = true;
            $tank->awaiting_confirmation = true;
            $tank->comment_movement_tmp = $data->comment_movement_tmp ?? null;
        } else {
            $tank->comment_movement_tmp = null;
        }

        $tank->object_id = $data->object_id;
        $tank->responsible_id = $data->responsible_id;

        $tank->save();

        if(!empty($needNotification)) {
            $this->notifyNewTankResponsible($tank);
        }

        return json_encode($tank);
    }

    public function confirmMovingFuelTank(Request $request)
    {
        $fuelTankId = json_decode($request->fuelTankId);
        $tank = $this->baseModel::findOrFail($fuelTankId);

        $lastTankTransferHistory = FuelTankTransferHystory::query()
            ->whereNull('fuel_tank_flow_id')
            ->whereNull('tank_moving_confirmation')
            ->orderByDesc('id')
            ->firstOrFail();

        FuelTankTransferHystory::create([
            'author_id' => Auth::user()->id,
            'fuel_tank_id' => $tank->id,
            'previous_object_id' => $lastTankTransferHistory->previous_object_id,
            'object_id' => $tank->object_id,
            'previous_responsible_id' => $lastTankTransferHistory->previous_responsible_id,
            'responsible_id' => $tank->responsible_id,
            'fuel_level' => $tank->fuel_level,
            'event_date' => now(),
            'tank_moving_confirmation' => true
        ]);

        $tank->awaiting_confirmation = false;
        $tank->comment_movement_tmp = null;
        $tank->save();

        $userId = App::environment('local') ? Auth::user()->id : $tank->responsible_id;
        $isLocal = App::environment('local');

        $notificationHook = 'notificationHook_confirmFuelTankRecieve-id-' . $tank->id . '_endNotificationHook';
        $notification = Notification::where([
            ['user_id', $userId],
            ['name', 'LIKE', '%' . $notificationHook . '%']
        ])->orderByDesc('id')->first();

        if ($notification) {
            $notificationWithoutHook = str_replace($notificationHook, '', $notification->name);
            DB::table('notifications')->where('id', $notification->id)->update(['name' => $notificationWithoutHook]);
            // в этой версии laravel не работает saveQuetly, поэтому пришлось делать через DB, чтобы не отправлялось лишнее сообщение
        }
    }

    public function getFuelTankConfirmationFormData(Request $request)
    {
        $fuelTankId = (int)json_decode($request->fuelTankId);
        $tank = $this->baseModel::findOrFail($fuelTankId);
        $responseData = new \stdClass();

        if(!$tank->awaiting_confirmation)
        {
            $responseData->status = 'not need confirmation';
            return json_encode($responseData, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        }

        $responseData->status = 'need confirmation';
        $responseData->id = $tank->id;
        $responseData->tank_number = $tank->tank_number;
        $responseData->fuel_level = $tank->fuel_level;
        $responseData->object_name = ProjectObject::find($tank->object_id)->short_name;
        $responseData->responsible_name = User::find($tank->responsible_id)->full_name;

        return json_encode($responseData, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function notifyNewTankResponsible($tank)
    {
        // if(!empty($needNotification)) {
        $notificationHook = 'notificationHook_confirmFuelTankRecieve-id-' . $tank->id . '_endNotificationHook';
        $notificationText =
            'Подтвердите получение топливной емкости № '
            . $tank->tank_number . ' на объекте ' . ProjectObject::find($tank->object_id)->short_name
            . ' ' . $notificationHook;

        Notification::create([
            'name' => $notificationText,
            'user_id' => App::environment('local') ? Auth::user()->id : $tank->responsible_id,
            'type' => 0,
        ]);
        // }
    }
}
