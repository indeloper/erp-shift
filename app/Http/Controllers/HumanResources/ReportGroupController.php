<?php

namespace App\Http\Controllers\HumanResources;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportGroupRequests\{ReportGroupDestroyRequest,
    ReportGroupStoreRequest,
    ReportGroupUpdateRequest};
use App\Services\AuthorizeService;
use App\Traits\AdditionalFunctions;
use App\Models\HumanResources\{JobCategory, ReportGroup};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportGroupController extends Controller
{
    use AdditionalFunctions;

    public function index(Request $request)
    {
        (new AuthorizeService())->authorizeReportGroupsIndex();

        $newRequest = $this->createNewRequest($request->toArray());
        $reportGroups = ReportGroup::filter($newRequest)->withCount('jobCategories')->orderBy('updated_at')->paginate(15);

        return view('human_resources.report_groups.index', [
            'data' => [
                'report_groups' => $reportGroups->items(),
                'report_groups_count' => $reportGroups->total(),
            ],
        ]);
    }

    public function create()
    {
        return view('human_resources.report_groups.create');
    }

    public function store(ReportGroupStoreRequest $request)
    {
        DB::beginTransaction();

        $reportGroup = ReportGroup::create($request->all());
        $reportGroup->jobCategories()->saveMany(JobCategory::whereIn('id', $request->job_categories ?? [])->get());

        DB::commit();

        return [
            'result' => 'success',
            'redirect' => route('human_resources.report_group.show', $reportGroup->id),
        ];
    }

    public function show(ReportGroup $reportGroup)
    {
        (new AuthorizeService())->authorizeReportGroupsShow();

        return view('human_resources.report_groups.show', [
            'data' => [
                'report_group' => $reportGroup,
                'job_categories' => $reportGroup->jobCategories->load('users'),
            ],
        ]);
    }

    public function edit(ReportGroup $reportGroup)
    {
        return view('human_resources.report_groups.edit', [
            'data' => [
                'report_group' => $reportGroup,
                'job_categories' => $reportGroup->jobCategories->load('users'),
            ]
        ]);
    }

    public function update(ReportGroupUpdateRequest $request, ReportGroup $reportGroup)
    {
        DB::beginTransaction();

        $reportGroup->update($request->all());
        $reportGroup->updateJobCategories($request->all());

        DB::commit();

        return [
            'result' => 'success',
            'redirect' => route('human_resources.report_group.show', $reportGroup->id),
        ];
    }

    public function destroy(ReportGroupDestroyRequest $request, ReportGroup $reportGroup)
    {
        DB::beginTransaction();

        $reportGroup->delete();

        DB::commit();

        return response()->json(true);
    }

    public function getGroups(Request $request)
    {
        $reportGroupsArray = [];
        $reportGroups = ReportGroup::query();

        if ($request->q) {
            $reportGroups->where('name', 'like', '%' . $request->q . '%');
        }

        foreach ($reportGroups->get() as $reportGroup) {
            $reportGroupsArray[] = ['id' => $reportGroup->id . '', 'name' => $reportGroup->name];
        }

        return response()->json($reportGroupsArray);
    }

    public function getGroupsPaginated(Request $request)
    {
        $output = [];
        parse_str(parse_url($request->url)['query'] ?? '', $output);
        $newRequest = $this->createNewRequest($output);

        $result = ReportGroup::filter($newRequest)->withCount('jobCategories')->orderBy('updated_at')->paginate(15);

        return response()->json([
            'data' => [
                'report_groups' => $result->items(),
                'report_groups_count' => $result->total(),
            ],
        ]);
    }
}
