<?php

namespace App\Http\Controllers\Building\MaterialAccounting;

use App\Http\Requests\Building\MaterialAccounting\SendTransformationRequest;
use App\Models\MatAcc\MaterialAccountingOperationResponsibleUsers;
use App\Models\ProjectObject;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests\Building\MaterialAccounting\CreateTransformationRequest;

use App\Models\MatAcc\MaterialAccountingOperation;
use App\Models\MatAcc\MaterialAccountingOperationMaterials;
use App\Models\MatAcc\MaterialAccountingBase;
use App\Models\MatAcc\MaterialAccountingMaterialFile;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class MatAccTransformationController extends Controller
{
    public function create(Request $request)
    {
        $from_resp = User::find($request->resp);
        $from_obj = ProjectObject::find($request->obj);

        return view('building.material_accounting.transformation.create', [
            'units' => MaterialAccountingOperationMaterials::$main_units,
            'resp' => $from_resp,
            'obj' => $from_obj,
        ]);
    }

    public function work($operation_id)
    {
        $operation = MaterialAccountingOperation::where('type', 3)->where('status', 1)->findOrFail($operation_id);
        $operation->checkClosed();
        $operation->load(['object_from', 'object_to', 'author', 'sender', 'recipient', 'responsible_user', 'responsible_user.user', 'materials.manual', 'images_sender', 'documents_sender', 'images_recipient', 'documents_recipient']);

        return view('building.material_accounting.transformation.work', [
            'operation' => $operation
        ]);
    }

    public function confirm($operation_id)
    {
        $operation = MaterialAccountingOperation::where('type', 3)->where('status', 2)->findOrFail($operation_id);
        $operation->checkClosed();
        $operation->load(['object_from', 'object_to', 'author', 'sender', 'recipient', 'responsible_user', 'responsible_user.user', 'materials.manual', 'images_sender', 'documents_sender', 'images_recipient', 'documents_recipient']);

        return view('building.material_accounting.transformation.confirm', [
            'operation' => $operation
        ]);
    }

    public function complete($operation_id)
    {
        $operation = MaterialAccountingOperation::where('type', 3)->whereIn('status', [3,7])->findOrFail($operation_id);
        $operation->load(['object_from', 'object_to', 'author', 'sender', 'recipient', 'materials.manual', 'images_sender', 'documents_sender', 'images_recipient', 'documents_recipient']);

        return view('building.material_accounting.transformation.complete', [
            'operation' => $operation
        ]);
    }

    public function conflict($operation_id)
    {
        $operation = MaterialAccountingOperation::where('type', 3)->where('status', 4)->findOrFail($operation_id);
        $operation->checkClosed();
        $operation->load(['object_from', 'object_to', 'author', 'sender', 'recipient', 'materials.manual', 'images_sender', 'documents_sender', 'images_recipient', 'documents_recipient']);

        return view('building.material_accounting.transformation.conflict', [
            'operation' => $operation
        ]);
    }

    public function edit($operation_id)
    {
        $operation = MaterialAccountingOperation::where('type', 3)->whereIn('status', [1, 4])->findOrFail($operation_id);
        $operation->checkClosed();
        $operation->load(['object_from', 'object_to', 'author', 'sender', 'recipient', 'responsible_user', 'responsible_user.user', 'materials.manual', 'images_sender', 'documents_sender', 'images_recipient', 'documents_recipient']);

        return view('building.material_accounting.transformation.update', [
            'operation' => $operation,
            'units' => MaterialAccountingOperationMaterials::$main_units,
            'edit_restrict' => false,
        ]);
    }

    public function draft($operation_id)
    {
        $operation = MaterialAccountingOperation::where('type', 3)->whereIn('status', [5, 8])->findOrFail($operation_id);
        $operation->checkClosed();
        $operation->load(['object_from', 'object_to', 'author', 'sender', 'recipient', 'responsible_user', 'responsible_user.user', 'materials.manual', 'images_sender', 'documents_sender', 'images_recipient', 'documents_recipient']);

        return view('building.material_accounting.transformation.update', [
            'operation' => $operation,
            'units' => MaterialAccountingOperationMaterials::$main_units,
            // operation author can't do anything in controlled operation
            'edit_restrict' => false,
        ]);
    }

    // 4 => 'fact_to' // NOW only for moving, transformation
    // 4 => 'fact_from' // NOW only for moving, transformation
    // 5 => 'plan_to', // NOW only for transformation
    // 6 => 'plan_from' // NOW only for transformation

    public function store(CreateTransformationRequest $request)
    {
        if (!Auth::user()->can('mat_acc_transformation_create') && !$request->is_draft) {
            return response()->json(['message' => 'У вас нет прав для создания операции поступления!']);
        } elseif ($request->is_draft && !(Auth::user()->can('mat_acc_transformation_draft_create'))) {
            return response()->json(['message' => 'У вас нет прав для создания черновика операции поступления!']);
        }

        DB::beginTransaction();

        $operation = MaterialAccountingOperation::create([
            'type' => 3,

            'object_id_from' => $request->object_id,
            'object_id_to' => $request->object_id,

            'planned_date_from' => Carbon::parse($request->planned_date_to)->format('d.m.Y'),
            'planned_date_to' => Carbon::parse($request->planned_date_to)->format('d.m.Y'),

            'author_id' => Auth::user()->id,
            'sender_id' => 0,
            'recipient_id' => 0,
            'comment_author' => $request->comment,
            'reason' => $request->reason,


            'status' =>  $request->is_draft ? 5 : 1,
            'is_close' => $request->parent_id ?? 0,
            'responsible_RP' => $request->responsible_RP ?? null
        ]);

        $is_conflict = MaterialAccountingOperation::getModel()->checkProblem($operation, $request->materials_from);

        if ($is_conflict !== true and $is_conflict !== false) {
            return response()->json(['message' => $is_conflict]);
        }

        if ($is_conflict && $operation->status == 1) {
            $operation->update(['status' => 4]);
        }

        $responsible_user = new MaterialAccountingOperationResponsibleUsers();

        $responsible_user->additional_info = $request->responsible_RP ? $request->responsible_RP : [];
        $responsible_user->operation_id = $operation->id;
        $responsible_user->user_id = $request->responsible_user_id;

        $responsible_user->saveOrFail();

        $result_from = MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($operation, $request->materials_from, 7);

        if ($result_from !== true) {
            return response()->json(['message' => $result_from]);
        }

        $result_to = MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($operation, $request->materials_to, 6);

        if ($result_to !== true) {
            return response()->json(['message' => $result_to]);
        }

        $operation->generateOperationConflictNotifications();

        DB::commit();

        return response()->json(true);
    }

    public function update(CreateTransformationRequest $request, $operation_id)
    {
        $operation = MaterialAccountingOperation::where('type', 3)->whereIn('status', [1, 4, 5, 8])->findOrFail($operation_id);
        $operation->checkClosed();

        if ((!$operation->isAuthor() and ($operation->status != 1 and $operation->status != 5)) or ($operation->status != 5 and !Auth::user()->can('mat_acc_transformation_create'))) {
            return response()->json(['message' => 'У вас нет прав для создания операции преобразования!']);
        } elseif ($operation->status == 5 and Auth::user()->can('mat_acc_transformation_draft_create')) {
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
        $operation->object_id_from = $request->object_id;
        $operation->object_id_to = $request->object_id;
        $operation->planned_date_from = Carbon::parse($request->planned_date_to)->format('d.m.Y');
        $operation->planned_date_to = Carbon::parse($request->planned_date_to)->format('d.m.Y');
        $operation->comment_author = $request->comment;
        $operation->reason = $request->reason;

        $is_conflict = MaterialAccountingOperation::getModel()->checkProblem($operation, $request->materials_from);

        if ($is_conflict !== true and $is_conflict !== false) {
            return response()->json(['message' => $is_conflict]);
        }

        if (!$is_conflict && $operation->status == 4) {
            $operation->status = 1;
        } elseif (!$is_conflict) {
            $operation->status = 1;
        } else {
            $operation->status = 4;
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

        $responsible_user = new MaterialAccountingOperationResponsibleUsers();
        // little trick here, hehe sorry
        $responsible_user->additional_info = $operation->wasDraftAndUserCanWorkOnlyWithDraftsAndNoConflictInOperation($oldStatus, $is_conflict) ? 'skip' : [];
        $responsible_user->operation_id = $operation->id;
        $responsible_user->user_id = $request->responsible_user_id;

        $responsible_user->saveOrFail();

        MaterialAccountingOperationMaterials::where('operation_id', $operation->id)->whereIn('type', [6, 7])->delete();

        $result_from = MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($operation, $request->materials_from, 7);

        if ($result_from !== true) {
            return response()->json(['message' => $result_from]);
        }

        $result_to = MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($operation, $request->materials_to, 6);

        if ($result_to !== true) {
            return response()->json(['message' => $result_to]);
        }

        $operation->generateOperationConflictNotifications();

        DB::commit();

        return response()->json(true);
    }


    public function send(SendTransformationRequest $request, $operation_id)
    {
        DB::beginTransaction();

        $operation = MaterialAccountingOperation::where('type', 3)->where('status', 1)->findOrFail($operation_id);
        $operation->checkClosed();

        $result = $operation->send($request);

        DB::commit();

        return response()->json($result);
    }

    public function part_send(SendTransformationRequest $request, $operation_id)
    {
        DB::beginTransaction();

        $operation = MaterialAccountingOperation::findOrFail($operation_id);

        $result_to = $operation->partSend($request);

        if ($result_to['status'] == 'error') {
            return response()->json($result_to);
        }

        DB::commit();

        return response()->json($result_to);
    }

    public function accept(Request $request, $operation_id)
    {
        DB::beginTransaction();

        $operation = MaterialAccountingOperation::where('type', 3)->where('status', 2)->findOrFail($operation_id);
        $operation->checkClosed();

        $operation->status = 3;
        $operation->is_close = 1;
        $operation->actual_date_to = Carbon::now()->format('d.m.Y');
        $operation->recipient_id = Auth::user()->id;
        $operation->comment_to = $request->comment;

        $operation->saveOrFail();

        $operation->generateOperationAcceptNotifications();

        $result_from = MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($operation, $request->materials_from, 5, 'inactivity');

        if ($result_from !== true) {
            return response()->json(['message' => $result_from]);
        }

        $result_to = MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($operation, $request->materials_to, 4, 'inactivity');

        if ($result_to !== true) {
            return response()->json(['message' => $result_to]);
        }

        DB::commit();

        return response()->json(true);
    }
}
