<?php

namespace App\Http\Controllers\Building\TechAccounting\Technic\old;

use App\Http\Controllers\Controller;
use App\Http\Requests\DefectRequests\DefectAcceptRequest;
use App\Http\Requests\DefectRequests\DefectDeclineRequest;
use App\Http\Requests\DefectRequests\DefectRepairEndRequest;
use App\Http\Requests\DefectRequests\DefectResponsibleUserAssignmentRequest;
use App\Http\Requests\DefectRequests\DefectStoreRequest;
use App\Models\FileEntry;
use App\Models\TechAcc\Defects\Defects;
use App\Models\TechAcc\OurTechnic;
use App\Services\AuthorizeService;
use App\Traits\AdditionalFunctions;
use App\Traits\NotificationGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DefectsController extends Controller
{
    use AdditionalFunctions, NotificationGenerator;

    public function index(Request $request): View
    {
        $newRequest = $this->createNewRequest($request->toArray());
        $defects = Defects::filter($newRequest)->permissionCheck()->orderBy('updated_at')->paginate(15);

        return view('tech_accounting.defects.index', [
            'data' => [
                'owners' => OurTechnic::$owners,
                'defects' => $defects->items(),
                'defects_count' => $defects->total(),
                'statuses' => Defects::STATUSES,
            ],
        ]);
    }

    public function store(DefectStoreRequest $request): JsonResponse
    {
        DB::beginTransaction();

        $defect = Defects::smartCreate($request->all());
        $defect->documents()->saveMany(FileEntry::find($request->file_ids) ?? []);
        $this->generateDefectCreateNotification($defect);

        DB::commit();

        return response()->json([
            'result' => 'success',
            'redirect' => $defect->card_route(),
        ]);
    }

    public function show(Defects $defect): View
    {
        return view('tech_accounting.defects.card', [
            'data' => [
                'defect' => $defect,
                'tasks' => $defect->active_tasks()
                    ->whereResponsibleUserId(auth()->id())
                    ->with('responsible_user')
                    ->get(),
            ],
        ]);
    }

    public function destroy(Defects $defect): JsonResponse
    {
        (new AuthorizeService())->authorizeDefectDelete($defect);

        $defect->update(['status' => Defects::DELETED]);
        $defect->solveActiveTasks();
        $this->generateDefectDeleteNotification($defect);
        $defect->comments()->create([
            'comment' => "@user({$defect->author->id}) удалил заявку на неисправность.",
            'author_id' => auth()->id(),
            'system' => 1,
        ]);

        return response()->json(true);
    }

    public function select_responsible(DefectResponsibleUserAssignmentRequest $request, Defects $defects)
    {
        DB::beginTransaction();

        if ($defects->responsible_user_id) {
            return redirect($defects->card_route());
        }

        $defects->update(['responsible_user_id' => $request->user_id, 'status' => Defects::DIAGNOSTICS]);
        $defects->solveActiveTasks();
        $defects->refresh();
        $defects->comments()->create([
            'comment' => '@user('.(auth()->id() ?? $defects->responsible_user->id).') назначен исполнителем на заявку.',
            'author_id' => auth()->id(),
            'system' => 1,
        ]);

        $this->generateDefectResponsibleUserStoreNotification($defects);

        $next_task = $defects->tasks()->create([
            'name' => 'Контроль неисправности техники',
            'responsible_user_id' => $request->user_id,
            'status' => 33,
            'expired_at' => $this->addHours(8),
        ]);

        $this->generateDefectControlTaskNotification($next_task);

        DB::commit();

        return $request->wantsJson() ? response()->json(true) : redirect($defects->card_route());
    }

    public function decline(DefectDeclineRequest $request, Defects $defects): JsonResponse
    {
        if ($defects->isNotInDiagnostics()) {
            return response()->json(false);
        }

        DB::beginTransaction();

        $defects->update(['status' => $defects::DECLINED]);
        $defects->solveActiveTasks();
        $defects->comments()->create([
            'comment' => '@user('.(auth()->id() ?? $defects->responsible_user->id).") отклонил заявку на дефект. Комментарий: {$request->comment}",
            'author_id' => auth()->id(),
            'system' => 1,
        ]);

        $this->generateDefectDeclineNotification($defects);

        DB::commit();

        return response()->json(true);
    }

    public function accept(DefectAcceptRequest $request, Defects $defects): JsonResponse
    {
        if ($defects->isNotInDiagnostics()) {
            return response()->json(false);
        }

        DB::beginTransaction();

        $defects->update(array_merge($request->all(), ['status' => $defects::IN_WORK]));
        $defects->solveActiveTasks();
        $defects->comments()->create([
            'comment' => '@user('.(auth()->id() ?? $defects->responsible_user->id).") подтвердил заявку на дефект. Период ремонта: с {$defects->repair_start} по {$defects->repair_end} Комментарий: {$request->comment}",
            'author_id' => auth()->id(),
            'system' => 1,
        ]);

        $this->generateDefectAcceptNotification($defects);

        $task = $defects->tasks()->create([
            'name' => 'Контроль выполнения заявки на неисправность',
            'responsible_user_id' => $defects->responsible_user_id,
            'status' => 35,
            'expired_at' => $defects->repair_end_date,
        ]);

        $this->generateDefectRepairControlTaskNotification($task);

        DB::commit();

        return response()->json(true);
    }

    public function update_repair_dates(DefectAcceptRequest $request, Defects $defects): JsonResponse
    {
        DB::beginTransaction();

        $defects->update($request->all());
        $defects->updateActiveRepairControlTask(['expired_at' => $request->repair_end_date]);
        $defects->comments()->create([
            'comment' => '@user('.(auth()->id() ?? $defects->responsible_user->id).") изменил сроки ремонта по заявке на дефект. Новый период: с {$defects->repair_start} по {$defects->repair_end}. Комментарий: {$request->comment}",
            'author_id' => auth()->id(),
            'system' => 1,
        ]);
        $this->generateDefectRepairDatesUpdateNotification($defects);

        DB::commit();

        return response()->json(true);
    }

    public function end_repair(DefectRepairEndRequest $request, Defects $defects): JsonResponse
    {
        DB::beginTransaction();

        $defects->update(array_merge($request->all(), ['status' => $defects::CLOSED]));
        $defects->defectable->update($request->merge(['object_id' => $request->start_location_id])->all());
        $defects->comments()->create([
            'comment' => '@user('.(auth()->id() ?? $defects->responsible_user->id).") завершил ремонт по заявке на дефект. Комментарий: {$request->comment}",
            'author_id' => auth()->id(),
            'system' => 1,
        ]);
        $defects->solveActiveTasks();
        $this->generateDefectRepairEndNotification($defects);

        DB::commit();

        return response()->json(true);
    }

    public function paginated_defects(Request $request): JsonResponse
    {
        $output = [];
        parse_str(parse_url($request->url)['query'] ?? '', $output);
        $newRequest = $this->createNewRequest($output);
        $filtered_defects = Defects::filter($newRequest)->permissionCheck()->orderBy('updated_at')->paginate(15);

        return response()->json([
            'data' => [
                'defects' => $filtered_defects->items(),
                'defects_count' => $filtered_defects->total(),
            ],
        ]);
    }
}
