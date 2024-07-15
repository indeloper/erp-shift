<?php

namespace App\Services\TechAccounting;

use App\Models\FileEntry;
use App\Models\TechAcc\CategoryCharacteristic;
use App\Models\TechAcc\OurTechnic;
use App\Models\TechAcc\OurTechnicTicket;
use App\Models\TechAcc\TechnicCategory;
use Illuminate\Support\Facades\DB;

class TechnicCategoryService
{
    public function createTechnicCategoryWithCharacteristics(array $attributes)
    {
        DB::beginTransaction();

        $technic = TechnicCategory::create($attributes);
        $characteristics = [];

        if (isset($attributes['characteristics'])) {
            foreach ($attributes['characteristics'] as $characteristic_attrs) {
                $characteristics[] = CategoryCharacteristic::create($characteristic_attrs)->id;
            }
        }

        $technic->addCharacteristic($characteristics);
        DB::commit();
    }

    public function updateTechnicCategoryWithCharacteristics(array $attributes, $technic_category_id)
    {
        DB::beginTransaction();
        $technic = TechnicCategory::find($technic_category_id);
        $technic->update($attributes);

        $characteristics = [];

        if (isset($attributes['characteristics'])) {
            foreach ($attributes['characteristics'] as $characteristic_attrs) {
                if ($characteristic_attrs['id'] > 0) {
                    CategoryCharacteristic::find($characteristic_attrs['id'])->update($characteristic_attrs);
                } else {
                    $characteristics[] = CategoryCharacteristic::create($characteristic_attrs)->id;
                }

            }
        }
        if (isset($attributes['deleted_characteristic_ids'])) {
            CategoryCharacteristic::whereIn('id', $attributes['deleted_characteristic_ids'])->delete();
        }
        $technic->addCharacteristic($characteristics);

        DB::commit();
    }

    public function createOurTechnicWithAttributes($attributes)
    {
        DB::beginTransaction();

        if (isset($attributes['technic_category'])) {
            $category = TechnicCategory::query()->where('name', $attributes['technic_category'])->first();

            $attributes['technic_category_id'] = $category ? $category->id : 0;
        }
        if (isset($attributes['exploitation_start'])) {
            $attributes['exploitation_start'] = \Carbon\Carbon::parse($attributes['exploitation_start']);
        }

        $our_technic = OurTechnic::create($attributes);

        if (isset($attributes['characteristics'])) {
            $our_technic->setCharacteristicsValue($attributes['characteristics']);
            $our_technic->save();
        }

        if (isset($attributes['file_ids'])) {
            $files = FileEntry::find($attributes['file_ids']);
            $our_technic->documents()->saveMany($files);
        }

        DB::commit();
        $our_technic->refresh();
        $our_technic->load('documents', 'category_characteristics', 'start_location');

        return $our_technic;
    }

    public function updateOurTechnicWithAttributes(OurTechnic $our_technic, $attributes)
    {
        DB::beginTransaction();

        if (isset($attributes['technic_category'])) {
            $category = TechnicCategory::query()->where('name', $attributes['technic_category'])->first();

            $attributes['technic_category_id'] = $category ? $category->id : 0;
        }
        if (isset($attributes['exploitation_start'])) {
            $attributes['exploitation_start'] = \Carbon\Carbon::parse($attributes['exploitation_start']);
        }

        $our_technic->update($attributes);

        if (isset($attributes['characteristics'])) {
            $our_technic->category_characteristics()->detach();
            $our_technic->setCharacteristicsValue($attributes['characteristics']);
        }

        if (isset($attributes['file_ids'])) {
            $files = FileEntry::find($attributes['file_ids']);
            $our_technic->documents()->saveMany($files);
        }

        $our_technic->push();
        DB::commit();
        $our_technic->refresh();
        $our_technic->load('documents', 'category_characteristics', 'start_location');

        return $our_technic;
    }

    public function collectDataForTransport(array $request, $category_id)
    {
        $owners = OurTechnic::$owners;
        $statuses = OurTechnic::$statuses;

        $category = TechnicCategory::with('category_characteristics')->findOrFail($category_id);
        $empty_model = OurTechnicTicket::getModel();
        $specializations = $empty_model->specializations;

        $paginated = $category->technics()->filter($request)->with('category_characteristics', 'documents', 'start_location')->paginate(15);
        $technics = $paginated->items();
        $technicsCount = $paginated->total();

        return ['data' => compact(['owners', 'category', 'technics', 'technicsCount', 'statuses', 'specializations'])];
    }

    public function collectDataForTrashedTransport(array $request, $category_id)
    {
        $owners = OurTechnic::$owners;
        $statuses = OurTechnic::$statuses;

        $category = TechnicCategory::withTrashed()->with('category_characteristics')->findOrFail($category_id);

        $paginated = $category->technics()->onlyTrashed()->filter($request)->with('category_characteristics', 'documents', 'start_location')->paginate(15);
        $technics = $paginated->items();
        $technicsCount = $paginated->total();

        return ['data' => compact(['owners', 'category', 'technics', 'technicsCount', 'statuses'])];
    }
}
