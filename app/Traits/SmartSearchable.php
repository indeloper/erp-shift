<?php

namespace App\Traits;

trait SmartSearchable
{
    private function formatSearchWord($searchWord)
    {
        return '%'.str_replace(' ', '%', $searchWord).'%';
    }

    private function getArrayOfSearchWords(string $searchString, string $delimiter = ' ')
    {
        return explode($delimiter, $searchString);
    }

    public function smartSearch($builderQuery, array $fields, string $searchText)
    {
        $searchWord = $this->formatSearchWord($searchText);
        $builderQuery->where(function ($query) use ($fields, $searchWord) {
            foreach ($fields as $field) {
                $query->orWhere($field, 'like', $this->formatSearchWord($searchWord));
            }
        });

        return $builderQuery;
    }
}
