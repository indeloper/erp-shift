<?php

namespace App\Http\Controllers\HumanResources;

use App\Http\Controllers\Controller;
use App\Models\HumanResources\TariffRates;
use App\Services\AuthorizeService;
use App\Traits\AdditionalFunctions;
use App\Http\Requests\JobCategoryRequests\{JobCategoryDestroyRequest,
    JobCategoryStoreRequest,
    JobCategoryUpdateRequest,
    JobCategoryUsersUpdateRequest};
use App\Models\HumanResources\JobCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobCategoryController extends Controller
{
    use AdditionalFunctions;

    public function index(Request $request)
    {
        (new AuthorizeService())->authorizeJobCategoriesIndex();

        $newRequest = $this->createNewRequest($request->toArray());
        $jobCategories = JobCategory::filter($newRequest)->with('reportGroup')->withCount('users')->orderBy('updated_at')->paginate(15);

        return view('human_resources.job_categories.index', [
            'data' => [
                'job_categories' => $jobCategories->items(),
                'job_categories_count' => $jobCategories->total(),
            ],
        ]);
    }

    public function create()
    {
        return view('human_resources.job_categories.create', [
            'data' => [
                'tariff_rates' => TariffRates::all()
            ]
        ]);
    }

    public function store(JobCategoryStoreRequest $request)
    {
        DB::beginTransaction();

        $jobCategory = JobCategory::create($request->all());
        $jobCategory->tariffs()->createMany($request->tariffs ?? []);

        DB::commit();

        return [
            'result' => 'success',
            'redirect' => route('human_resources.job_category.show', $jobCategory->id),
        ];
    }

    public function show(JobCategory $jobCategory)
    {
        (new AuthorizeService())->authorizeJobCategoryShow();

        return view('human_resources.job_categories.show', [
            'job_category' => $jobCategory->load('tariffs.tariff', 'users', 'reportGroup'),
        ]);
    }

    public function users(JobCategory $jobCategory)
    {
        return view('human_resources.users_wrapper', [
            'data' => [
                'job_category' => $jobCategory->load( 'users'),
                'source' => 'job_category',
            ]
        ]);
    }

    public function edit(JobCategory $jobCategory)
    {
        return view('human_resources.job_categories.edit', [
            'data' => [
                'job_category' => $jobCategory->load('tariffs.tariff'),
                'tariff_rates' => TariffRates::all()
            ]
        ]);
    }

    public function update(JobCategoryUpdateRequest $request, JobCategory $jobCategory)
    {
        DB::beginTransaction();

        $jobCategory->update($request->all());
        $jobCategory->deleteTariffs($request->deleted_tariffs ?? []);
        $jobCategory->updateTariffs($request->tariffs ?? []);

        DB::commit();

        return response()->json([
            'result' => 'success',
            'redirect' => route('human_resources.job_category.show', $jobCategory->id),
        ]);
    }

    public function updateUsers(JobCategoryUsersUpdateRequest $request, JobCategory $jobCategory)
    {
        DB::beginTransaction();

        $jobCategory->updateUsers($request->all());

        DB::commit();

        return response()->json([
            'result' => 'success',
            'redirect' => route('human_resources.job_category.show', $jobCategory->id),
        ]);
    }

    public function destroy(JobCategoryDestroyRequest $request, JobCategory $jobCategory)
    {
        DB::beginTransaction();

        $jobCategory->delete();

        DB::commit();

        return response()->json(true);
    }

    public function getCategories(Request $request)
    {
        $jobCategoriesArray = [];
        $jobCategories = JobCategory::query();

        if ($request->q) {
            $jobCategories->where('name', 'like', '%' . $request->q . '%');
        }

        foreach ($jobCategories->get() as $jobCategory) {
            $jobCategoriesArray[] = ['id' => $jobCategory->id . '', 'name' => $jobCategory->name];
        }

        return response()->json($jobCategoriesArray);
    }

    public function getCategoriesPaginated(Request $request)
    {
        $output = [];
        parse_str(parse_url($request->url)['query'] ?? '', $output);
        $newRequest = $this->createNewRequest($output);

        $result = JobCategory::filter($newRequest)->with('reportGroup')->withCount('users')->orderBy('updated_at')->paginate(15);

        return response()->json([
            'data' => [
                'job_categories' => $result->items(),
                'job_categories_count' => $result->total(),
            ],
        ]);
    }
}
