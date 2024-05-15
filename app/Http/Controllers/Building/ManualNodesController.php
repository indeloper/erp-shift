<?php

namespace App\Http\Controllers\Building;

use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ManualRequests\NodeRequest;
use App\Http\Requests\ManualRequests\TypicalNodesRequest;
use App\Models\Manual\ManualMaterial;
use App\Models\Manual\ManualNodeCategories;
use App\Models\Manual\ManualNodeMaterials;
use App\Models\Manual\ManualNodes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManualNodesController extends Controller
{
    public function index(Request $request): View
    {
        $node_categories = ManualNodeCategories::query();

        if ($request->search) {
            $node_categories = $node_categories->where('id', 'like', '%'.$request->search.'%')
                ->orWhere('name', 'like', '%'.$request->search.'%');
        }

        return view('building.materials.nodes.index')->with([
            'node_categories' => $node_categories->get(),
        ]);
    }

    public function category_store(TypicalNodesRequest $request)
    {
        DB::beginTransaction();

        $node_category = new ManualNodeCategories();

        $node_category->name = $request->category_name;
        $node_category->description = $request->category_description;
        $node_category->safety_factor = $request->safety_factor;

        $node_category->save();

        DB::commit();

        return back();
    }

    public function category_update(TypicalNodesRequest $request)
    {
        DB::beginTransaction();

        $node_category = ManualNodeCategories::find($request->id);

        $node_category->name = $request->category_name;
        $node_category->description = $request->category_description;
        $node_category->safety_factor = $request->safety_factor;

        $node_category->save();

        DB::commit();

        return back();
    }

    public function category_delete(Request $request): JsonResponse
    {
        DB::beginTransaction();

        $node_category = ManualNodeCategories::find($request->category_id)->delete();
        $category_nodes = ManualNodes::where('node_category_id', $request->category_id)->get();

        if ($category_nodes != null) {
            ManualNodes::where('node_category_id', $request->category_id)->delete();
            ManualNodeMaterials::whereIn('node_id', $category_nodes->pluck('id'))->delete();
        }

        DB::commit();

        return response()->json(true);
    }

    public function view_category(Request $request, $id): View
    {
        $node_category = ManualNodeCategories::findOrFail($id);

        $nodes = $node_category->nodes()->with(['node_category', 'node_materials', 'node_materials.materials.category'])->get();

        if ($request->search) {
            $nodes = ManualNodes::where('node_category_id', $id);
            $nodes = $nodes->where('id', 'like', '%'.$request->search.'%')
                ->orWhere('name', 'like', '%'.$request->search.'%')->with(['node_category', 'node_materials', 'node_materials.materials', 'node_materials.materials.category'])->get();
        }

        // $mat_ids = ManualNodeMaterials::whereIn('node_id', $nodes->pluck('id'))->pluck('manual_material_id');
        // $manual_materials = ManualMaterial::query()->whereNotIn('category_id', [12, 14])->with('category')->get();
        $buy_cost_array = [];
        $use_cost_array = [];
        $weight_array = [];

        foreach ($nodes as $node) {
            $buy_cost = 0;
            $use_cost = 0;
            $weight = 0;

            foreach ($node->node_materials as $node_material) {
                $count = $node_material->count;
                $buy_cost += isset($node_material->materials->buy_cost) ? $node_material->materials->buy_cost * $count : 0;
                $use_cost += isset($node_material->materials->use_cost) ? $node_material->materials->buy_cost * $count : 0;

                if (isset($node_material->materials->category->category_unit)) {
                    if ($node_material->materials->category->category_unit === 'т') {
                        $weight += $count * (1 + $node->node_category->safety_factor / 100);
                    } elseif ($node_material->materials->category->category_unit !== 'т') {
                        if ($node_material->materials->parameters->where('is_preset', 1)
                            ->where(function ($q) {
                                $q->where('name', 'Масса 1 шт');
                            })->first()) {
                            $attr = str_replace(',', '.', $node_material->materials->parameters->where('is_preset', 1)->where(function ($q) {
                                $q->where('name', 'Масса 1 шт');
                            })->first()->value);
                            $weight += $attr * $count * (1 + $node->node_category->safety_factor / 100);
                        }
                    }
                }
            }

            $buy_cost_array[] = $buy_cost;
            $use_cost_array[] = $use_cost;
            $weight_array[] = isset($weight) ? $weight : 0;
        }

        return view('building.materials.nodes.view')->with([
            'nodes' => $nodes,
            'node_category' => $node_category,
            // 'materials' => $manual_materials,
            'buy_cost' => $buy_cost_array,
            'use_cost' => $use_cost_array,
            'weight' => $weight_array,
        ]);
    }

    public function store(NodeRequest $request)
    {
        DB::beginTransaction();

        $node = new ManualNodes();

        $node->node_category_id = $request->node_category_id;
        $node->name = $request->node_name;
        $node->description = $request->node_description;
        $node->is_compact_wv = isset($request->is_compact_wv) ? $request->is_compact_wv : 0;
        $node->is_compact_cp = isset($request->is_compact_cp) ? $request->is_compact_cp : 0;

        $node->save();

        if ($request->has('materials')) {
            foreach ($request->materials as $key => $material) {
                ManualNodeMaterials::create([
                    'node_id' => $node->id,
                    'manual_material_id' => $material,
                    'count' => $request->count[$key],
                ]);
            }
        }

        DB::commit();

        return back();
    }

    public function update(NodeRequest $request)
    {
        DB::beginTransaction();

        $node = ManualNodes::find($request->node_id);

        $node->node_category_id = $request->node_category_id;
        $node->name = $request->node_name;
        $node->description = $request->node_description;
        $node->is_compact_wv = isset($request->is_compact_wv) ? $request->is_compact_wv : 0;
        $node->is_compact_cp = isset($request->is_compact_cp) ? $request->is_compact_cp : 0;

        $node->save();

        if ($request->has('materials')) {
            ManualNodeMaterials::where('node_id', $node->id)->delete();
            foreach ($request->materials as $key => $material) {
                ManualNodeMaterials::create([
                    'node_id' => $node->id,
                    'manual_material_id' => $material,
                    'count' => $request->count[$key],
                ]);
            }
        }

        DB::commit();

        return back();
    }

    public function clone(NodeRequest $request)
    {
        DB::beginTransaction();

        $node = new ManualNodes();

        $node->node_category_id = $request->node_category_id;
        $node->name = $request->node_name;
        $node->description = $request->node_description;

        $node->save();

        if ($request->has('materials')) {
            foreach ($request->materials as $key => $material) {
                ManualNodeMaterials::create([
                    'node_id' => $node->id,
                    'manual_material_id' => $material,
                    'count' => $request->count[$key],
                ]);
            }
        }

        DB::commit();

        return back();
    }

    public function delete(Request $request): JsonResponse
    {
        DB::beginTransaction();

        $node = ManualNodes::find($request->node_id)->delete();
        $node_materials = ManualNodeMaterials::where('node_id', $request->node_id)->delete();

        DB::commit();

        return response()->json(true);
    }

    public function get_materials(Request $request)
    {
        $wv_materials = ManualMaterial::query();

        if ($request->q) {
            $wv_materials = $wv_materials->where('manual_materials.name', 'like', '%'.trim($request->q).'%');
        }

        $wv_materials = $wv_materials->take(50)->get();

        $results = [];
        foreach ($wv_materials as $wv_material) {
            $results[] = [
                'id' => $wv_material->id,
                'text' => $wv_material->name,
            ];
        }

        return ['results' => $results];
    }
}
