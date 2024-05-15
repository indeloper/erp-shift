<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait AdditionalFunctions
{
    /**
     * This function checks that all values
     * in given $array are the same as given $value
     *
     * @param  array  $array
     * @return bool
     */
    public function all_values_in_array_are($value, array $array): bool
    {
        return boolval(reset($array) == $value && count(array_unique($array)) == 1);
    }

    /**
     * Function create new request from given array
     */
    public function createNewRequest(array $output): Request
    {
        $request_payload = collect($output)->except('page');

        $newRequest = new Request([
            'filters' => $request_payload->keys()->toArray(),
            'values' => $request_payload->values()->toArray(),
        ]);

        return $newRequest;
    }
}
