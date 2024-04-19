<?php

namespace App\Http\Controllers\Building\MaterialAccounting;


use App\Domain\Enum\NotificationType;
use App\Http\Requests\Building\MaterialAccounting\AttachContractRequest;
use App\Http\Requests\Building\MaterialAccounting\MaterialAccountingBaseMoveToNewRequest;
use App\Http\Requests\Building\MaterialAccounting\MaterialAccountingBaseMoveToUsedRequest;
use App\Http\Requests\Building\MaterialAccounting\SplitBaseRequest;
use App\Models\Contractors\Contractor;
use App\Models\Manual\ManualMaterialCategory;
use App\Models\Manual\ManualMaterial;
use App\Services\MaterialAccounting\MaterialAccountingBadMaterilas;
use App\Services\MaterialAccounting\MaterialAccountingService;
use App\Services\MaterialAccounting\Reports\BasesReportExport;
use Carbon\CarbonPeriod;
use App\Models\{Comment,
    Group,
    Manual\ManualMaterialParameter,
    Manual\ManualReference,
    Notification,
    Task,
    User,
    ProjectObject,
    FileEntry};
use Illuminate\Database\Eloquent\Builder;
use App\Models\MatAcc\{
    MaterialAccountingOperation,
    MaterialAccountingOperationFile,
    MaterialAccountingBase,
    MaterialAccountingMaterialFile,
    MaterialAccountingOperationMaterials,
    MaterialAccountingMaterialAddition};
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Building\MaterialAccounting\OperationReportRequest;
use Illuminate\Support\Facades\{Artisan, Auth, DB, File, Session, Storage};
use Carbon\Carbon;
use Log;


use App\Services\MaterialAccounting\Reports\ObjectActionReportExport;

class MaterialAccountingController extends Controller
{
    public function operations()
    {
        $operations = MaterialAccountingOperation::index()->get();

        $user_has_operations = MaterialAccountingOperation::where('author_id', auth()->id())
            ->orWhereHas('responsible_users', function($resp) {
                return $resp->where('user_id', auth()->id());
            })->exists();

        $operations->each->append('total_weigth');

        return view('building.material_accounting.operation_log', [
            'filter_params' => MaterialAccountingOperation::$filter,
            'operations' => $operations,
            'user_has_operations' => $user_has_operations,
            'categories' => ManualMaterialCategory::whereNotIn('id', [12,14])->with('attributes')->select('id', 'name')->get(),
        ]);
    }

    public function print_operations()
    {
        $results = json_decode(request()->results);
        $params = $results->filter_params;
        $values = $results->filter_values;

        $filter = [];
        $prepared_results = collect([]);

        $objects = ProjectObject::get();
        $users = User::find($results->author_id);
        $dummy_operation = new MaterialAccountingOperation();

        foreach ($results->object_text as $id => $object_text) {
            $curr_obj = ProjectObject::where(function (Builder $q) use ($object_text) {
                $q->where('name', 'like', "%{$object_text}%")
                    ->orWhere('address', 'like', "%{$object_text}%")
                    ->orWhere('short_name', 'like', "%{$object_text}%");
            })->firstOrFail();
            $curr_user = $users->find($results->author_id[$id]);
            $curr_type = $dummy_operation->type_names[$results->type[$id]];
            $curr_status = $dummy_operation->status_names[$results->status[$id]];

            $prepared_results->push( collect([
                'object_id' => $curr_obj->id,
                'object_name' => $curr_obj->name,
                'object_address' => $curr_obj->address,
                'object_short_name' => $curr_obj->short_name,
                'operation_type' => $curr_type,
                'curr_status' => $curr_status,
                'curr_user' => $curr_user->full_name,
                'created_at' => $results->created_at[$id],
                'actual_date_to' => $results->actual_date_to[$id],
                'actual_date_from' => $results->actual_date_from[$id],
            ]));
        }
        $filters = [];

        foreach($params as $id => $param) {
            $filters[$param] []= $values[$id];
        }

        return view('building.material_accounting.print_operations', [
            'filters' => $filters,
            'prepared_results' => $prepared_results->groupBy('object_id'),
        ]);

    }

    public function report_card()
    {
        $bases = MaterialAccountingBase::index()->with('comments')->get();

        return view('building.material_accounting.report_card', [
            'filter_params' => MaterialAccountingBase::$filter,
            'bases' => $bases,
            'categories' => ManualMaterialCategory::whereNotIn('id', [12,14])->with('attributes')->select('id', 'name')->get(),
        ]);
    }

    public function print_report(Request $request)
    {
        $result = json_decode($request->results, true);

        if ($result['date'] == 'null') {
            $result['date'] = now()->format('d.m.Y');
        }

        if (isset($request['filter'])) {
            foreach ($result['filter'] as $index => $filter) {
                $result['filter'][$index] = get_object_vars($filter);
            }

            foreach ($result['filter'] as $index => $item) {
                if (is_object($item['value_id'])) {
                    $result['filter'][$index]['value_id'] = get_object_vars($item['value_id']);
                }
            }

            foreach ($result['filter'] as $index => $item) {
                if (isset($item['value_id']['parameters']) && is_array($item['value_id']['parameters'])) {
                    foreach ($item['value_id']['parameters'] as $key => $param) {
                        $result['filter'][$index]['value_id']['parameters'][$key] = get_object_vars($param);
                        $result['filter'][$index]['value_id']['parameters'][$key]['value'] = get_object_vars($param->value);
                    }
                }
            }
        }

        $customRequest = new Request($result);

        return (new BasesReportExport($this->filter_base($customRequest)->getData(), $customRequest))->export();
    }

    public function closed_operation($operation_id)
    {
        $operation = MaterialAccountingOperation::where('type', '!=', 5)->findOrFail($operation_id);
        $operation->load(['object_from', 'object_to', 'author', 'sender', 'recipient', 'materials.manual', 'images_sender', 'documents_sender', 'images_recipient', 'documents_recipient']);

        return view('building.material_accounting.closed_operation', [
            'operation' => $operation
        ]);
    }


    public function filter(Request $request)
    {
        $operations = MaterialAccountingOperation::with(['object_from', 'object_to', 'author', 'sender', 'recipient'])
            ->where('type', '!=', 5);

        if (! $request->with_closed) {
            $operations->where('is_close', '!=', 1);
        }

        $manual_search = MaterialAccountingOperation::$filter;
        $new_options = [];

        if ($request->filter and count($request->filter)) {
            foreach ($request->filter as $key => $item) {
                $new_options[$item['parameter_id']][] = $item['value_id'];
            }

            foreach ($new_options as $key => $item) {
                if ($key == 5) {
                    $operations->where(function ($query) use ($item) {
                        foreach ($item as $one_entity) {
                            $query->orWhereHas('materials', function ($oper_mat) use ($one_entity) {
                                $oper_mat->whereHas('manual', function ($mat) use ($one_entity) {
                                    if (isset($one_entity['reference_id'])) {
                                        $mat->where('manual_reference_id', $one_entity['reference_id']);
                                    } else {
                                        $mat->where('name', 'like', "{$one_entity['reference_name']}%");
                                    }
                                    foreach ($one_entity['parameters'] as $parameter) {
                                        if (is_array($parameter['value'])) {
                                            if ($parameter['value']['from'] !== null and $parameter['value']['to'] !== null) {
                                                $mats_id = ManualMaterial::where('manual_reference_id', $one_entity['reference_id'])->get('id')->pluck('id');
                                                $par_q = ManualMaterialParameter::whereIn('mat_id', $mats_id)->where('attr_id', $parameter['attr_id']);

                                                if (!($parameter['value']['from'] == floor($par_q->min('value'))) or !($parameter['value']['to'] == floor($par_q->max('value')))) {
                                                    $mat->whereHas('parameters', function ($mat_par) use ($parameter) {
                                                        $mat_par->where('attr_id', $parameter['attr_id'])
                                                            ->where('manual_material_parameters.value', '>=', $parameter['value']['from'])
                                                            ->where('manual_material_parameters.value', '<=', $parameter['value']['to']);
                                                    });
                                                }
                                            } else {
                                                $mat->whereDoesntHave('parameters', function ($mat_par) use ($parameter) {
                                                    $mat_par->where('attr_id', $parameter['attr_id']);
                                                });
                                            }
                                        } else {
                                            if ($parameter['value']) {
                                                $mat->whereHas('parameters', function ($mat_par) use ($parameter) {
                                                    $mat_par->where('attr_id', $parameter['attr_id'])->where('manual_material_parameters.value', $parameter['value']);
                                                });
                                            }
                                        }
                                    }
                                });
                            });
                        }
                    });
                }
                elseif ($key == 1) {
                    $operations->with('materials.manual')
                        ->whereHas('materials', function($q) use ($item) {
                            $q->whereIn('manual_material_id', $item);
                        });
                } elseif($key == 0) {

                    $request->session()->put('object_id', $item);

                    $operations->where(function($q) use($manual_search, $key, $item) {
                        $q->whereIn('object_id_to', $item)->orWhereIn('object_id_from', $item);
                    });
                } else {
                    $operations->whereIn($manual_search[$key]['db_name'], $item);
                }
            };
        }

        if ($request->date) {
            if (Carbon::parse($request->date) <= Carbon::today()) {
                $operations = $operations->where(function ($query) use ($request) {
                    $query->whereDate('created_at', Carbon::parse($request->date));
                });
            } else {
                $operations = $operations->where(function ($query) use ($request) {
                    $query->whereDate('created_at', Carbon::today()->format('d.m.Y'));
                });
            }
        }

        if ($request->search && $searches = explode('•', $request->search)) {
            foreach ($searches as $search) {
                $operations = $operations->with('materials.manual', 'object_from', 'object_to', 'author', 'responsible_users.user')
                    ->where(function (Builder $firstQuery) use ($search) {
                        $firstQuery->orWhereHas('materials.manual', function(Builder $q) use ($search) {
                            $q->where('manual_materials.name', 'like',  "%{$search}%");
                        })->orWhereHas('object_from', function (Builder $q) use ($search) {
                            $q->where(function (Builder $query) use ($search) {
                                $query->where('name', 'like',  "%{$search}%")
                                    ->orWhere('address', 'like',  "%{$search}%")
                                    ->orWhere('short_name', 'like',  "%{$search}%");
                            });
                        })->orWhereHas('object_to', function (Builder $q) use ($search) {
                            $q->where(function (Builder $query) use ($search) {
                                $query->where('name', 'like',  "%{$search}%")
                                    ->orWhere('address', 'like',  "%{$search}%")
                                    ->orWhere('short_name', 'like',  "%{$search}%");
                            });
                        })->orWhereHas('author', function (Builder $q) use ($search) {
                            $q->where(DB::raw('CONCAT(last_name, " ", first_name, " ", patronymic)'), 'like',  "%{$search}%");
                        })->orWhereHas('responsible_users.user', function (Builder $q) use ($search) {
                            $q->where(DB::raw('CONCAT(last_name, " ", first_name, " ", patronymic)'), 'like',  "%{$search}%");
                        })->orWhere(function (Builder $q) use ($search) {
                            $string = mb_strtolower($search);
                            $result = array_filter(MaterialAccountingOperation::getModel()->status_names, function ($item) use ($string) {
                                return stristr(mb_strtolower($item), $string);
                            });

                            $q->whereIn('status', array_keys($result));
                        })->orWhere(function (Builder $q) use ($search) {
                            $string = mb_strtolower($search);
                            $result = array_filter(MaterialAccountingOperation::getModel()->type_names, function ($item) use ($string) {
                                return stristr(mb_strtolower($item), $string);
                            });

                            $q->whereIn('type', array_keys($result));
                        });
                    });
            }
        }

        $operations = $operations->orderBy('id', 'desc')->limit(40)->get();

        $operations->each->append('total_weigth');
        $operations->loadMissing(['materials.manual']);

        return response()->json(['result' => $operations]);
    }

    public function get_search_values(Request $request)
    {
        $response = (new MaterialAccountingService())->getSearchValues($request->search);

        return response([
            'data' => $response
        ]);
    }

    public function operations_get_search_values(Request $request)
    {
        $response = (new MaterialAccountingService())->getSearchValues($request->search_untrimmed, true);
        return response([
            'data' => $response
        ]);
    }


    public function filter_base(Request $request)
    {
        $bases = MaterialAccountingBase::with(['object', 'material.convertation_parameters', 'comments'])->where('count', '>', 0);
        $manual_search = MaterialAccountingBase::$filter;

        $request->date = $request->date ? $request->date : Carbon::today()->format('d.m.Y');

        if ($request->filter) {

            foreach ($request->filter as $key => $item) {
                $new_options[$item['parameter_id']][] = $item['value_id'];
            }

            foreach ($new_options as $key => $item) {
                if ($key == 3) {
                    $bases->where(function ($query) use ($item) {
                        foreach ($item as $one_entity) {
                            $query->orWhereHas('material', function ($mat) use ($one_entity) {
                                if (isset($one_entity['reference_id'])) {
                                    $mat->where('manual_reference_id', $one_entity['reference_id']);
                                } else {
                                    $mat->where('name', 'like', "{$one_entity['reference_name']}%");
                                }
                                foreach ($one_entity['parameters'] as $parameter) {
                                    if (is_array($parameter['value'])) {
                                        if ($parameter['value']['from'] !== null and $parameter['value']['to'] !== null) {
                                            $mats_id = ManualMaterial::where('manual_reference_id', $one_entity['reference_id'])->get('id')->pluck('id');
                                            $par_q = ManualMaterialParameter::whereIn('mat_id', $mats_id)->where('attr_id', $parameter['attr_id']);

                                            if (!($parameter['value']['from'] == floor($par_q->min('value'))) or !($parameter['value']['to'] == floor($par_q->max('value')))) {
                                                $mat->whereHas('parameters', function ($mat_par) use ($parameter) {
                                                    $mat_par->where('attr_id', $parameter['attr_id'])
                                                        ->where('manual_material_parameters.value', '>=', $parameter['value']['from'])
                                                        ->where('manual_material_parameters.value', '<=', $parameter['value']['to']);
                                                });
                                            }
                                        } else {
                                            $mat->whereDoesntHave('parameters', function ($mat_par) use ($parameter) {
                                                $mat_par->where('attr_id', $parameter['attr_id']);
                                            });
                                        }
                                    } else {
                                        if ($parameter['value']) {
                                            $mat->whereHas('parameters', function ($mat_par) use ($parameter) {
                                                $mat_par->where('attr_id', $parameter['attr_id'])->where('manual_material_parameters.value', $parameter['value']);
                                            });
                                        }
                                    }
                                }
                            });
                        }
                    });
                }
                else {
                    $bases->where(function ($q) use ($request, $key, $item, $manual_search) {
                        if ($key == 0) {
                            session()->put('object_id', $item);
                        }

                        $q->whereIn($manual_search[$key]['db_name'], $item);
                    });
                }
            }
        }

        if ($request->date) {
            if (Carbon::parse($request->date) <= Carbon::today()) {
                $bases = $bases->where(function ($query) use ($request) {
                    $query->where('date', Carbon::parse($request->date)->format('d.m.Y'));
                });
            }
            else {
                $bases = $bases->where(function ($query) use ($request) {
                    $query->where('date', Carbon::today()->format('d.m.Y'));
                });
            }
        }

        $operations = MaterialAccountingOperation::query()
            ->with('materials.manual')
            ->whereNotIn('status', [5, 7])
            ->where('is_close', 0)
            ->where('type', '!=', 5)
            ->get();

        $bases = $bases->get();


        if (Carbon::parse($request->date)->between(Carbon::today()->addDay(1), Carbon::today()->addYears(100))) {

            // plan for arrival, moving and write_off
            foreach ($operations->whereNotIn('type', [3, 5]) as $operation) {
                if ($operation->planned_date_from && Carbon::parse($request->date)->between(Carbon::parse($operation->planned_date_from), Carbon::today()->addYears(100))) {
                    foreach ($operation->materials->where('type', 3) as $material) {
                        if ($bases->where('object_id', $operation->object_id_from)->where('manual_material_id', $material->manual_material_id)->first()) {
                            $bases->where('object_id', $operation->object_id_from)->where('manual_material_id', $material->manual_material_id)->first()->count -= $material->count;
                        } else {
                            $new_base = new MaterialAccountingBase();
                            $new_base->object_id = $operation->object_id_from;
                            $new_base->object = $operation->object_from ?? $operation->object_to;
                            $new_base->manual_material_id = $operation->manual_material_id;
                            $new_base->material = $material->manual;
                            $new_base->date = Carbon::parse($request->date)->format('d.m.Y');
                            $new_base->count = $material->count;

                            $bases = $bases->push($new_base);
                        }
                    }
                }

                if ($operation->planned_date_to && Carbon::parse($request->date)->between(Carbon::parse($operation->planned_date_to)->addDays(1), Carbon::today()->addYears(100))) {
                    foreach ($operation->materials->where('type', 3) as $material) {
                        if ($bases->where('object_id', $operation->object_id_to)->where('manual_material_id', $material->manual_material_id)->first()) {
                            $bases->where('object_id', $operation->object_id_to)->where('manual_material_id', $material->manual_material_id)->first()->count += $material->count;
                        } else {
                            $new_base = new MaterialAccountingBase();
                            $new_base->object_id = $operation->object_id_to;
                            $new_base->object = $operation->object_to ? $operation->object_to : $operation->object_from;
                            $new_base->manual_material_id = $operation->manual_material_id;
                            $new_base->material = $material->manual;
                            $new_base->date = Carbon::parse($request->date)->format('d.m.Y');
                            $new_base->count = $material->count;

                            $bases = $bases->push($new_base);
                        }
                    }
                }
            }
            // 6 => 'plan_to', // for transformation
            // 7 => 'plan_from' // for transformation
            foreach ($operations->where('type', 3) as $operation) {
                if ($operation->planned_date_to && Carbon::parse($request->date)->between(Carbon::parse($operation->planned_date_to), Carbon::today()->addYears(100))) {
                    foreach ($operation->materials->where('type', 7) as $material) {
                        if ($bases->where('object_id', $operation->object_id_from)->where('manual_material_id', $material->manual_material_id)->first()) {
                            $bases->where('object_id', $operation->object_id_from)->where('manual_material_id', $material->manual_material_id)->first()->count -= $material->count;
                        } else {
                            $new_base = new MaterialAccountingBase();
                            $new_base->object_id = $operation->object_id_to;
                            $new_base->object = $operation->object_to ? $operation->object_to : $operation->object_from;
                            $new_base->manual_material_id = $operation->manual_material_id;
                            $new_base->material = $material->manual;
                            $new_base->date = Carbon::parse($request->date)->format('d.m.Y');
                            $new_base->count = $material->count;

                            $bases = $bases->push($new_base);
                        }
                    }
                    foreach ($operation->materials->where('type', 6) as $material) {
                        if ($bases->where('object_id', $operation->object_id_to)->where('manual_material_id', $material->manual_material_id)->first()) {
                            $bases->where('object_id', $operation->object_id_to)->where('manual_material_id', $material->manual_material_id)->first()->count += $material->count;
                        } else {
                            $new_base = new MaterialAccountingBase();
                            $new_base->object_id = $operation->object_id_to;
                            $new_base->object = $operation->object_to ? $operation->object_to : $operation->object_from;
                            $new_base->manual_material_id = $operation->manual_material_id;
                            $new_base->material = $material->manual;
                            $new_base->date = Carbon::parse($request->date)->format('d.m.Y');
                            $new_base->count = $material->count;

                            $bases = $bases->push($new_base);
                        }
                    }
                }
            }

            if (count($request->filter)) {
                foreach ($new_options as $key => $item) {
                    $bases->whereIn($manual_search[$key]['db_name'], $item);
                }
            }

            if ($request->date) {
                if (Carbon::parse($request->date) <= Carbon::today()) {
                    $bases = $bases->where(function ($query) use ($request) {
                        $query->where('date', Carbon::parse($request->date)->format('d.m.Y'));
                    });
                }
                else {
                    $bases = $bases->where('date', Carbon::today()->format('d.m.Y'));
                }
            }
        }

        if (isset($new_options[0])){
            $bases = $bases->whereIn('object_id', $new_options[0]);
        }
        if (isset($new_options[1])){
            $bases = $bases->whereIn('manual_material_id', $new_options[1]);
        }
        $bases->load('material.parameters');
        $bases = $bases->where('count', '>', 0);
        if (count($request->filter) == 0) {
            $bases = $bases->take(50);
        }
        return response()->json(['result' => $bases->each->append('comment_name')]);
    }


    public function upload(Request $request, $operation_id)
    {
        if ($request->file) {
            $file = new MaterialAccountingOperationFile();

            $mime = $request->file->getClientOriginalExtension();
            $file_name =  'operation_' . $operation_id . '-'. uniqid() .'-file-' . uniqid() . '.' . $mime;

            Storage::disk('mat_acc_operation_files')->put($file_name, File::get($request->file));

            FileEntry::create([
                'filename' => 'storage/docs/mat_acc_operation_files/' . $file_name,
                'size' => $request->file->getSize(),
                'mime' => $request->file->getClientMimeType(),
                'original_filename' => $request->file->getClientOriginalName(),
                'user_id' => Auth::user()->id,
            ]);

            $file->file_name = $file_name;
            $file->path = 'storage/docs/mat_acc_operation_files';
            $file->user_id = Auth::user()->id;
            $file->operation_id = $operation_id;
            $file->author_type = $request->author_type;

            if ($mime == 'png' or $mime == 'jpg' or $mime == 'jpeg') {
                $file->type = 2;
            }
            else {
                $file->type = 1;
            }

            $file->save();
        }
        return response()->json($file);
    }

    public function part_upload(Request $request, $operation_id)
    {
        if ($request->file) {
            $file = new MaterialAccountingMaterialFile();
            $mime = $request->file->getClientOriginalExtension();
            $file_name =  'operation_' . $operation_id . '-'. uniqid() .'-file-' . uniqid() . '.' . $mime;

            Storage::disk('mat_acc_operation_files')->put($file_name, File::get($request->file));

            FileEntry::create([
                'filename' => 'storage/docs/mat_acc_operation_files/' . $file_name,
                'size' => $request->file->getSize(),
                'mime' => $request->file->getClientMimeType(),
                'original_filename' => $request->file->getClientOriginalName(),
                'user_id' => Auth::user()->id,
            ]);

            $file->file_name = $file_name;
            $file->path = 'storage/docs/mat_acc_operation_files';
            $file->operation_id = $operation_id;
            $file->operation_material_id = $request->operation_material_id ?? 0;
            $file->type = 1;

            if ($mime == 'png' or $mime == 'jpg' or $mime == 'jpeg') {
                $file->type = 2;
            }
            if ($request->type === 'cert') {
                $file->type = 3;
            }

            $file->save();
        }

        return response()->json($file);
    }

    public function delete_file(Request $request, $operation_id)
    {
        MaterialAccountingOperationFile::where('operation_id', $operation_id)->where('file_name', $request->file_name)->delete();
        MaterialAccountingMaterialFile::where('operation_id', $operation_id)->where('file_name', $request->file_name)->delete();

        return response()->json(true);
    }

    public function update_part_operation(Request $request, $task_id = null)
    {
        DB::beginTransaction();
        $is_updating = true;

        $part_description = $request->description;

        if ($task_id) {
            $task = Task::find($task_id);
            $part_description = $task->final_note;
            $task->final_note = $request->description;
            $task->save();
            $mat_id = $task->target_id;
            $task->solve_n_notify();

            $is_updating = ($request->status_result == 'accept');
        } else {
            $mat_id = $request->material_id;
        }

        $material = MaterialAccountingOperationMaterials::find($mat_id);
        if ($is_updating) {
            $material->load('updated_material');

            if ($material->updated_material) {
                $request->material_unit = $material->updated_material->unit;
                $request->material_count = $material->updated_material->count;
                $request->manual_material_id = $material->updated_material->manual_material_id;
                $request->used = $material->updated_material->used;
            }

            $operation = $material->operation;

//            $material->unit = $request->material_unit;
            $material->save();

            $resultDelete = $material->deletePart();

            if ($resultDelete['status'] == 'error') {
                return $resultDelete;
            }

            $base = MaterialAccountingBase::find($request->base_id);
            $base_comments = $base ? $base->comments : [];
            $fakeRequest = new Request([
                'type' => $material->type,
                'materials' => [[
                    'material_id' => $request->manual_material_id,
                    'base_id' => 'undefined',
                    'material_unit' => $request->material_unit,
                    'material_count' => $request->material_count,
                    'material_date' => Carbon::parse($material->fact_date),
                    'used' => $request->used ?? $material->used,
                    'comments' => $base_comments,
                ]]
            ]);

            $resultCreate = $operation->partSend($fakeRequest);

            if ($resultCreate['status'] == 'error') {
                return $resultCreate;
            }

            MaterialAccountingMaterialAddition::where('operation_id', $operation->id)
                ->where('operation_material_id', $mat_id)
                ->update([
                    'description' => $part_description,
                    'user_id' => Auth::user()->id,
                    'operation_material_id' => $resultCreate['operation_material_id']
                ]);

            MaterialAccountingMaterialFile::where('operation_material_id', $mat_id)
                ->update(['operation_material_id' => $resultCreate['operation_material_id']]);

            if ($material->updated_material) {
                $material->updated_material->delete();
            }
        }

        if ($material->updated_material) {
            $material->updated_material->delete();
        }

        if ($is_updating) {
            $operation->update_fact();
        }

        DB::commit();

        return  $task_id ? redirect(route('tasks::index')) : ['status' => 'success'];
    }

    function store_update_task(Request $request)
    {
        $material = MaterialAccountingOperationMaterials::find($request->material_id);
        $operation = $material->operation;

        $manual_material = ManualMaterial::where('manual_materials.id', $request->manual_material_id)
            ->with('parameters')
            ->leftJoin('manual_material_categories', 'manual_material_categories.id', 'manual_materials.category_id')
            ->select('manual_material_categories.id as cat_id', 'manual_material_categories.category_unit', 'manual_materials.*')
            ->first();

        $unit = $material->units_name[$request->material_unit];
        $unit_parameter = $manual_material->parameters()->where('unit', $unit)->first();

        if ($manual_material->category_unit == $unit) {
            $material_unit = $request->material_unit;
            $material_count = $request->material_count;
        } else {
            if (!isset($unit_parameter->value) or $unit_parameter->value <= 0) {
                $message = 'Невозможно преобразовать ' . $manual_material->name . ' из "' . $unit . '" в основную единицу измерения данной категории "' . $manual_material->category_unit . '".';

                return response()->json(['message' => $message]);
            }
            $material_count = $request->material_count / $unit_parameter->value;
            $material_unit = array_search($manual_material->category_unit, $material->units_name);
        }

        MaterialAccountingOperationMaterials::create([
            'operation_id' => $operation->id,
            'manual_material_id' => $request->manual_material_id,
            'count' => $material_count,
            'unit' => $material_unit,
            'type' => 10,
            'updated_material_id' => $material->id,
            'used' => $material->used,
        ]);

        $updation_task = Task::create([
            'name' => 'Запрос на редактирование операции частичного закрытия',
            'description' => 'Пользователь ' . Auth::user()->full_name .
                ' отправил заявку на редактирование операции частичного закрытия с комментарием: "' . $request->description . '"',
            'responsible_user_id' => $operation->author_id,
            'user_id' => Auth::id(),
            'target_id' => $material->id,
            'expired_at' => $this->addHours(48),
            'status' => 23,
            'final_note' => $request->description,
        ]);

        dispatchNotify(
            $updation_task->responsible_user_id,
            'Новая задача «' . $updation_task->name . '» ',
            NotificationType::PARTIAL_CLOSURE_OPERATION_EDIT_REQUEST_NOTIFICATION,
            [
                'additional_info' => ' Ссылка на задачу: ' . $updation_task->task_route(),
                'task_id' => $updation_task->id,
                'contractor_id' => null,
                'project_id' => null,
                'object_id' => null,
            ]
        );

        return ['status' => 'success'];
    }

    public function delete_part_operation(Request $request, $task_id = null)
    {
        DB::beginTransaction();
        $is_deleting = true;

        if ($task_id) {
            $task = Task::find($task_id);
            $task->final_note = $request->description;
            $task->save();
            $mat_id = $task->target_id;
            $task->solve_n_notify();

            $is_deleting = ($request->status_result == 'accept');
        } else {
            $mat_id = $request->material_id;
        }

        if ($is_deleting) {
            $mat = MaterialAccountingOperationMaterials::find($mat_id);
            $operation = $mat->operation;
            $result = $mat->deletePart();
        }

        $operation->update_fact();
        DB::commit();

        return  $task_id ? redirect(route('tasks::index')) : $result;
    }

    public function store_deletion_task(Request $request)
    {
        $mat = MaterialAccountingOperationMaterials::find($request->material_id);
        $operation = $mat->operation;

        $deletion_task = Task::create([
            'name' => 'Запрос на удаление операции частичного закрытия',
            'description' => 'Пользователь ' . Auth::user()->full_name .
                ' отправил заявку на удаление операции частичного закрытия',
            'responsible_user_id' => $operation->author_id,
            'user_id' => Auth::id(),
            'target_id' => $mat->id,
            'expired_at' => $this->addHours(48),
            'status' => 22
        ]);

        dispatchNotify(
            $deletion_task->responsible_user_id,
            'Новая задача «' . $deletion_task->name . '» ',
            NotificationType::PARTIAL_CLOSURE_OPERATION_DELETION_REQUEST_NOTIFICATION,
            [
                'additional_info' => ' Ссылка на задачу: ' . $deletion_task->task_route(),
                'task_id' => $deletion_task->id,
                'contractor_id' => null,
                'project_id' => null,
                'object_id' => null,
            ]
        );

        return \GuzzleHttp\json_encode($operation->url);

    }

    public function update_part_task($task_id)
    {
        $task = Task::find($task_id);

        if (! $task->target) {
            $task->solve();
            abort(404);
        }
        $operation_link = $task->target->operation->url;
        return view('tasks.update_part_material', [
            'task' => $task,
            'operation_url' => $operation_link,
        ]);
    }

    public function delete_part_task($task_id)
    {
        $task = Task::find($task_id);

        return view('tasks.remove_task', [
            'task' => $task
        ]);
    }

    public function certificatelessTask($task_id)
    {
        $task = Task::whereStatus(43)->findOrFail($task_id)->load('taskable');

        return view('tasks.certificateless_task', [
            'task' => $task
        ]);
    }

    public function get_objects(Request $request)
    {
        $objects = ProjectObject::query();

        if ($request->q) {
            $objects = $objects->where(function ($objects) use ($request) {
                $objects->where('name', 'like', '%' . $request->q . '%')
                    ->orWhere('address', 'like', '%' . $request->q . '%')
                    ->orWhere('short_name', 'like', '%' . $request->q . '%');
            });
        }

        $objects = $objects->take(10)->get();

        $objects_json = [];
        if ($request->object_id and !$objects->where('id', $request->object_id)->first()) {
            $one_more = ProjectObject::find($request->object_id);
            if ($one_more) {
                $objects_json[] = ['code' => $one_more->id . '', 'label' => $one_more->short_name ?? ($one_more->name . '. ' . $one_more->address)];
            }
        }

        if ($request->one_more_object_id and !$objects->where('id', $request->one_more_object_id)->first()) {
            $one_more = ProjectObject::find($request->one_more_object_id);
            if ($one_more) {
                $objects_json[] = ['code' => $one_more->id . '', 'label' => $one_more->short_name ?? ($one_more->name . '. ' . $one_more->address)];
            }
        }

        if ($request->has('selected')) {
            // If we press "accept and fill", we should find object
            // from request parameter and return it as additional parameter
            // It won't affect other requests with search parameters because this parameter added one time from front-end
            $object = ProjectObject::find($request->get('selected'));
            if ($object) {
                $objects_json[] = ['code' => $object->id . '', 'label' => $object->short_name ?? ($object->name . '. ' . $object->address),];
            }
        }

        foreach ($objects as $object) {
            $objects_json[] = ['code' => $object->id . '', 'label' => $object->short_name ?? ($object->name . '. ' . $object->address)];
        }

        return response()->json($objects_json);
    }

    public function get_suppliers(Request $request)
    {
        $suppliers = Contractor::byType(Contractor::SUPPLIER);

        if ($request->q) {
            $suppliers = $suppliers->where('full_name', 'like', '%' . trim($request->q) . '%')
                ->orWhere('short_name', 'like', '%' . trim($request->q) . '%')
                ->orWhere('inn', 'like', '%' . trim($request->q) . '%')
                ->orWhere('kpp', 'like', '%' . trim($request->q) . '%');
        }

        $suppliers = $suppliers->where('in_archive', 0)->take(10)->get();
        $suppliers_json = [];

        if ($request->supplier_id and !$suppliers->where('id', $request->supplier_id)->first()) {
            $one_more = Contractor::find($request->supplier_id);
            $suppliers_json[] = ['code' => $one_more->id . '', 'label' => $one_more->short_name];
        }

        if ($request->ttn == 'true') {
            foreach ($suppliers as $supplier) {
                $suppliers_json[] = ['code' => $supplier->id . '', 'label' => $supplier->short_name . '. ' . $supplier->legal_address];
            }
        } else {
            foreach ($suppliers as $supplier) {
                $suppliers_json[] = ['code' => $supplier->id . '', 'label' => $supplier->short_name];
            }
        }

        return response()->json($suppliers_json);
    }

    public function get_users(Request $request)
    {
        $users = User::getAllUsers()->where('status', 1);
        $users_json = [];

        if ($request->q) {
            $groups = Group::where('name', $request->q)
                ->orWhere('name', 'like', '%' . $request->q . '%')
                ->pluck('id')
                ->toArray();

            $users = $users->where(DB::raw('CONCAT(last_name, " ", first_name, " ", patronymic)'), 'like', '%' . $request->q . '%');

            if (!empty($groups)) {
                $users = $users->orWhereIn('group_id', [$groups]);
            }
        }

        if (!$request->q || strlen($request->q) <= 2) {
            $frontUsers = $users->where('users.id', '!=', 1)->whereIn('users.group_id', [8,23,31,43,44,45,19,27])->take(10)->get();
            $otherUsers = $users->where('users.id', '!=', 1)->take(10)->get();
            $users = collect($frontUsers, $otherUsers)->unique()->slice(0,10);
        }
        else {
            $users = $users->where('users.id', '!=', 1)->take(10)->get();
        }

        if ($request->responsible_user_id and !$users->where('id', $request->responsible_user_id)->first()) {
            $one_more = User::find($request->responsible_user_id);
            if ($one_more) {
                $users_json[] = ['code' => $one_more->id . '', 'label' => $one_more->full_name];
            }
        }

        if ($request->from_responsible_user and !$users->where('id', $request->from_responsible_user)->first()) {
            $one_more = User::find($request->from_responsible_user);
            if ($one_more) {
                $users_json[] = ['code' => $one_more->id . '', 'label' => $one_more->full_name];
            }
        }

        if ($request->to_responsible_user and !$users->where('id', $request->to_responsible_user)->first()) {
            $one_more = User::find($request->to_responsible_user);
            if ($one_more) {
                $users_json[] = ['code' => $one_more->id . '', 'label' => $one_more->full_name];
            }
        }

        if ($request->author_id) {
            $users = [];
            $one_more = User::find($request->author_id);
            if ($one_more) {
                $users_json = [['code' => $one_more->id . '', 'label' => $one_more->full_name]];
            }
        }
        foreach ($users as $user) {
            if ($user->id != 1) {
                $users_json[] = ['code' => $user->id . '', 'label' => $user->full_name];
            }
        }

        return response()->json($users_json);
    }

    public function get_RPs(Request $request)
    {
        if ($this->weNeedToGetResponsibleRP($request)) return $this->findResponsibleProjectManager($request);

        $users = User::getAllUsers()->whereIn('users.group_id', [8, 19, 27]);
        $users_json = [];

        if ($request->q) {
            $users = $users->where(DB::raw('CONCAT(last_name, " ", first_name, " ", patronymic)'), 'like', '%' . $request->q . '%');
        }

        $users = $users->get();

        foreach ($users as $user) {
            if ($user->id != 1) {
                $users_json[] = ['code' => $user->id . '', 'label' => $user->full_name];
            }
        }

        return response()->json($users_json);
    }

    public function get_statuses(Request $request)
    {
        $statuses = (new MaterialAccountingOperation())->status_names;

        if ($request->q) {
            $statuses = array_filter($statuses, function($stat) use($request) {
                return stripos($stat, $request->q) !== false;
            });
        }

        if ($request->status_id) {
            $statuses = [(new MaterialAccountingOperation())->status_names[$request->status_id]];
        }

        $statuses_json = [];
        foreach ($statuses as $id => $stat) {
            $statuses_json[] = ['code' => $id . '', 'label' => $stat];
        }

        return response()->json($statuses_json);
    }

    public function get_types(Request $request)
    {
        $types = (new MaterialAccountingOperation())->type_names;

        if ($request->q) {
            $types = array_filter($types, function($type) use($request) {
                return stripos($type, $request->q) !== false;
            });
        }

        if ($request->type_id) {
            $types = [(new MaterialAccountingOperation())->type_names[$request->type_id]];
        }

        $types_json = [];
        foreach ($types as $id => $type) {
            $types_json[] = ['code' => $id . '', 'label' => $type];
        }

        return response()->json($types_json);
    }

    public function get_materials(Request $request)
    {
        $units = array_flip(MaterialAccountingOperationMaterials::getModel()->units_name);
        $materials = ManualMaterial::with('category');

        if ($request->withTrashed) {
            $materials->withTrashed();
        }

        if ($request->q) {
            $materials = $materials->where(function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->q . '%');
            });
        }

        $materials_json = [];
        $added_mat_bases = [];

        $date = $request->date ? Carbon::parse($request->date) : Carbon::today();
        if ($date > Carbon::now()) {
            $date = Carbon::today()->format('d.m.Y');
        } else {
            $date = $date->format('d.m.Y');
        }

        if ($request->base_id) {
            $bases = MaterialAccountingBase::where('object_id', $request->base_id)
                ->where('date', $date)
                ->where('count', '>', 0)
                ->with('material.category');

            if ($request->q) {
                $bases = $bases->whereHas('material', function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->q . '%');
                });
            }

            $bases = $bases->take(40)->get();
            foreach ($bases as $material) {
                $added_mat_bases[] = $material->id;
                $materials_json[] = [
                    'id' => $material->material->id,
                    'code' => $material->material->id,
                    'base_id' => $material->id,
                    'label' => $material->comment_name,
                    'used' => $material->used,
                    'unit' => $units[$material->material->category->unit_show ?? $material->material->category_unit]
                ];
            }

            if ($request->material_ids) {

                $existMaterials = MaterialAccountingBase::where('object_id', $request->base_id)
                    ->where('date', $date)
                    ->where('count', '>', 0)
                    ->whereNotIn('id', $bases->pluck('id'))
                    ->whereIn('manual_material_id', [$request->material_ids])
                    ->with('material.category');

                foreach ($existMaterials->get() as $material) {
                    $added_mat_bases[] = $material->id;
                    $materials_json[] = [
                        'id' => $material->material->id,
                        'base_id' => $material->id,
                        'code' => $material->material->id,
                        'label' => $material->comment_name,
                        'used' => $material->used,
                        'unit' => $units[$material->material->category->unit_show ?? $material->material->category_unit]
                    ];
                }
            }

            if (!$request->with_etc) {
                return response()->json($materials_json);
            }
        }

        $materials = ManualMaterial::with('category');

        if ($request->q) {
            $materials = $materials->where(function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->q . '%');
            });
        }

        $materials = $materials->take(40)->get();
        foreach ($materials as $material) {
            $materials_json[] = [
                'id' => $material->id,
                'code' => $material->id . '',
                'label' => $material->name,
                'unit' => $units[$material->category->unit_show ?? $material->category->category_unit]
            ];
        }

        if ($request->base_ids) {
            $mats = MaterialAccountingBase::find(array_diff($request->base_ids, $added_mat_bases));
            foreach ($mats as $material) {
                $materials_json[] = [
                    'id' => $material->material->id,
                    'base_id' => $material->id,
                    'code' => $material->material->id,
                    'label' => $material->comment_name,
                    'used' => $material->used,
                    'unit' => $units[$material->material->category->unit_show ?? $material->material->category_unit]
                ];
            }
        }

        if ($request->material_ids) {
            $mats = ManualMaterial::with('category')->whereIn('id', $request->material_ids)->whereNotIn('id', $materials->pluck('id'))->get();
            foreach ($mats as $material) {
                $materials_json[] = [
                    'id' => $material->id,
                    'code' => $material->id . '',
                    'label' => $material->name,
                    'unit' => $units[$material->category->unit_show ?? $material->category->category_unit]
                ];
            }
        }

        return response()->json($materials_json);
    }

    public function getMaterialCategoryDescription(Request $request)
    {
        $material = ManualMaterial::where('id', $request->id)
            ->with('category.documents')
            ->first();

        return response()->json(['status' => 'success', 'message' => $material->category->description ?? 'Нет описания', 'documents' => $material->category->documents]);
    }

    public function get_bases(Request $request)
    {
        $bases = MaterialAccountingBase::with('object', 'material')->where('count', '>', 0);

        if (Carbon::parse($request->date) < Carbon::today()) {
            $bases = $bases->where(function ($query) use ($request) {
                $query->where('date', Carbon::parse($request->date)->format('d.m.Y'));
            });
        }

        return response()->json(['result' => $bases->get()]);
    }

    public function get_base_comments(Request $request)
    {
        $base = MaterialAccountingBase::findOrFail($request->base_id);

        return response(['comments' => $base->comments]);
    }

    public function check_problem($operation_id)
    {
        $operation = MaterialAccountingOperation::findOrFail($operation_id);
        $operation->load(['materials.manual']);

        $message = [];

        foreach ($operation->materials->whereIn('type', [3, 7]) as $material) {
            $mat = ManualMaterial::where('manual_materials.id', $material->manual_material_id)
                ->with('parameters')
                ->leftJoin('manual_material_categories', 'manual_material_categories.id', 'manual_materials.category_id')
                ->select('manual_material_categories.id as cat_id', 'manual_material_categories.category_unit', 'manual_materials.*')
                ->first();

            $period = CarbonPeriod::create($operation->planned_date_from, Carbon::today());

            $count = $material->count;

            foreach ($period as $date) {
                $base = MaterialAccountingBase::where('object_id', $operation->object_id_from)
                    ->where('manual_material_id', $material['material_id'])
                    ->where('date', $date->format('d.m.Y'))
                    ->where('used', $material->used)
                    ->first();

                if (is_null($base)) {
                    $base = new MaterialAccountingBase();
                    $base->unit = $mat->category_unit;
                }
                if ($base->unit == $material->units_name[$material->unit]) {
                } else {

                    $convertParam = $mat->convert_from($material->units_name[$material->unit])->where('unit', $base->unit)->first()->value ?? 0;

                    if ($convertParam) {
                        $count = $count * $convertParam;
                    }
                }
            if (round($count, 3) > round($base->count,3) or !$base->count) {
                $text = 'Невозможно использовать ' . $mat->name . ($material->used ? ' Б/У': '') . '. Кол-во материала на объекте ' . $date->format('d.m.Y') . ': ' . (preg_replace('/\.\d{4}\K.+/', '', $base->count) ?: 0) . ' ' . $base->unit . '. ';

                    $message[] = $text;
                }
            }
        }

        if ($message == []) {
            return response()->json(['result' => ['Операция может быть выполнена']]);
        }

        return response()->json(['result' => $message]);
    }

    public function suggestSolution(Request $request)
    {
        $period = CarbonPeriod::create($request->planned_date_to, Carbon::today()->endOfDay());

        $materials_solutions = [
            'transform' => false,
            'failure' => false,
            ];

        foreach ($request->materials as $material_data) {
            $given_base_mat = null;
            if ($material_data['base_id'] != 'undefined' and $material_data['base_id'] != false) {
                $given_base_mat = MaterialAccountingBase::find($material_data['base_id']);
            }
            //put validation to request
            $given_manual_mat = ManualMaterial::find($material_data['material_id']);

            $given_count = $material_data['material_count'];
            $given_used = $material_data['used'] ? 1 : 0;
            $human_unit = (new MaterialAccountingOperation())->units_name[$material_data['material_unit']];


            $same_comments_without_date_used = MaterialAccountingBase::where([
                'manual_material_id' => $given_base_mat->manual_material_id ?? $given_manual_mat->id,
                'object_id' => $given_base_mat->object_id ?? $request->object_id,
            ]);

            if ($given_base_mat && $given_base_mat->comments) {
                foreach ($given_base_mat->comments as $comment) {
                    $same_comments_without_date_used->whereHas('comments', function($com_q) use ($comment) {
                        $com_q->where('comment', $comment->comment);
                    });
                }
                $same_comments_without_date_used->has('comments', $given_base_mat->comments->count());
            }

            foreach ($period as $date) {
                $date = $date->format('d.m.Y');

                $base_materials = (clone $same_comments_without_date_used)->where('date', $date)->get();

                if ($base_materials->count()) {
                    $base = $base_materials->where('used', $given_used)->first();
                    $alt_base = $base_materials->where('used', ! $given_used)->first();
                } elseif ($base_materials->where('used', !$given_used)->count()) {
                    $base = $base_materials->where('used', ! $given_used)->first();
                    $alt_base = $base_materials->where('used', $given_used)->first();
                } else {
                    $materials_solutions['failure'] = true;
                    $materials_solutions['solutions'][0] = [
                        'status' => 'failure',
                        'message' => "На объекте на {$date} отсутствует {$given_manual_mat->name}"
                    ];
                    continue;
                }
                $base_unit = $base->unit ?? $human_unit;

                $base_count = $base->count ?? 0;
                $base_to_given_mult = ($given_manual_mat->convert_from($base_unit)->where('unit', $human_unit)->first()->value ?? 1);
                if ($human_unit !== $base_unit) {
                    $base_count = $base_to_given_mult * $base_count;
                }

                $alt_base_unit = $alt_base->unit ?? $human_unit;
                $alt_base_count = $alt_base->count ?? 0;
                if ($human_unit !== $alt_base_unit) {
                   $alt_base_count = ($given_manual_mat->convert_from($alt_base_unit)->where('unit', $human_unit)->first()->value ?? 1) * $alt_base_count;
                }

                if ($base_count < $given_count) {
                    if ($alt_base) {
                        if (($alt_base_count + $base_count) < $given_count) {
                            $materials_solutions['failure'] = true;
                            $materials_solutions['solutions'][$alt_base->id] = [
                                'status' => 'failure',
                                'message' => "На {$date} недостаточно материала: " .
                                    "{$alt_base->material->name}. В сумме " .
                                    ($alt_base_count + $base_count) . " {$human_unit}, вы указали {$given_count}."
                            ];
                            continue;
                        }
                    }
                }

                if ($base_count >= $given_count) {
                    $materials_solutions['solutions'][$base->id] = [
                        'status' => 'ok'
                    ];
                } else {
                    if ($alt_base) {
                        $materials_solutions['transform'] = true;
                        $materials_solutions['solutions'][$alt_base->id] = [
                            'status' => 'transform',
                            'to' => $base->id ?? 'new',
                            'count' => ($given_count - $base_count) / $base_to_given_mult,
                            'message' => "На {$date} имеется " .
                                number_format($base_count, 3, '.', ' ') . " {$human_unit} {$alt_base->material->name} выбранного вами " . (!$alt_base->used ? 'б/у' : 'нового') . " материала. ".
                                number_format(($given_count - $base_count), 3, '.', ' ') . " {$human_unit} будет переведено в " . (!$alt_base->used ? 'б/у' : 'новый') . " автоматически."
                        ];
                    }
                }
            }
        }
        return response($materials_solutions);
    }

    public function doSolutions(Request $request)
    {
        $solutions = $request->solutions;
        DB::beginTransaction();

        foreach ($solutions as $alt_base_key => $solution) {
            if ($solution['status'] == 'transform') {
                $alt_base = MaterialAccountingBase::find($alt_base_key);

                if ($solution['to'] === 'new') {
                    $new_base = $alt_base->replicate();
                    $new_base->count = $solution['count'];
                    $new_base->used = !$new_base->used;
                    $new_base->save();
                    $alt_base->copyCommentsTo($new_base);
                    $new_base->ancestor_base_id = $new_base->id;
                } else {
                    $new_base = MaterialAccountingBase::find($solution['to']);
                    $new_base->count += $solution['count'];
                }
                $new_base->save();

                $alt_base->count -= $solution['count'];
                $alt_base->save();
            }
        }

        DB::commit();

        return response([
            'status' => 'success'
        ]);
    }

    public function close_operation($operation_id)
    {
        $operation = MaterialAccountingOperation::findOrFail($operation_id);

        if (in_array($operation->status, [1, 2, 4, 5, 8])) {
            DB::beginTransaction();

            $oldStatus = $operation->status;
            $operation->is_close = 1;
            $operation->status = 7;
            $operation->actual_date_to = Carbon::today()->format('d.m.Y');

            // only for write off
            $operation->checkControlTask('decline');

            // every operation
            $operation->generateOperationDeclineNotifications($oldStatus);

            foreach ($operation->materialsPart as $material) {
                $result = $material->deletePart();

                if ($result['status'] == 'error') {
                    return response()->json(['status' => 'error','message' => $result['message']]);
                }
            }

            $operation->save();

            DB::commit();

            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'error','message' => 'Нельзя отменить операцию']);
    }

    public function materials_count(Request $request)
    {
        $materials = ManualMaterial::with('convertation_parameters', 'category')->whereIn('id', json_decode($request->materials)->material_id)->get();
        $weight = 0;

        $js_materials = json_decode($request->materials);
        foreach ($js_materials->material_id as $key => $material_id) {
            $new_mat = $materials->where('id', $js_materials->material_id[$key])->first();

            if ($new_mat->category->category_unit == 'т' && $js_materials->material_unit[$key] == 'т') {
                $weight += $js_materials->material_count[$key];
            } else {
                $convers = $new_mat->convertation_parameters->where('unit', $js_materials->material_unit[$key])->first();
                if ($convers) {
                    $weight += $js_materials->material_count[$key] / $convers->value;
                }
            }
        }

        return response()->json($weight);
    }

    public function manual_transfer()
    {
        Artisan::call('mat_acc:transfer_base');
    }

    public function export_object_actions(OperationReportRequest $request)
    {
        $report = new ObjectActionReportExport($request->object_id);

        if ($request->start_date || $request->end_date) {
            $report->dateBetween($request->start_date, $request->end_date);
        }

        return $report->export();
    }

    public function redirector($operation_id)
    {
        $url = MaterialAccountingOperation::findOrFail($operation_id)->url;

        return redirect($url);
    }

    /**
     * This function check if we need to get
     * operation responsible project manager
     * @param Request $request
     * @return bool
     */
    public function weNeedToGetResponsibleRP(Request $request): bool
    {
        return boolval($request->responsible_RP);
    }

    /**
     * This function return operation responsible project
     * manager as JSON
     * @param Request $request
     * @param array $users_json
     * @return mixed
     */
    public function findResponsibleProjectManager(Request $request, array $users_json = [])
    {
        $only_one = User::find($request->responsible_RP);

        if ($only_one) {
            $users_json[] = ['code' => $only_one->id . '', 'label' => $only_one->full_name];
        }

        return response()->json($users_json);
    }

    public function attach_material(Request $request)
    {
        DB::beginTransaction();

        $result = collect();
        if (isset($request['attributes'])) {
            $material = ManualMaterial::getModel()->createMaterial($request['attributes'], $request->category_id);

            $material->load('category');
            $result = $material;
        } else {
            foreach ($request->materials as $raw_material) {
                $material = ManualMaterial::getModel()->createMaterial($raw_material, $request->category_id);

                $material->load('category');

                $material->used = $raw_material[0]['used'] ?? false;
                $material->unit = $raw_material[0]['unit'] + 0;
                $material->count = $raw_material[0]['count'];
                $result->push($material);
            }
        }
        DB::commit();

        return response()->json($result);
    }

    public function getSiblings(Request $request)
    {
        $base = MaterialAccountingBase::find($request->base_id);

        return response($base->siblings);
    }

    public function splitBase(SplitBaseRequest $request)
    {
        DB::beginTransaction();
        $main_base = MaterialAccountingBase::find($request->comment_id);
        $main_converted_count = $request->count * $main_base->material->getConvertValueFromTo($request->unit, $main_base->unit);

        if ($request->mode === 'split') {
            $split_base = $main_base->replicate();
            $split_base->save();
            $split_base->ancestor_base_id = $split_base->id;

            $split_base->count = $main_converted_count;

            foreach ($request->comments ?? [] as $comment) {
                $split_base->comments()->save(new Comment(['comment' => $comment]));
            }

            $split_base->save();
        } else {
            $target_mat = MaterialAccountingBase::find($request->mat_to_unite);

            $target_converted_count = $request->count * $main_base->material->getConvertValueFromTo($request->unit, $target_mat->unit);
            $target_mat->count += $target_converted_count;
            $target_mat->save();
        }

        $main_base->count -= $main_converted_count;
        if ($main_base->count < 0.0001) {
            $main_base->count = 0;
        }
        $main_base->save();


        DB::commit();

        return back();
    }

    public function moveToUsed(MaterialAccountingBaseMoveToUsedRequest $request)
    {
        DB::beginTransaction();

        $base = MaterialAccountingBase::findOrFail($request->base_id);
        $base->moveTo('used', $request);

        DB::commit();

        return response()->json(true);
    }

    public function moveToNew(MaterialAccountingBaseMoveToNewRequest $request)
    {
        DB::beginTransaction();

        $base = MaterialAccountingBase::findOrFail($request->base_id);
        $base->moveTo('new', $request);

        DB::commit();

        return response()->json(true);
    }

    public function getMaterialsFromBase(Request $request)
    {
        $bases = collect();

        if ($request->base_id) {
            $bases = MaterialAccountingBase::with('object', 'material')
                ->where('date', $request->date ? Carbon::parse($request->date)->format('d.m.Y') : Carbon::today()->format('d.m.Y'))
                ->where('object_id', $request->base_id)
                ->where('count', '>', 0)
                ->get()->each->setAppends(['comment_name', 'round_count', 'material_name', 'all_converted']);
        }

        return response()->json($bases);
    }

    public function certificatelessOperations()
    {
        $grantAllUsers = [
            User::HARDCODED_PERSONS['subCEO'],
            User::HARDCODED_PERSONS['CEO'],
            User::HARDCODED_PERSONS['certificateWorker'],
            User::HARDCODED_PERSONS['SYSTEMGOD'],
            User::HARDCODED_PERSONS['mainPTO']
        ];
        // Commented this because we notifying about all operations, not only closed
        $operations = MaterialAccountingOperation::where('status', '!=' , 7)->whereIn('type', [1, 4])->whereNotNull('contract_id')
            ->whereHas('materialsPartTo', function($part) {
                return $part->whereDoesntHave('certificates');
            })
            ->with(['object_from', 'object_to', 'author', 'sender', 'recipient', 'contract',
                'materialsPartTo' => function ($q) {
                    $q->doesntHave('certificates');
                },
                'materials' => function($q) {
                    $q->groupBy('manual_material_id', 'operation_id')->select('*')->with('manual');
                }
        ]);

        if (! in_array($user = auth()->id(), $grantAllUsers)) {
            $operations->where(function ($query) use ($user) {
                $query->where('author_id', $user)
                    ->orWhere('sender_id', $user)
                    ->orWhere('recipient_id', $user)
                    ->orWhereHas('responsible_users', function ($q) use ($user) {
                        $q->where('user_id', $user);
                    });
            });
        }

        if (request()->has('contract_id')) {
            $operations->where('contract_id', request()->get('contract_id'));
        }

        $operations = $operations->orderBy('id', 'desc')->get();

        $operations->each->append('total_weigth');

        return view('building.material_accounting.certificateless_operation_log', [
            'filter_params' => MaterialAccountingOperation::$filter,
            'operations' => $operations
        ]);
    }

    public function attach_contract(AttachContractRequest $request)
    {
        DB::beginTransaction();

        $operation = MaterialAccountingOperation::findOrFail($request->operation_id);
        $operation->contract_id = $request->contract_id;
        $operation->save();

        $task = Task::whereIn('status', [45])->findOrFail($request->task_id);
        $task->final_note = "Задача закрыта";
        $task->solve_n_notify();

        DB::commit();

        return ['status' => 'success'];
    }


    public function delete_object_from_session(Request $request)
    {
        $objects = array_unique(session()->pull('object_id', []));

        if(($key = array_search($request->object_id, $objects)) !== false) {
            unset($objects[$key]);
        }

        session()->put('object_id', $objects);

        return ['status' => 'success'];
    }

    public function print_bad_objects(Request $request)
    {
        $check = (new MaterialAccountingBadMaterilas())->check();
    }
}
