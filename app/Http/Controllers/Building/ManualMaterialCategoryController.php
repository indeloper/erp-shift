<?php

namespace App\Http\Controllers\Building;

use App\Http\Controllers\Controller;
use App\Http\Requests\ManualRequests\CategoryRequest;
use App\Models\Manual\ManualMaterialCategory;
use App\Models\Manual\ManualMaterialCategoryAttribute;
use App\Models\Manual\ManualMaterialParameter;
use App\Models\Manual\ManualReference;
use App\Models\Manual\ManualReferenceParameter;
use App\Models\MatAcc\MaterialAccountingOperationMaterials;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManualMaterialCategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = ManualMaterialCategory::with('attributes', 'documents');

        if ($request->search) {
            $categories = $categories->where('id', 'like', '%' . $request->search . '%')
                ->orWhere('name', 'like', '%' . $request->search . '%');
        }

        return view('building.materials.index')->with([
            'categories' => $categories->get()
        ]);
    }

    public function store(CategoryRequest $request)
    {
        DB::beginTransaction();
        $category = new ManualMaterialCategory($request->all());
        $category->save();
        foreach ($request->attrs as $attr) {
            if ($attr['id'] < 4) {
                $attr['is_preset'] = 1;
            } else {
                $attr['is_preset'] = 0;
            }
            $attr['category_id'] = $category->id;
            $attirbute = new ManualMaterialCategoryAttribute($attr);
            $attirbute->save();
        }

        $category->attachFiles(explode(',', $request->file_ids) ?? []);

        DB::commit();

        return back();
    }


    public function update(CategoryRequest $request)
    {
        DB::beginTransaction();
        $category = ManualMaterialCategory::findOrFail($request->id);
        $category->name = $request->name;
        $category->description = $request->description;
        $category->category_unit = $request->category_unit;
        $category->formula = $request->formula;

        $category->save();

        $old_attrs = ManualMaterialCategoryAttribute::where('category_id', $request->id)->pluck('id')->toArray();
        $saved_attrs_id = [];

        foreach ($request->attrs as $attr) {
            $saved_attrs_id[] = $attr['id'];
        }

        $ids_to_remove = array_diff($old_attrs, $saved_attrs_id);

        if (!empty($ids_to_remove)) {
            $category->attributes()->whereIn('id', $ids_to_remove)->delete();
//            ManualMaterialParameter::whereIn('attr_id', $ids_to_remove)->delete();
        }

        $new_attrs = [];

        $new_attrs_id = array_diff(collect($request->attrs)->pluck('id')->toArray(), $old_attrs);
        foreach ($request->attrs as $attr) {
            if (in_array($attr['id'], $new_attrs_id)) {
                $new_attrs[] = $attr;
            }
        }

        if (!empty($new_attrs)) {
            $mat_ids = ManualMaterialCategory::where('id', $category->id)->with('materials')->first()->materials->pluck('id')->toArray();

            foreach ($new_attrs as $attr) {
                if ($attr['id'] < 4) {
                    $attr['is_preset'] = 1;
                    $attr['is_display'] = 1;
                } else {
                    $attr['is_preset'] = 0;
                    $attr['is_display'] = 1;
                }
                $attr['category_id'] = $category->id;
                $attribute = new ManualMaterialCategoryAttribute($attr);
                $attribute->save();

                foreach ($mat_ids as $mat_id) {
                    $parameter = new ManualMaterialParameter();
                    $parameter->mat_id = $mat_id;
                    $parameter->attr_id = $attribute->id;
                    $parameter->value = 'null';
                    $parameter->save();
                }
            }
        }

        $category->attachFiles(explode(',', $request->file_ids) ?? []);

        DB::commit();

        return back();
    }

    public function clone(CategoryRequest $request)
    {
        DB::beginTransaction();
        $category = new ManualMaterialCategory($request->all());
        $category->save();

        foreach ($request->attrs as $attr) {
            if ($attr['id'] < 4) {
                $attr['is_preset'] = 1;
            } else {
                $attr['is_preset'] = 0;
            }
            $attr['category_id'] = $category->id;
            $attirbute = new ManualMaterialCategoryAttribute($attr);
            $attirbute->save();
        }

        DB::commit();

        return back();
    }

    public function delete(Request $request)
    {
        $cat = ManualMaterialCategory::where('id', $request->category_id)->first();

        foreach ($cat->materials as $mat) {
            $mat->parameters()->delete();
        }
        $cat->materials()->delete();
        $cat->attributesAll()->delete();
        $test = $cat->delete();

        return \GuzzleHttp\json_encode($test + 1);
    }

    public function getNeedAttributes(Request $request)
    {
        $category = ManualMaterialCategory::find($request->category_id);

        return response()->json(['attrs' => $category->needAttributes(), 'unit_show' => MaterialAccountingOperationMaterials::flipUnit($category->unit_show) . '']);
    }

    public function getNeedAttributesValues(Request $request)
    {
        if ($request->attribute_id == 'etalon') {
            $references = ManualReference::where('category_id', $request->category_id)->take(50);

            if ($request->q) {
                $references->where('name', 'like', '%' . trim($request->q) . '%');
            }

            return $references->get(['id', 'name']);

        } else {
            $items = [];
            $parameters = ManualReferenceParameter::where('attr_id', $request->attribute_id);

            if ($request->q) {
                $parameters->where('value', 'like', '%' . trim($request->q) . '%');
            }

            $parameters = $parameters->get('value')->unique();

            foreach ($parameters as $parameter) {
                $items[] = ['id' => $parameter->value, 'name' => $parameter->value];
            }

        }

        return response()->json($items);
    }
}
