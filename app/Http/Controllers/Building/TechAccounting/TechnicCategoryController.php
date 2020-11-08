<?php

namespace App\Http\Controllers\Building\TechAccounting;

use App\Http\Requests\Building\TechAccounting\StoreTechnicCategoryRequest;
use App\Http\Requests\Building\TechAccounting\UpdateTechnicCategoryRequest;
use App\Models\TechAcc\TechnicCategory;
use App\Services\TechAccounting\TechnicCategoryService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class TechnicCategoryController extends Controller
{
//resource methods
    public function index()
    {
        $technic_categories = TechnicCategory::with('category_characteristics')
            ->withCount('technics')
            ->withCount('free_technics')
            ->get();

        return view('tech_accounting.technics.index', [
            'technic_categories' => $technic_categories,
        ]);
    }

    public function show($category_id)
    {
        $category = TechnicCategory::with('category_characteristics')->findOrFail($category_id);

        return view('tech_accounting.technics.category_card', [
            'category' => $category,
        ]);
    }


    public function create()
    {
        if (auth()->user()->cannot('tech_acc_tech_category_create')) return redirect()->back();

        return view('tech_accounting.technics.create_category');
    }

    public function store(StoreTechnicCategoryRequest $request)
    {
        $category_service = new TechnicCategoryService();
        $category_service->createTechnicCategoryWithCharacteristics($request->all());

        return \GuzzleHttp\json_encode([
            'result' => 'success',
            'redirect' => route('building::tech_acc::technic_category.index'),
        ]);
    }

    public function edit($technic_category_id)
    {
        if (auth()->user()->cannot('tech_acc_tech_category_update')) return redirect()->back();
        $technic_category = TechnicCategory::with('category_characteristics')->findOrFail($technic_category_id);

        return view('tech_accounting.technics.edit_category', [
            'technic_category' => $technic_category,
        ]);
    }

    public function update(UpdateTechnicCategoryRequest $request, $technic_category_id)
    {
        if (auth()->user()->cannot('tech_acc_tech_category_update')) return redirect()->back();
        $category_service = new TechnicCategoryService();
        $category_service->updateTechnicCategoryWithCharacteristics($request->all(), $technic_category_id);

        return \GuzzleHttp\json_encode([
            'result' => 'success',
            'redirect' => route('building::tech_acc::technic_category.index'),
        ]);
    }

    public function destroy(TechnicCategory $technicCategory)
    {
        if (auth()->user()->cannot('tech_acc_tech_category_delete')) return response()->json(false);

        DB::beginTransaction();

        $technicCategory->delete();

        DB::commit();

        return \GuzzleHttp\json_encode([
            'result' => 'success',
        ]);
    }

    public function display_trashed()
    {
        $technic_categories = TechnicCategory::onlyTrashed()->with('category_characteristics')
            ->withCount('trashed_technics')
            ->get();

        return view('tech_accounting.technics.trashed_index', [
            'technic_categories' => $technic_categories,
        ]);
    }

    public function show_trashed($category_id)
    {
        $category = TechnicCategory::onlyTrashed()->with('category_characteristics')->findOrFail($category_id);

        return view('tech_accounting.technics.trashed_category_card', [
            'category' => $category,
        ]);
    }
}
