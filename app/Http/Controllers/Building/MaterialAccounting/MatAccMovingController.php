<?php

namespace App\Http\Controllers\Building\MaterialAccounting;

use App\Events\OperationClosed;
use App\Http\Controllers\Controller;
use App\Http\Requests\Building\MaterialAccounting\CreateMovingRequest;
use App\Http\Requests\Building\MaterialAccounting\SendMovingRequest;
use App\Models\Manual\ManualMaterial;
use App\Models\MatAcc\MaterialAccountingOperation;
use App\Models\MatAcc\MaterialAccountingOperationMaterials;
use App\Models\MatAcc\MaterialAccountingOperationResponsibleUsers;
use App\Models\MatAcc\MaterialAccountingTtn;
use App\Models\MatAcc\MaterialAccountingTtnMaterial;
use App\Models\ProjectObject;
use App\Models\User;
use App\Services\MaterialAccounting\MaterialAccountingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MatAccMovingController extends Controller
{
    public function create(Request $request): View
    {
        $from_resp = User::find($request->from_resp);
        $from_obj = ProjectObject::find($request->from_obj);
        $to_resp = User::find($request->to_resp);
        $to_obj = ProjectObject::find($request->to_obj);

        return view('building.material_accounting.moving.create', [
            'units' => MaterialAccountingOperationMaterials::$main_units,
            'from_resp' => $from_resp,
            'from_obj' => $from_obj,
            'to_resp' => $to_resp,
            'to_obj' => $to_obj,
        ]);
    }

    public function work($operation_id): View
    {
        $operation = MaterialAccountingOperation::where('type', 4)->where('status', 1)->findOrFail($operation_id);
        $operation->checkClosed();
        $operation->load(['object_from', 'object_to', 'author', 'sender', 'recipient', 'responsible_users', 'responsible_users.user', 'materials.manual', 'materialsPart.updated_material', 'images_sender', 'documents_sender', 'images_recipient', 'documents_recipient', 'materialsPartFrom.materialFiles', 'materialsPartFrom.materialAddition.user', 'materialsPartTo.materialFiles', 'materialsPartTo.materialAddition.user']);

        return view('building.material_accounting.moving.work', [
            'operation' => $operation,
            'entities' => MaterialAccountingOperation::$entities,
        ]);
    }

    public function confirm($operation_id): View
    {
        $operation = MaterialAccountingOperation::where('type', 4)->where('status', 2)->findOrFail($operation_id);
        $operation->checkClosed();
        $operation->load(['object_from', 'object_to', 'author', 'sender', 'recipient', 'responsible_users', 'responsible_users.user', 'materials.manual', 'images_sender', 'documents_sender', 'images_recipient', 'documents_recipient']);

        $conflicts = false;

        foreach ($operation->allMaterials->whereIn('type', [3, 7]) as $plan_mat) {
            $conflicts = $plan_mat->sameMaterials()->where('type', 2)->sum('count') != $plan_mat->sameMaterials()->where('type', 1)->sum('count');
        }

        return view('building.material_accounting.moving.confirm', [
            'operation' => $operation,
            'conflicts' => $conflicts,
        ]);
    }

    public function complete($operation_id): View
    {
        $operation = MaterialAccountingOperation::where('type', 4)->whereIn('status', [3, 7])->findOrFail($operation_id);
        $operation->load(['object_from', 'object_to', 'author', 'sender', 'recipient', 'materials.manual', 'images_sender.operationMaterial', 'documents_sender', 'images_recipient.operationMaterial', 'documents_recipient']);

        return view('building.material_accounting.moving.complete', [
            'operation' => $operation,
        ]);
    }

    public function conflict($operation_id): View
    {
        $operation = MaterialAccountingOperation::where('type', 4)->where('status', 4)->findOrFail($operation_id);
        $operation->checkClosed();
        $operation->load(['object_from', 'object_to', 'author', 'sender', 'recipient', 'materials.manual', 'images_sender', 'documents_sender', 'images_recipient', 'documents_recipient']);

        return view('building.material_accounting.moving.conflict', [
            'operation' => $operation,
        ]);
    }

    public function edit($operation_id): View
    {
        $operation = MaterialAccountingOperation::where('type', 4)->whereIn('status', [1, 4])->findOrFail($operation_id);
        $operation->checkClosed();
        $operation->load(['object_from', 'object_to', 'author', 'sender', 'recipient', 'responsible_users', 'responsible_users.user', 'materials.manual', 'images_sender', 'documents_sender', 'images_recipient', 'documents_recipient']);

        return view('building.material_accounting.moving.update', [
            'operation' => $operation,
            'units' => MaterialAccountingOperationMaterials::$main_units,
            'edit_restrict' => false,
        ]);
    }

    public function draft($operation_id): View
    {
        $operation = MaterialAccountingOperation::where('type', 4)->whereIn('status', [5, 8])->findOrFail($operation_id);
        $operation->checkClosed();
        $operation->load(['object_from', 'object_to', 'author', 'sender', 'recipient', 'responsible_user', 'responsible_user.user', 'materials.manual', 'images_sender', 'documents_sender', 'images_recipient', 'documents_recipient']);

        return view('building.material_accounting.moving.update', [
            'operation' => $operation,
            'units' => MaterialAccountingOperationMaterials::$main_units,
            // operation author can't do anything in controlled operation
            'edit_restrict' => in_array(Auth::id(), [$operation->responsible_RP, $operation->author_id]) ? false : $operation->status == 8,
        ]);
    }

    public function make_ttn(Request $request): View
    {
        $ttn = new MaterialAccountingTtn();

        $ttn->operation_id = $request->operation_id;
        $ttn->main_entity = $request->main_entity_from;
        $ttn->main_entity_to = $request->main_entity_to;

        $ttn->take_time = $request->take['time'];
        $ttn->take_fact_arrival_time = $request->take['fact_arrival_time'];
        $ttn->take_weight = $request->take['weight'] == '0.000' ? null : $request->take['weight'];
        $ttn->take_places_count = $request->take['places_count'] == '0' ? null : $request->take['places_count'];

        $ttn->give_time = $request->give['time'];
        $ttn->give_fact_arrival_time = $request->give['fact_arrival_time'];
        $ttn->give_weight = $request->give['weight'] == '0.000' ? null : $request->give['weight'];
        $ttn->give_places_count = $request->give['places_count'] == '0' ? null : $request->give['places_count'];

        $ttn->entity = $request->entity;
        $ttn->city = $request->city;
        $ttn->address = $request->address;
        $ttn->phone_number = $request->phone_number;
        $ttn->driver_name = $request->driver_name;
        $ttn->driver_phone_number = $request->driver_phone_number;
        $ttn->vehicle = $request->vehicle;
        $ttn->trailer = $request->trailer;
        $ttn->trailer_number = $request->trailer_number;
        $ttn->carrier = $request->carrier;
        $ttn->consignor = Auth::id();

        $ttn->save();

        $materials_from = json_decode($request->materials_from);

        $operation = MaterialAccountingOperation::findOrFail($request->operation_id);
        $operation->load('object_from', 'object_to');

        $materials = ManualMaterial::whereIn('id', $materials_from->material_id)->get();
        foreach ($materials_from->material_id as $key => $material_id) {
            $append_material = $materials->where('id', $material_id)->first();

            $ttn_mat = new MaterialAccountingTtnMaterial();

            $ttn_mat->ttn_id = $ttn->id;
            $ttn_mat->count = $materials_from->material_count[$key];
            $ttn_mat->unit = $materials_from->material_unit[$key];
            $ttn_mat->material_id = $material_id;

            $ttn_mat->save();

            $append_material->count = $materials_from->material_count[$key];
            $append_material->unit = $materials_from->material_unit[$key];
        }

        $data = array_merge($request->all(), ['operation' => $operation, 'manual_materials' => $materials, 'id' => $ttn->id]);

        return view('building.material_accounting.ttn', $data);
    }

    public function store(CreateMovingRequest $request): JsonResponse
    {
        if (! Auth::user()->can('mat_acc_moving_create') && ! $request->is_draft) {
            return response()->json(['message' => 'У вас нет прав для создания операции перемещения!']);
        } elseif ($request->is_draft && ! (Auth::user()->can('mat_acc_moving_draft_create'))) {
            return response()->json(['message' => 'У вас нет прав для создания черновика операции перемещения!']);
        }

        DB::beginTransaction();

        $operation = MaterialAccountingOperation::create([
            'type' => 4,

            'object_id_from' => $request->object_id_from,
            'object_id_to' => $request->object_id_to,
            'contract_id' => $request->contract_id,

            'planned_date_from' => Carbon::parse($request->planned_date_from)->format('d.m.Y'),
            'planned_date_to' => Carbon::parse($request->planned_date_to)->format('d.m.Y'),

            'author_id' => Auth::user()->id,
            'sender_id' => 0,
            'recipient_id' => 0,
            'comment_author' => $request->comment,

            'status' => $request->is_draft ? 5 : 1,
            'is_close' => 0,
            'parent_id' => $request->parent_id ?? 0,
            'responsible_RP' => $request->responsible_RP ?? null,
        ]);

        $is_conflict = MaterialAccountingOperation::getModel()->checkProblem($operation, $request->materials);

        if ($is_conflict !== true and $is_conflict !== false) {
            return response()->json(['message' => $is_conflict]);
        }

        if ($is_conflict && $operation->status == 1) {
            $operation->status = 4;
            $operation->save();
        }

        $to_responsible_user = new MaterialAccountingOperationResponsibleUsers();

        $to_responsible_user->additional_info = $request->responsible_RP ? $request->responsible_RP : [];
        $to_responsible_user->operation_id = $operation->id;
        $to_responsible_user->user_id = $request->to_responsible_user;
        $to_responsible_user->type = 2;

        $to_responsible_user->saveOrFail();

        $from_responsible_user = new MaterialAccountingOperationResponsibleUsers();
        // little trick here, hehe sorry
        $from_responsible_user->additional_info = $request->responsible_RP ? 'skip' : [];
        $from_responsible_user->operation_id = $operation->id;
        $from_responsible_user->user_id = $request->from_responsible_user;
        $from_responsible_user->type = 1;

        $from_responsible_user->saveOrFail();

        $result = MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($operation, $request->materials, 7);

        if ($result !== true) {
            return response()->json(['message' => $result]);
        }

        $result = MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($operation, $request->materials, 6);

        if ($result !== true) {
            return response()->json(['message' => $result]);
        }

        $operation->generateOperationConflictNotifications();

        DB::commit();

        return response()->json(true);
    }

    public function update(CreateMovingRequest $request, $operation_id): JsonResponse
    {
        $operation = MaterialAccountingOperation::where('type', 4)->whereIn('status', [1, 4, 5])->findOrFail($operation_id);
        $operation->checkClosed();

        if ((! $operation->isAuthor() and $operation->responsible_RP != Auth::user()->id and ($operation->status != 1 and $operation->status != 5)) or ($operation->status != 5 and ! Auth::user()->can('mat_acc_moving_create'))) {
            return response()->json(['message' => 'У вас нет прав для создания операции перемещения!']);
        } elseif ($operation->status == 5 and Auth::user()->can('mat_acc_moving_draft_create')) {
            // update info only logic
            $draft = true;
        }

        DB::beginTransaction();

        $oldStatus = $operation->status;
        $oldAuthor = $operation->author_id;
        $operation->author_id = Auth::user()->id;
        if ($operation->status != 4) {
            isset($draft) ?: $operation->status = 1;
        }
        $operation->planned_date_from = Carbon::parse($request->planned_date_from)->format('d.m.Y');
        $operation->planned_date_to = Carbon::parse($request->planned_date_to)->format('d.m.Y');
        $operation->object_id_from = $request->object_id_from;
        $operation->object_id_to = $request->object_id_to;
        $operation->contract_id = $request->contract_id;

        $is_conflict = MaterialAccountingOperation::getModel()->checkProblem($operation, $request->materials);

        if ($is_conflict !== true and $is_conflict !== false) {
            return response()->json(['message' => $is_conflict]);
        }

        if (! $is_conflict && $operation->status == 4) {
            $operation->status = 1;
        } elseif (! $is_conflict) {
            $operation->status = 1;
        } else {
            $operation->status = 4;
            $operation->author_id = $operation->responsible_RP ?? Auth::user()->id;
        }

        if ($operation->wasDraftAndUserCanWorkOnlyWithDraftsAndNoConflictInOperation($oldStatus, $is_conflict)) {
            $operation->status = $oldStatus;
            $operation->generateDraftUpdateNotifications();
        }

        if ($operation->wasDraftAndUserCanCreateOperationAndNoConflictInOperation($oldStatus, $is_conflict)) {
            $operation->status = 1;
            $operation->generateDraftAcceptNotification($oldAuthor);
        }

        $operation->saveOrFail();

        MaterialAccountingOperationResponsibleUsers::where('operation_id', $operation_id)->delete();

        $to_responsible_user = new MaterialAccountingOperationResponsibleUsers();

        $to_responsible_user->additional_info = $operation->wasDraftAndUserCanWorkOnlyWithDraftsAndNoConflictInOperation($oldStatus, $is_conflict) ? 'skip' : [];
        $to_responsible_user->operation_id = $operation->id;
        $to_responsible_user->user_id = $request->to_responsible_user;
        $to_responsible_user->type = 2;

        $to_responsible_user->saveOrFail();

        $from_responsible_user = new MaterialAccountingOperationResponsibleUsers();
        // little trick here, hehe sorry
        $from_responsible_user->additional_info = $operation->wasDraftAndUserCanWorkOnlyWithDraftsAndNoConflictInOperation($oldStatus, $is_conflict) ? 'skip' : [];
        $from_responsible_user->operation_id = $operation->id;
        $from_responsible_user->user_id = $request->from_responsible_user;
        $from_responsible_user->type = 1;

        $from_responsible_user->saveOrFail();

        MaterialAccountingOperationMaterials::where('operation_id', $operation->id)->whereIn('type', [3, 6, 7])->delete();

        $result = MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($operation, $request->materials, 7);

        if ($result !== true) {
            return response()->json(['message' => $result]);
        }

        $result = MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($operation, $request->materials, 6);

        if ($result !== true) {
            return response()->json(['message' => $result]);
        }

        $part_mats = MaterialAccountingOperationMaterials::where('operation_id', $operation->id)->whereIn('type', [8, 9])->get();

        foreach ($part_mats as $part_mat) {
            $plan_q = $part_mat->sameMaterials()->whereIn('type', [3, 7]);
            if ($plan_q->doesntExist()) {
                if ($plan_q->withTrashed()->exists()) {
                    $plan_mat = $plan_q->first();
                    $plan_mat->restore();
                    $plan_mat->count = 0;
                    $plan_mat->save();
                } else {
                    $plan_mat = $part_mat->replicate();
                    $plan_mat->count = 0;
                    $plan_mat->type = 7;
                    $plan_mat->save();
                }
            }
        }

        $operation->generateOperationConflictNotifications();

        DB::commit();

        return response()->json(true);
    }

    public function send(Request $request, $operation_id): JsonResponse
    {
        $operation = MaterialAccountingOperation::where('type', 4)->where('status', 1)->with('materials.manual')->findOrFail($operation_id);
        $operation->checkClosed();

        DB::beginTransaction();

        $result = $operation->send($request);

        DB::commit();

        return response()->json($result);
    }

    public function part_send(SendMovingRequest $request, $operation_id): JsonResponse
    {
        DB::beginTransaction();

        $operation = MaterialAccountingOperation::findOrFail($operation_id);

        $result = $operation->partSend($request);

        if ($result['status'] == 'error') {
            return response()->json($result);
        }

        DB::commit();

        return response()->json($result);
    }

    public function accept(Request $request, $operation_id): JsonResponse
    {
        DB::beginTransaction();

        $operation = MaterialAccountingOperation::where('type', 4)->where('status', 2)->findOrFail($operation_id);

        $result = (new MaterialAccountingService())->acceptOperation($operation);

        if ($result !== true) {
            return response()->json(['message' => $result]);
        }

        event((new OperationClosed)->withOutContract($operation));

        DB::commit();

        return response()->json($request->all());
    }
}
