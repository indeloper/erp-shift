<?php

namespace App\Services\Commerce;

use App\Models\CommercialOffer\CommercialOfferMaterialSplit;

class SplitService
{
    /**
     * @var CommercialOfferMaterialSplit
     */
    private $dummySplit;

    public function __construct()
    {
        $this->dummySplit = new CommercialOfferMaterialSplit();
    }

    public function splitMore(CommercialOfferMaterialSplit $old_split, $count, $type, $time = null, $comment = ''): CommercialOfferMaterialSplit
    {
        $type = $this->convertTypeToNumeric($type);

        if ($this->thisIsTheFirstSplitOfThisType($old_split, $type, $time)) {
            return $this->makeNewSplitFromParent($old_split, $count, $type, $time, $comment);
        } else {
            $existing_split = $old_split->children()->where('type', $type)->where('time', $time)->first() ?? $old_split->parent()->where('type', $type)->where('time', $time)->first();

            return $this->mergeRelatedSplitWithOldOne($old_split, $count, $existing_split);
        }
    }

    public function makeNewSplitFromParent(CommercialOfferMaterialSplit $old_split, $count, $type, $time = null, $comment = ''): CommercialOfferMaterialSplit
    {
        $new_split = $old_split->replicate();
        $new_split->count = $count;
        $new_split->time = $time;
        $new_split->type = $type;
        $new_split->price_per_one = 0;
        $new_split->result_price = 0;
        $new_split->comment = $comment;

        if (in_array($type, $this->dummySplit->modification_types)) {
            $old_split->children()->save($new_split);
        } else {
            $this->decreaseOldSplitCount($old_split, $type, $count);
        }
        $new_split->save();

        return $new_split;
    }

    public function mergeRelatedSplitWithOldOne(CommercialOfferMaterialSplit $source_split, $count, $target_split): CommercialOfferMaterialSplit
    {
        $target_split->count += $count;

        $this->decreaseOldSplitCount($source_split, $target_split->type, $count);

        $target_split->save();

        return $target_split;
    }

    /**
     * @return false|int|string
     */
    public function convertTypeToNumeric($type)
    {
        if (! is_numeric($type)) {
            $type = array_search($type, $this->dummySplit->english_types);
        }

        return $type;
    }

    /**
     * Check if there are any child splits of modification type without parent_id
     * if so, tries to find a parent or deletes this split
     */
    public function fixParentChildRelations($splits)
    {
        foreach ($splits->whereIn('type', $this->dummySplit->modification_types)->where('parent_id', null) as $orphan) {
            $potential_parent = $splits->whereIn('type', $this->dummySplit->parent_types)->where('man_mat_id', $orphan->man_mat_id)->first();
            if ($potential_parent) {
                $orphan->parent_id = $potential_parent->id;
                $orphan->save();
            }
        }

        return $splits->whereIn('type', $this->dummySplit->parent_types)->union($splits->where('parent_id', '!=', null));
    }

    private function thisIsTheFirstSplitOfThisType(CommercialOfferMaterialSplit $old_split, int $type, ?int $time = null): bool
    {
        return $old_split->parent()->where('type', $type)->where('time', $time)->doesntExist() and
                $old_split->children()->where('type', $type)->where('time', $time)->doesntExist();
    }

    private function decreaseOldSplitCount(CommercialOfferMaterialSplit $old_split, int $type, $count)
    {
        if (! in_array($type, $this->dummySplit->modification_types)) {
            $old_split->decreaseCountBy($count);
        }
    }

    public function makeHumanRentTime($time)
    {
        $complex_one = [1, 21, 31, 41, 51, 61, 71, 81, 91];
        $complex_two_three_four = [
            2, 3, 4,
            22, 23, 24,
            32, 33, 34,
            42, 43, 44,
            52, 53, 54,
            62, 63, 64,
            72, 73, 74,
            82, 83, 84,
            92, 93, 94,
        ];
        $floored_time = $time > 100 ? $time % 100 : $time;

        if (in_array($floored_time, $complex_one)) {
            return "$time месяц";
        } elseif (in_array($floored_time, $complex_two_three_four)) {
            return "$time месяца";
        } else {
            return "$time месяцев";
        }
    }
}
