<?php

namespace App\Http\Controllers\Building;

use App\Http\Controllers\Controller;
use App\Http\Requests\ManualRequests\MaterialsRequest;
use App\Models\FileEntry;
use App\Models\Manual\ManualMaterial;
use App\Models\Manual\ManualMaterialCategory;
use App\Models\Manual\ManualMaterialPassport;
use App\Models\Manual\ManualReference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;


class ManualMaterialController extends Controller
{
    public function card(Request $request, $id)
    {
        $showMaterials = $request->materials ?? false;
        $onlyTrashed = $request->deleted ?? false;
        $without_ref = $request->without_ref ?? false;

        $materials = ManualReference::where('category_id', $id)->with([
            'parameters',
        ]);

        $category = ManualMaterialCategory::where('id', $id)->with('attributes');

        // if references is exist -> show referenses
        if ($showMaterials) {
            $materials = ManualMaterial::where('category_id', $id)->with([
                'parameters',
                'work_relations',
                'passport'
            ]);
        }

        if ($request->search) {
            $materials = $materials->where('name', 'like', '%' . $request->search . '%');
        }

        if ($onlyTrashed) {
            $materials->onlyTrashed();
        }

        if ($without_ref) {
            $materials->where('manual_reference_id', null);
        }

        return view('building.materials.card', with([
            'category' => $category->first(),
            'materials' => $materials->paginate(30),
            'className' => class_basename($materials->first()),
        ]));
    }


    public function store(MaterialsRequest $request, $id)
    {
        DB::beginTransaction();

        $className = $request->className ?? 'ManualMaterial';

        $material = new $className($request->all());
        $material->category_id = $id;
        $material->save();

        if (is_iterable($request->attrs)) {

            foreach ($request->attrs as $attr_id => $value) {
                if ($value) {
                    $material->parameters()->create([
                        'attr_id' => $attr_id,
                        'value' => $value
                    ]);
                }
            }
        }

        if ($request->document) {
            $document = $request->document;

            $file = new ManualMaterialPassport();

            $file->name = $document->getClientOriginalName();
            $file->user_id = Auth::user()->id;
            $file->material_id = $material->id;

            $mime = $document->getClientOriginalExtension();
            $file_name =  'material-' . $material->id . '/passport-' . uniqid() . '.' . $mime;

            Storage::disk('material_passport')->put($file_name, File::get($document));

            FileEntry::create([
                'filename' => $file_name,
                'size' => $document->getSize(),
                'mime' => $document->getClientMimeType(),
                'original_filename' => $document->getClientOriginalName(),
                'user_id' => Auth::user()->id,
            ]);

            $file->file_name = $file_name;

            $file->save();
        }

        DB::commit();

        return back();
    }


    public function update(MaterialsRequest $request, $id)
    {
        DB::beginTransaction();

        $className = $request->className ?? 'ManualMaterial';
        $material = $className::findOrFail($request->id);
        $material->name = $request->name;
        $material->description = $request->description;
        if ($className == 'ManualMaterial') {
            $material->buy_cost = $request->buy_cost;
            $material->use_cost = $request->use_cost;
            $material->manual_reference_id = $request->manual_reference_id;
        }

        if ($request->attrs) {
        $material->parametersClear()->whereIn('attr_id', array_keys($request->attrs))->forceDelete();

            foreach ($request->attrs as $attr_id => $value) {

                if ($value) {
                    $material->parameters()->create([
                        'attr_id' => $attr_id,
                        'value' => $value
                    ]);
                }
            }
        }

        $material->save();


        if ($request->document) {
            !isset($material->passport) ?: $material->passport->delete();

            $document = $request->document;

            $file = new ManualMaterialPassport();

            $file->name = $document->getClientOriginalName();
            $file->user_id = Auth::user()->id;
            $file->material_id = $material->id;

            $mime = $document->getClientOriginalExtension();
            $file_name =  'material-' . $material->id . '/passport-' . uniqid() . '.' . $mime;

            Storage::disk('material_passport')->put($file_name, File::get($document));

            FileEntry::create([
                'filename' => $file_name,
                'size' => $document->getSize(),
                'mime' => $document->getClientMimeType(),
                'original_filename' => $document->getClientOriginalName(),
                'user_id' => Auth::user()->id,
            ]);

            $file->file_name = $file_name;

            $file->save();
        }

        DB::commit();

        return back();
    }


    public function clone(MaterialsRequest $request, $id)
    {
        DB::beginTransaction();

        $className = $request->className ?? 'ManualMaterial';

        $material = new $className($request->all());
        $material->category_id = $id;
        $material->save();

        if ($request->attrs){
            foreach ($request->attrs as $attr_id => $value) {
                if ($value) {
                    $material->parameters()->create([
                        'attr_id' => $attr_id,
                        'value' => $value
                    ]);
                }
            }
        }

        if (!$request->document) {
            $old_passport = ManualMaterialPassport::where('material_id', $request->id)->first();

            if ($old_passport) {
                $new_passport = $old_passport->replicate();
                $new_passport->material_id = $material->id;

                $file = storage_path('app/public/docs/material_passport/' . $old_passport->file_name);
                $mime = explode('/', mime_content_type($file));
                $file_name =  'material-' . $material->id . '/passport-' . uniqid() . '.' . $mime[1];
                Storage::disk('material_passport')->copy($old_passport->file_name, $file_name);

                $new_passport->file_name = $file_name;
                $new_passport->save();
            }
        } else {
            $document = $request->document;

            $file = new ManualMaterialPassport();

            $file->name = $document->getClientOriginalName();
            $file->user_id = Auth::user()->id;
            $file->material_id = $material->id;

            $mime = $document->getClientOriginalExtension();
            $file_name =  'material-' . $material->id . '/passport-' . uniqid() . '.' . $mime;

            Storage::disk('material_passport')->put($file_name, File::get($document));

            FileEntry::create([
                'filename' => $file_name,
                'size' => $document->getSize(),
                'mime' => $document->getClientMimeType(),
                'original_filename' => $document->getClientOriginalName(),
                'user_id' => Auth::user()->id,
            ]);

            $file->file_name = $file_name;

            $file->save();
        }

        DB::commit();

        return back();
    }


    public function delete(Request $request)
    {
        abort_if(!Auth::user()->can('materials_remove'), 403);

        DB::beginTransaction();

        $className = $request->className;

        $mat = $className::findOrFail($request->mat_id);
        $mat->parameters()->delete();

        if ($className == 'ManualMaterial') {
            $mat->work_relations()->delete();
        }

        $mat->delete();

        DB::commit();

        return \GuzzleHttp\json_encode(true);
    }

    public function restore(Request $request)
    {
        abort_if(!Auth::user()->can('materials_remove'), 403);

        DB::beginTransaction();

        $className = $request->className;

        $mat = $className::withTrashed()->findOrFail($request->mat_id);
        $mat->parametersClear()->restore();

        $mat->restore();

        DB::commit();

        return \GuzzleHttp\json_encode(true);
    }


    public function select_attr_value(Request $request)
    {
        $className = $request->className ?? 'ManualMaterialParameter';
        $query = (new $className)->where('attr_id', $request->attr_id);

        if ($request->reference_name or $request->reference_id) {
            if ($request->reference_id) {
                $mat_ids = ManualMaterial::where('manual_reference_id',  $request->reference_id)->get()->pluck('id');
            } else {
                $mat_ids = ManualMaterial::where('name', 'like', "$request->reference_name%")->get()->pluck('id');
            }
            $query->whereIn('mat_id', $mat_ids);
        }

        $params = $query->get();

        if ($request->extended) {
            $unique_values = array_unique($params->pluck('value')->toArray());

            $unique_values = array_map(
                function($val) use($request) {
                return [
                    'attr_id' => $request->attr_id,
                    'value' => $val
                ];
            }, $unique_values);

        } else {
            $unique_values = array_unique($params->pluck('value')->toArray());
        }

        return response()->json($unique_values);
    }


    public function search_by_attributes(Request $request)
    {
        $className = $request->className ?? 'ManualMaterial';

        if ($request->has('category_id')) {
            $result = $className::where('category_id', $request->category_id)->with(['parameters',
                ])->get()->take(30);
        } else {
            $classNameParameter = class_basename($className::first()->parameters()->first());

            $step = $classNameParameter::query();
            foreach ($request->values as $key => $value) {
                $step->orWhere(function ($q) use ($request, $key, $value) {
                    $q->where('attr_id', $request->attr_id[$key])->whereIn('value', $value);
                });
            };

            $a = array_count_values($step->get()->pluck('mat_id')->toArray());
            arsort($a);

            foreach ($a as $step) {
                if (count($request->attr_id) > $step) {
                    if(($key = array_search($step, $a)) !== FALSE){
                        unset($a[$key]);
                    }
                }
            }

            $result = $className::whereIn('id', array_keys($a))->with(['parameters',
                'work_relations',
                'passport'])->get()->take(50);
        }

        return response()->json($result);
    }

    public function get_all_materials(Request $request)
    {
        $category = ManualMaterialCategory::where('id', $request->category_id)
            ->with([
                'attributes',
                'materials',
                'materials.parameters',
                'materials.work_relations',
            ]);

        return response()->json($category->first());
    }

    public function get_materials(Request $request)
    {
        $category = ManualMaterialCategory::where('id', $request->category_id)
            ->with([
                'attributes',
                'materials',
                'materials.parameters',
                'materials.work_relations',
            ]);

        return response()->json($category->first());
    }

    public function getReferences(Request $request)
    {
        $references = ManualReference::where('category_id', $request->category_id);

        if ($request->q) {
            $references = $references->where('name', 'like', '%' . $request->q . '%');
        }

        $references = $references->take(20)->get();

        $results = [];

        foreach ($references as $index => $reference) {
            $results[] = [
                'id' => $reference->id,
                'text' => $reference->name,
            ];
        }

        return ['results' => $results];
    }
}
