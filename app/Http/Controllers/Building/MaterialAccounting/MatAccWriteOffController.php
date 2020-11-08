<?php

namespace App\Http\Controllers\Building\MaterialAccounting;

use App\Events\NotificationCreated;

use App\Http\Controllers\Controller;
use App\Http\Requests\Building\MaterialAccounting\CreateWriteOffRequest;

use App\Http\Requests\Building\MaterialAccounting\SendWriteOffRequest;
use App\Models\Group;
use App\Models\MatAcc\MaterialAccountingOperation;
use App\Models\MatAcc\MaterialAccountingOperationMaterials;
use App\Models\MatAcc\MaterialAccountingOperationResponsibleUsers;
use App\Models\MatAcc\MaterialAccountingOperationFile;
use App\Models\MatAcc\MaterialAccountingMaterialFile;
use App\Models\Notification;
use App\Models\ProjectObject;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

use App\Traits\TimeCalculator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MatAccWriteOffController extends Controller
{
    use TimeCalculator;

    public function create(Request $request)
    {
        $from_resp = User::find($request->resp);
        $from_obj = ProjectObject::find($request->obj);

        return view('building.material_accounting.write_off.create', [
            'units' => MaterialAccountingOperationMaterials::$main_units,
            'resp' => $from_resp,
            'obj' => $from_obj,
        ]);
    }

    public function work($operation_id)
    {
        $operation = MaterialAccountingOperation::where('type', 2)->where('status', 1)->findOrFail($operation_id);
        $operation->checkClosed();
        $operation->load(['object_from', 'object_to', 'author', 'sender', 'recipient', 'responsible_user', 'responsible_user.user', 'materials.manual', 'images_sender', 'documents_sender', 'images_recipient', 'documents_recipient', 'materialsPartFrom.materialFiles', 'materialsPartFrom.materialAddition.user']);

        return view('building.material_accounting.write_off.work', [
            'operation' => $operation
        ]);
    }

    public function confirm($operation_id)
    {
        $operation = MaterialAccountingOperation::where('type', 2)->where('status', 2)->findOrFail($operation_id);
        $operation->checkClosed();
        $operation->load(['object_from', 'object_to', 'author', 'sender', 'recipient', 'responsible_user', 'responsible_user.user', 'materials.manual', 'images_sender', 'documents_sender', 'images_recipient', 'documents_recipient']);

        return view('building.material_accounting.write_off.confirm', [
            'operation' => $operation
        ]);
    }

    public function complete($operation_id)
    {
        $operation = MaterialAccountingOperation::where('type', 2)->whereIn('status', [3, 7])->findOrFail($operation_id);
        $operation->load(['object_from', 'object_to', 'author', 'sender', 'recipient', 'materials.manual', 'images_sender', 'documents_sender', 'images_recipient', 'documents_recipient']);

        return view('building.material_accounting.write_off.complete', [
            'operation' => $operation
        ]);
    }

    public function conflict($operation_id)
    {
        $operation = MaterialAccountingOperation::where('type', 2)->where('status', 4)->findOrFail($operation_id);
        $operation->checkClosed();
        $operation->load(['object_from', 'object_to', 'author', 'sender', 'recipient', 'materials.manual', 'images_sender', 'documents_sender', 'images_recipient', 'documents_recipient']);

        return view('building.material_accounting.write_off.conflict', [
            'operation' => $operation
        ]);
    }

    public function edit($operation_id)
    {
        $operation = MaterialAccountingOperation::where('type', 2)->whereIn('status', [1, 4])->findOrFail($operation_id);
        $operation->checkClosed();
        $operation->load(['object_from', 'object_to', 'author', 'sender', 'recipient', 'responsible_user', 'responsible_user.user', 'materials.manual', 'images_sender', 'documents_sender', 'images_recipient', 'documents_recipient']);

        return view('building.material_accounting.write_off.update', [
            'operation' => $operation,
            'units' => MaterialAccountingOperationMaterials::$main_units,
            // required variable for update section
            'edit_restrict' => false
        ]);
    }

    public function draft($operation_id)
    {
        $operation = MaterialAccountingOperation::where('type', 2)->whereIn('status', [5, 8])->findOrFail($operation_id);
        $operation->checkClosed();
        $operation->load(['object_from', 'object_to', 'author', 'sender', 'recipient', 'responsible_user', 'responsible_user.user', 'materials.manual', 'images_sender', 'documents_sender', 'images_recipient', 'documents_recipient']);

        return view('building.material_accounting.write_off.update', [
            'operation' => $operation,
            'units' => MaterialAccountingOperationMaterials::$main_units,
            // operation author can't do anything in controlled operation
            'edit_restrict' => (Auth::id() == 7) ? false : ($operation->status == 8) ? true : false
        ]);
    }

    public function store(CreateWriteOffRequest $request)
    {
        if (!Auth::user()->can('mat_acc_write_off_create') && !$request->is_draft) {
            return response()->json(['message' => 'У вас нет прав для создания операции списания!']);
        } elseif ($request->is_draft && !(Auth::user()->can('mat_acc_write_off_draft_create'))) {
            return response()->json(['message' => 'У вас нет прав для создания черновика операции списания!']);
        }

        DB::beginTransaction();

        $operation = MaterialAccountingOperation::create([
            'type' => 2,

            'object_id_from' => $request->object_id,

            'planned_date_from' => Carbon::parse($request->planned_date_to)->format('d.m.Y'),

            'author_id' => Auth::user()->id,
            'sender_id' => 0,
            'recipient_id' => 0,
            'comment_author' => $request->comment,
            'reason' => $request->reason,

            'status' =>  $request->is_draft ? 5 : 8/*1*/,
            'is_close' => 0,
            'parent_id' => $request->parent_id ?? 0,
            'responsible_RP' => $request->responsible_RP ?? null
        ]);

        $is_conflict = MaterialAccountingOperation::getModel()->checkProblem($operation, $request->materials);

        if ($is_conflict !== true and $is_conflict !== false) {
            return response()->json(['message' => $is_conflict]);
        }

        if ($is_conflict && $operation->status == 8) {
            $operation->update(['status' => 4]);
        }

        $responsible_user = new MaterialAccountingOperationResponsibleUsers();

        $responsible_user->additional_info = $request->responsible_RP ? $request->responsible_RP : [];
        $responsible_user->operation_id = $operation->id;
        $responsible_user->user_id = $request->responsible_user_id;

        $responsible_user->saveOrFail();

        $result = MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($operation, $request->materials, 3);

        if ($result !== true) {
            return response()->json(['message' => $result]);
        }

        if (! $request->is_draft) {
            // create operation control/agreement task
            Auth::id() == 13 ?: $this->createControlTask($operation);
            if ($operation->status == 8 and Auth::id() == 13) {
                $operation->status = 1;
                $operation->save();
            }
        }

        // attach files
        MaterialAccountingOperationFile::whereIn('id', array_merge($request->files_ids ?? [], $request->images_ids ?? []))
            ->where('operation_id', 0)
            ->update(['operation_id' => $operation->id]);

        $operation->generateOperationConflictNotifications();

        DB::commit();

        return response()->json(['operation_id' => $operation->id]);
    }

    public function update(CreateWriteOffRequest $request, $operation_id)
    {
        $operation = MaterialAccountingOperation::where('type', 2)->whereIn('status', [1, 4, 5, 8])->findOrFail($operation_id);
        $operation->checkClosed();

        if ((!$operation->isAuthor() and (! in_array($operation->status, [1, 5, 8]))) or ($operation->status != 5 and !Auth::user()->can('mat_acc_write_off_create'))) {
            return response()->json(['message' => 'У вас нет прав для создания операции списания!']);
        } elseif ($operation->status == 5 and Auth::user()->can('mat_acc_write_off_draft_create')) {
            // update info only logic
            $draft = true;
        }

        $oldStatus = $operation->status;
        $oldAuthor = $operation->author_id;
        DB::beginTransaction();
        $operation->author_id = Auth::user()->id;
        if ($operation->status != 4) {
            isset($draft) ?: $operation->status = 1;
        }
        $operation->object_id_from = $request->object_id;
        $operation->comment_author = $request->comment;
        $operation->reason = $request->reason;
        $operation->planned_date_from = Carbon::parse($request->planned_date_to)->format('d.m.Y');

        $is_conflict = MaterialAccountingOperation::getModel()->checkProblem($operation, $request->materials);

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
            $operation->update(['status' => $oldStatus]);
            $operation->refresh();
            $operation->generateDraftUpdateNotifications();
        }

        if ($operation->wasDraftAndUserCanCreateOperationAndNoConflictInOperation($oldStatus, $is_conflict)) {
            $operation->update(['status' => 8]);
            $operation->refresh();
            Auth::id() == 13 ?: $this->createControlTask($operation);
        }

        $operation->saveOrFail();

        if ($oldStatus == 8)
            $operation->checkControlTask();

        MaterialAccountingOperationResponsibleUsers::where('operation_id', $operation_id)->delete();

        $responsible_user = new MaterialAccountingOperationResponsibleUsers();
        // little trick here, hehe sorry
        $responsible_user->additional_info = $operation->wasDraftAndUserCanWorkOnlyWithDraftsAndNoConflictInOperation($oldStatus, $is_conflict) ? 'skip' : [];
        $responsible_user->operation_id = $operation->id;
        $responsible_user->user_id = $request->responsible_user_id;

        $responsible_user->saveOrFail();

        if ($operation->isWasDraft($oldStatus) and auth()->user()->isOperationCreator($operation->getEnglishTypeNameAttribute()))
            $operation->generateDraftAcceptNotification($oldAuthor);

        MaterialAccountingOperationMaterials::where('operation_id', $operation->id)->where('type', 3)->delete();

        $result = MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($operation, $request->materials, 3);

        if ($result !== true) {
            return response()->json(['message' => $result]);
        }

        $operation->generateOperationConflictNotifications();

        DB::commit();

        return response()->json(true);
    }

    public function send(SendWriteOffRequest $request, $operation_id)
    {
        DB::beginTransaction();

        $operation = MaterialAccountingOperation::where('type', 2)->where('status', 1)->findOrFail($operation_id);
        $operation->checkClosed();

        $result = $operation->send($request);

        DB::commit();

        return response()->json($result);
    }

    public function part_send(SendWriteOffRequest $request, $operation_id)
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

        $operation = MaterialAccountingOperation::where('type', 2)->where('status', 2)->findOrFail($operation_id);
        $operation->checkClosed();
        $operation->status = 3;
        $operation->actual_date_to = Carbon::now()->format('d.m.Y');
        $operation->recipient_id = Auth::user()->id;
        $operation->comment_to = $request->comment;
        $operation->is_close = 1;
        $operation->saveOrFail();

        $operation->generateOperationAcceptNotifications();

        $result = MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($operation, $request->materials, 2, 'inactivity');

        if ($result !== true) {
            return response()->json(['message' => $result]);
        }

        // attach files
        MaterialAccountingOperationFile::whereIn('id', array_merge($request->files_ids ?? [], $request->images_ids ?? []))
            ->where('operation_id', 0)
            ->update(['operation_id' => $operation->id]);

        DB::commit();

        return response()->json(true);
    }

    /**
     * Operation control task card
     * @param int $id
     * @return void
     */
    public function control($id)
    {
        $task = Task::findOrFail($id);

        $operation = MaterialAccountingOperation/*->where('status', 8)*/::findOrFail($task->target_id);
        $operation->load(['author', 'materials.manual']);

        return view('tasks.write_off_control', [
           'task' => $task,
           'operation' => $operation
        ]);
    }

    /**
     * Create operation control task
     * @param MaterialAccountingOperation $operation
     * @return void
     */
    public function createControlTask(MaterialAccountingOperation $operation)
    {
        DB::beginTransaction();

        $task = Task::create([
            'name' => 'Контроль списания',
            'responsible_user_id' => 13, //stinky place
            'target_id' => $operation->id,
            'expired_at' => $this->addHours(24),
            'status' => 21
        ]);

        DB::commit();
    }

    /**
     * Operation control task solver
     * @param int $task_id
     * @param Request $request
     * @return mixed
     */
    public function solve_control(Request $request, $task_id)
    {
        DB::beginTransaction();

        $task = Task::whereIn('status', [21, 38])->findOrFail($task_id);
        $operation = MaterialAccountingOperation::/*->where('status', 8)*/findOrFail($task->target_id);
        $operation->checkClosed();
        $operation->load('author');

        // update task
        $task->result = $request->status_result === 'accept' ? 1 : 2;
        $task->final_note = $task->descriptions[$task->status] . $task->results[$task->status][$task->result] . ($request->description ? ' с комментарием: ' . $request->description : '');
        $task->solve_n_notify();

        // update operation
        $operation->update([
            'status' => $request->status_result === 'accept' ? 1 : 7,
            'is_close' => $request->status_result === 'accept'? 0 : 1,
        ]);

        if ($request->status_result == 'decline' && $task->status == 21) {
            //notify operation creator
            Notification::create([
                'name' => 'Ваша операция списания была отклонена' .
                    ($request->description ? ' с комментарием: ' . $request->description : ''),
                'task_id' => $task->id,
                'user_id' => $operation->author->id,
                'type' => 13,
            ]);
        }

        if ($task->status == 38) {
             Notification::create([
                'name' => 'Ваша операция была ' . $task->results[$task->status][$task->result] .
                    ($request->description ? ' с комментарием: ' . $request->description : ''),
                'task_id' => $task->id,
                'user_id' => $operation->author->id,
                'type' => $task->result == 1 ? 92 : 93,
            ]);
        }

        DB::commit();

        return redirect()->route('tasks::index');
    }
}
