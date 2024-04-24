<?php

namespace App\Http\Controllers\Building\MaterialAccounting;

use App\Events\OperationClosed;
use App\Http\Controllers\Controller;
use App\Http\Requests\Building\MaterialAccounting\CreateArrivalRequest;
use App\Http\Requests\Building\MaterialAccounting\SendArrivalRequest;
use App\Models\MatAcc\MaterialAccountingBase;
use App\Models\MatAcc\MaterialAccountingOperation;
use App\Models\MatAcc\MaterialAccountingOperationMaterials;
use App\Models\MatAcc\MaterialAccountingOperationResponsibleUsers;
use App\Models\ProjectObject;
use App\Models\User;
use App\Services\MaterialAccounting\MaterialAccountingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MatAccArrivalController extends Controller
{
    public function create(Request $request)
    {
        $from_resp = User::find($request->resp);
        $from_obj = ProjectObject::find($request->obj);
        $supplier = ProjectObject::find($request->supplier);

        return view('building.material_accounting.arrival.create', [
            'units' => MaterialAccountingOperationMaterials::$main_units,
            'resp' => $from_resp,
            'obj' => $from_obj,
            'supplier' => $supplier,
        ]);
    }

    public function work($operation_id)
    {
        $operation = MaterialAccountingOperation::where('type', 1)->where('status', 1)->findOrFail($operation_id);
        $operation->checkClosed();
        $operation->load(['object_from', 'object_to', 'supplier', 'author', 'sender', 'recipient', 'responsible_user', 'responsible_user.user', 'materials.manual', 'images_sender', 'documents_sender', 'images_recipient', 'documents_recipient', 'materialsPartTo.manual', 'materialsPartTo.materialFiles', 'materialsPartTo.materialAddition.user']);

        return view('building.material_accounting.arrival.work', [
            'operation' => $operation
        ]);
    }

    public function confirm($operation_id)
    {
        $operation = MaterialAccountingOperation::where('type', 1)->where('status', 2)->findOrFail($operation_id);
        $operation->checkClosed();
        $operation->load(['object_from', 'object_to', 'author', 'sender', 'recipient', 'responsible_user', 'responsible_user.user', 'materials.manual', 'images_sender', 'documents_sender', 'images_recipient', 'documents_recipient', 'materialsPartTo.materialFiles', 'materialsPartTo.materialAddition.user']);

        return view('building.material_accounting.arrival.confirm', [
            'operation' => $operation
        ]);
    }

    public function complete($operation_id)
    {
        $operation = MaterialAccountingOperation::where('type', 1)->whereIn('status', [3, 7])->findOrFail($operation_id);
        $operation->load(['object_from', 'object_to', 'author', 'sender', 'recipient', 'materials.manual', 'images_sender', 'documents_sender', 'images_recipient', 'documents_recipient', 'materialsPartTo.materialFiles', 'materialsPartTo.materialAddition.user']);

        return view('building.material_accounting.arrival.complete', [
            'operation' => $operation
        ]);
    }

    public function edit($operation_id)
    {
        $operation = MaterialAccountingOperation::where('type', 1)->where('status', 1)->findOrFail($operation_id);
        $operation->checkClosed();
        $operation->load(['object_from', 'object_to', 'author', 'sender', 'recipient', 'responsible_user', 'responsible_user.user', 'materials.manual', 'images_sender', 'documents_sender', 'images_recipient', 'documents_recipient']);

        return view('building.material_accounting.arrival.update', [
            'operation' => $operation,
            'units' => MaterialAccountingOperationMaterials::$main_units,
            'edit_restrict' => false,
        ]);
    }

    public function draft($operation_id)
    {
        $operation = MaterialAccountingOperation::where('type', 1)->whereIn('status', [5, 8])->findOrFail($operation_id);
        $operation->checkClosed();
        $operation->load(['object_from', 'object_to', 'author', 'sender', 'recipient', 'responsible_user', 'responsible_user.user', 'materials.manual', 'images_sender', 'documents_sender', 'images_recipient', 'documents_recipient']);

        return view('building.material_accounting.arrival.update', [
            'operation' => $operation,
            'units' => MaterialAccountingOperationMaterials::$main_units,
            // operation author can't do anything in controlled operation
            'edit_restrict' => (Auth::id() == 7) ? false : ($operation->status == 8) ? true : false,
        ]);
    }


    public function store(CreateArrivalRequest $request)
    {
        if ($request->without_confirm == true && Auth::user()->id == 1) {
            MaterialAccountingBase::getModel()->backdating($request->materials, Carbon::parse($request->planned_date_to), $request->object_id);
        } else {
            if (!Auth::user()->can('mat_acc_arrival_create') && !$request->is_draft) {
                return response()->json(['message' => 'У вас нет прав для создания операции поступления!']);
            } elseif ($request->is_draft && !(Auth::user()->can('mat_acc_arrival_draft_create'))) {
                return response()->json(['message' => 'У вас нет прав для создания черновика операции поступления!']);
            }

            DB::beginTransaction();

            $operation = new MaterialAccountingOperation();

            $operation->type = 1;
            $operation->object_id_to = $request->object_id;
            $operation->planned_date_to = Carbon::parse($request->planned_date_to)->format('d.m.Y');
            $operation->planned_date_from = Carbon::parse($request->planned_date_from)->format('d.m.Y');
            $operation->author_id = Auth::user()->id;
            $operation->sender_id = 0;
            $operation->recipient_id = 0;
            $operation->contract_id = $request->contract_id;
            $operation->supplier_id = $request->supplier_id;
            $operation->status = $request->is_draft ? 5 : 1;
            $operation->responsible_RP = $request->responsible_RP ?? null;
            $operation->is_close = 0;
            $operation->parent_id = $request->parent_id ?? 0;

            $operation->saveOrFail();

            $responsible_user = new MaterialAccountingOperationResponsibleUsers();

            $responsible_user->additional_info = $request->responsible_RP ? $request->responsible_RP : [];
            $responsible_user->operation_id = $operation->id;
            $responsible_user->user_id = $request->responsible_user_id;

            $responsible_user->saveOrFail();

            $result = MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($operation, $request->materials, 3);

            if ($result !== true) {
                return response()->json(['message' => $result]);
            }

            DB::commit();
        }

        return response()->json(true);
    }

    public function update(CreateArrivalRequest $request, $operation_id)
    {
        $operation = MaterialAccountingOperation::where('type', 1)->whereIn('status', [1, 5])->findOrFail($operation_id);
        $operation->checkClosed();

        if ((!$operation->isAuthor() and ($operation->status != 1 and $operation->status != 5)) or ($operation->status != 5 and !Auth::user()->can('mat_acc_arrival_create'))) {
            return response()->json(['message' => 'У вас нет прав для создания операции поступления!']);
        }

        DB::beginTransaction();

        $oldStatus = $operation->status;
        $oldAuthor = $operation->author_id;
        $operation->object_id_to = $request->object_id;
        $operation->supplier_id = $request->supplier_id;
        $operation->contract_id = $request->contract_id;
        $operation->author_id = Auth::user()->id;
        $operation->planned_date_to = Carbon::parse($request->planned_date_to)->format('d.m.Y');

        if ($operation->wasDraftAndUserCanWorkOnlyWithDraftsAndNoConflictInOperation($oldStatus, $is_conflict = false)) {
            $operation->status = $oldStatus;
            $operation->generateDraftUpdateNotifications();
        }

        if ($operation->wasDraftAndUserCanCreateOperationAndNoConflictInOperation($oldStatus, $is_conflict = false)) {
            $operation->status = 1;
            $operation->generateDraftAcceptNotification($oldAuthor);
        }

        $operation->saveOrFail();

        MaterialAccountingOperationResponsibleUsers::where('operation_id', $operation_id)->delete();

        $responsible_user = new MaterialAccountingOperationResponsibleUsers();
        // little trick here, hehe sorry
        $responsible_user->additional_info = $operation->wasDraftAndUserCanWorkOnlyWithDraftsAndNoConflictInOperation($oldStatus, $is_conflict = false) ? 'skip' : [];
        $responsible_user->operation_id = $operation->id;
        $responsible_user->user_id = $request->responsible_user_id;

        $responsible_user->saveOrFail();

        MaterialAccountingOperationMaterials::where('operation_id', $operation->id)->where('type', 3)->delete();

        $result = MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($operation, $request->materials, 3);

        if ($result !== true) {
            return response()->json(['message' => $result]);
        }
        $part_mats = MaterialAccountingOperationMaterials::where('operation_id', $operation->id)->where('type', 9)->get();

        foreach ($part_mats as $part_mat) {
            $plan_q = $part_mat->sameMaterials()->where('type', 3);
            if ($plan_q->doesntExist()) {
                if ($plan_q->withTrashed()->exists()) {
                    $plan_mat = $plan_q->first();
                    $plan_mat->restore();
                    $plan_mat->count = 0;
                    $plan_mat->save();
                } else {
                    $plan_mat = $part_mat->replicate();
                    $plan_mat->count = 0;
                    $plan_mat->type = 3;
                    $plan_mat->save();
                }
            }
        }
        DB::commit();

        return response()->json(true);
    }

    public function send(SendArrivalRequest $request, $operation_id)
    {
        DB::beginTransaction();

        $operation = MaterialAccountingOperation::where('type', 1)->where('status', 1)->findOrFail($operation_id);
        $operation->checkClosed();

        $result = $operation->send($request);

        DB::commit();

        return response()->json($result);
    }

    public function part_send(SendArrivalRequest $request, $operation_id)
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

    public function accept(Request $request, $operation_id)
    {
        DB::beginTransaction();

        $operation = MaterialAccountingOperation::where('type', 1)->where('status', 2)->findOrFail($operation_id);

        $result = (new MaterialAccountingService())->acceptOperation($operation);

        if ($result !== true) {
            return response()->json(['message' => $result]);
        }

        $resultCompare = $operation->compareMaterials();
        if ($resultCompare['status'] !== 'success') {
            return response()->json($resultCompare);
        }

        event((new OperationClosed)->withOutContract($operation));

        DB::commit();

        return response()->json(true);
    }
}
