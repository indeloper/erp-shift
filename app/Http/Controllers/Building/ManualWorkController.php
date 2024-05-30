<?php

namespace App\Http\Controllers\Building;

use App\Http\Controllers\Controller;
use App\Http\Requests\ManualRequests\WorkRequest;
use App\Models\Manual\ManualCopiedWorks;
use App\Models\Manual\ManualMaterial;
use App\Models\Manual\ManualMaterialCategory;
use App\Models\Manual\ManualMaterialCategoryAttribute;
use App\Models\Manual\ManualMaterialCategoryRelationToWork;
use App\Models\Manual\ManualMaterialParameter;
use App\Models\Manual\ManualRelationMaterialWork;
use App\Models\Manual\ManualWork;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ManualWorkController extends Controller
{
    public function index(Request $request): View
    {
        $onlyTrashed = $request->deleted ?? false;

        $works = ManualWork::query();

        if ($request->search) {
            $works->where('name', 'like', '%'.$request->search.'%');
        }

        if ($onlyTrashed) {
            $works->onlyTrashed();
        }

        return view('building.works.work_index', [
            'works' => $works->paginate(15),
            'work_groups' => (new ManualWork())->work_group,
        ]);
    }

    public function store(WorkRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        $work = new ManualWork();

        $work->work_group_id = $request->work_group;
        $work->name = $request->name;
        $work->description = $request->description;
        $work->price_per_unit = $request->price_per_unit;
        $work->unit = $request->unit;
        $work->unit_per_days = $request->unit_per_days;
        $work->nds = $request->nds;
        $work->show_materials = $request->show_materials ?: 0;

        if ($request->copy) {
            $work->is_copied = 1;
            $work->save();

            $have_parent = ManualCopiedWorks::where('child_work_id', $request->copy_id)->first();
            // if work have parent, we create relation for parent
            $copied_relation = new ManualCopiedWorks();

            $copied_relation->parent_work_id = $have_parent ? $have_parent->parent_work_id : $request->copy_id;
            $copied_relation->child_work_id = $work->id;

            $copied_relation->save();
        } else {
            $work->save();
        }

        DB::commit();

        return redirect()->back();
    }

    public function update(WorkRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        $work = ManualWork::find($request->work_id);

        $work->work_group_id = $request->work_group;
        $work->name = $request->name;
        $work->description = $request->description;
        $work->price_per_unit = $request->price_per_unit;
        $work->unit = $request->unit;
        $work->unit_per_days = $request->unit_per_days;
        $work->nds = $request->nds;
        $work->show_materials = $request->show_materials ? 1 : 0;

        $work->save();

        DB::commit();

        return redirect()->back();
    }

    public function type(Request $request, $id): View
    {
        $onlyTrashed = $request->deleted ?? false;

        $works = ManualWork::where('work_group_id', $id);

        if ($request->search) {
            $works->where('name', 'like', '%'.$request->search.'%');
        }

        if ($onlyTrashed) {
            $works->onlyTrashed();
        }

        return view('building.works.work_index', [
            'works' => $works->paginate(15),
            'work_groups' => (new ManualWork())->work_group,
        ]);
    }

    public function delete(Request $request)
    {
        abort_if(! Auth::user()->can('works_remove'), 403);

        DB::beginTransaction();

        $work = ManualWork::findOrFail($request->id);
        $work->material_relations()->delete();
        ! $work->is_copied ?: ManualCopiedWorks::where('child_work_id', $request->id)->delete();

        if ($work->is_parent()) {
            $work->delete_childs();
        }

        $work->delete();

        DB::commit();

        return \GuzzleHttp\json_encode(true);
    }

    public function restore(Request $request)
    {
        abort_if(! Auth::user()->can('works_remove'), 403);

        DB::beginTransaction();

        $work = ManualWork::onlyTrashed()->findOrFail($request->id);
        $work->materialRelationsClear()->restore();
        ! $work->is_copied ?: ManualCopiedWorks::where('child_work_id', $request->id)->withTrashed()->restore();

        if ($work->is_parent()) {
            ManualWork::whereIn('id', $this->childs()->withTrashed()->pluck('child_work_id')->toArray())->restore();
            $this->childs()->withTrashed()->restore();
        }

        $work->restore();

        DB::commit();

        return \GuzzleHttp\json_encode(true);
    }

    public function card(Request $request, $id): View
    {
        $work = ManualWork::findOrFail($id)->load('parent.parent_work');

        $materials = ManualRelationMaterialWork::where('manual_work_id', $work->is_copied ? $work->parent->parent_work->id : $work->id)
            ->leftJoin('manual_materials', 'manual_materials.id', '=', 'manual_relation_material_works.manual_material_id')
            ->leftJoin('manual_material_categories', 'manual_material_categories.id', '=', 'manual_materials.category_id')
            ->select('manual_relation_material_works.*', 'manual_materials.*', 'manual_material_categories.name as category_name', 'manual_material_categories.id as category_id');

        $categories = ManualMaterialCategory::whereIn('id', $materials->get()->pluck('category_id')->unique())->get();

        if ($request->search) {
            $materials->where('manual_materials.name', 'like', '%'.$request->search.'%');
        }

        return view('building.works.work_card', [
            'work' => $work,
            'materials' => $materials->take(50)->get(),
            'categories' => $categories,
        ]);
    }

    public function get_materials(Request $request): JsonResponse
    {
        $material = ManualMaterial::where('id', $request->mat_id)
            ->with('parameters')->with('work_relations')
            ->first();

        return response()->json($material);
    }

    public function get_attributes(Request $request): JsonResponse
    {
        $attrs = ManualMaterialCategoryAttribute::where('category_id', $request->id)->get();

        return response()->json($attrs);
    }

    public function get_values(Request $request): JsonResponse
    {
        if ($request->edit == 1) {
            $values = ManualMaterialParameter::where('attr_id', $request->id)->get();
            $unique_values = array_unique($values->pluck('value')->toArray());
        } else { /*if ($request->edit == 0)*/
            $values = ManualMaterialParameter::/*whereIn('mat_id', $request->materials_id)->*/ where('attr_id', $request->id)->get();
            $unique_values = array_unique($values->pluck('value')->toArray());
        }

        return response()->json($unique_values);
    }

    public function edit(Request $request, $id): View
    {
        $work = ManualWork::findOrFail($id);

        if ($work->is_copied) {
            abort(403);
        }

        $materials = ManualMaterial::query()
            ->with(['parameters', 'work_relations' => function ($query) use ($id) {
                $query->where('manual_work_id', $id);
            }])
            ->leftJoin('manual_material_categories', 'manual_material_categories.id', '=', 'manual_materials.category_id')
            ->select('manual_materials.*', 'manual_material_categories.name as category_name');

        if ($request->show_all != 1) {
            $materials = $materials->whereHas('work_relations', function ($query) use ($id) {
                $query->where('manual_work_id', $id);
            });

            $categories = ManualMaterialCategory::whereIn('id', $materials->take(50)->get()->pluck('category_id')->unique())->get();
        } else {
            $categories = ManualMaterialCategory::all();
        }

        if ($request->search) {
            $materials->where('manual_materials.name', 'like', '%'.$request->search.'%');
        }

        return view('building.works.work_card_edit', [
            'work' => $work,
            'materials' => $materials->take(50)->get()->unique(),
            'categories' => $categories,
        ]);
    }

    public function select_material(Request $request)
    {
        DB::beginTransaction();

        $material = ManualMaterial::findOrFail($request->manual_material_id);

        if ($request->is_checked == 'true') {
            $check = ManualRelationMaterialWork::query()->where('manual_work_id', $request->manual_work_id)->where('manual_material_id', $request->manual_material_id)->first();
            if (! $check) {
                ManualRelationMaterialWork::create([
                    'manual_work_id' => $request->manual_work_id,
                    'manual_material_id' => $request->manual_material_id,
                ]);

                ManualMaterialCategoryRelationToWork::firstOrCreate([
                    'work_id' => $request->manual_work_id,
                    'manual_material_category_id' => $material->category_id,
                ]);
            }
        } else {
            $category = ManualMaterialCategoryRelationToWork::query()
                ->where('work_id', $request->manual_work_id)
                ->where('manual_material_category_id', $material->category_id)
                ->count();

            if ($category == 1) {
                ManualMaterialCategoryRelationToWork::query()
                    ->where('work_id', $request->manual_work_id)
                    ->where('manual_material_category_id', $request->category_id)
                    ->delete();
            }
            $deleted = ManualRelationMaterialWork::query()
                ->where('manual_work_id', $request->manual_work_id)
                ->where('manual_material_id', $request->manual_material_id)
                ->delete();
        }

        DB::commit();
    }

    public function search_by_attributes(Request $request): JsonResponse
    {
        $step = ManualMaterialParameter::query();

        foreach ($request->attr_id as $key => $attr) {
            $step->orWhere(function ($q) use ($request, $attr) {
                $q->where('attr_id', $attr)->whereIn('value', $request->values);
            });
        }

        $a = array_count_values($step->get()->pluck('mat_id')->toArray());
        arsort($a);

        if ($request->edit == 0) {
            $a = array_count_values($step->whereIn('mat_id', $request->materials_id)->get()->pluck('mat_id')->toArray());
            arsort($a);
        }

        foreach ($a as $step) {
            if (count($request->attr_id) > $step) {
                if (($key = array_search($step, $a)) !== false) {
                    unset($a[$key]);
                }
            }
        }

        $result = ManualMaterial::whereIn('manual_materials.id', array_keys($a))->with('parameters')
            ->leftJoin('manual_material_categories', 'manual_material_categories.id', '=', 'manual_materials.category_id')
            ->select('manual_materials.*', 'manual_material_categories.name as category_name', 'manual_material_categories.id as category_id')->get()->take(50);

        return response()->json($result);
    }

    public function get_all_materials(Request $request): JsonResponse
    {
        $work = ManualWork::findOrFail($request->id);

        $materials = ManualRelationMaterialWork::where('manual_work_id', $work->is_copied ? $work->parent->id : $work->id)
            ->leftJoin('manual_materials', 'manual_materials.id', '=', 'manual_relation_material_works.manual_material_id')
            ->leftJoin('manual_material_categories', 'manual_material_categories.id', '=', 'manual_materials.category_id')
            ->select('manual_relation_material_works.*', 'manual_materials.*', 'manual_material_categories.name as category_name', 'manual_material_categories.id as category_id');

        return response()->json($materials->get());
    }
}
