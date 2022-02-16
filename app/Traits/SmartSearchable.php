<?php

namespace App\Traits;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

trait SmartSearchable
{
    private function formatSearchWord($searchWord){
        return '%'.str_replace(' ', '%', $searchWord).'%';
    }

    private function getArrayOfSearchWords(string $searchString, string $delimiter = ' '){
        return explode($delimiter, $searchString);
    }

    public function smartSearch(array $fields, string $searchText){
        //dd($this);
        $result = $this::query();

        $searchWords = $this->getArrayOfSearchWords($searchText);

        foreach ($fields as $field){
            foreach ($searchWords as $searchWord){
                $result->where(function ($query) use ($field, $searchWord) {
                    $query->orWhere($this->formatSearchWord($searchWord));
                });
            }
        }
    }
}
