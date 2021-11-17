<?php

namespace App\Traits;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

trait DevExtremeDataSourceLoadable
{
    protected $logicalOperators = ['and' => 'and',
        'or' => 'or'];

    protected $conditionOperators = [
        '=' => '=',
        '<>' => '<>',
        ">" => '>',
        '>=' => '>=',
        "<" => '<',
        '<=' => '<=',
        'startswith' => 'like',
        'endswith' => 'like',
        'contains' => 'like',
        'notcontains' => 'not like'
    ];

    /**
     * Форматирует значение согласно логике оператора сравнения
     * @param $value
     * @param $conditionOperator
     */
    protected function formatValue($value, $conditionOperator)
    {
        switch ($conditionOperator) {
            case 'startswith':
                return DB::raw("upper('$value%')");
            case 'endswith':
                return DB::raw("upper('%$value')");
            case 'notcontains':
            case 'contains':
                return DB::raw("upper('%$value%')");
            default:
                return $value;
        }
    }

    /**
     * Форматирует наименование поля согласно логике оператора сравнения
     * @param $fieldName
     * @param $conditionOperator
     */
    protected function formatField($fieldName, $conditionOperator)
    {
        switch ($conditionOperator) {
            case 'startswith':
            case 'endswith':
            case 'contains':
            case 'notcontains':
                return DB::raw("upper(`$fieldName`)");
            default:
                return $fieldName;
        }
    }

    /**
     * Формирует массив для фильтрации основываясь на поиске
     * @param $query
     * @param string $searchOperation
     * @param string $searchValue
     * @param $searchExpr
     */
    protected function appendSearchOperation($query, string $searchOperation, string $searchValue, $searchExpr)
    {
        $filter = [];

        $searchValues = explode(' ', $searchValue);

        $valuesCounter = count($searchValues) - 1;
        foreach ($searchValues as $valueKey => $value) {
            $filterOperation = [];
            $filterOperation[] = $searchExpr;
            $filterOperation[] = $searchOperation;
            $filterOperation[] = $value;

            $filter[] = $filterOperation;

            if (!($valuesCounter == $valueKey)){
                $filter[] = 'and';
            }
        }

        return $this->appendFilter($query, $filter);
    }

    /**
     * Транслирует полученный массив filter, добавляя их к запросу.
     * Получаемый массив может быть нескольких типов:
     *   С одним условием: "filter":[["id","<>",35]]
     *   С группой условий: "filter":[["id","<>",35],"and",["operation_route_id","=",2]]
     *   Со вложенными условиями: "filter":[["id","<>",35],"and",[["date_start",">=","2020/11/03 00:00:00"],"and",["date_start","<=","2020/11/04 00:00:00"]]]
     *
     * @param $query
     * @param $filterArray
     * @param string $logicalOperator
     * @return mixed
     */
    protected function appendFilter($query, $filterArray, string $logicalOperator = '')
    {
        $result = $query;

        $isFilterConditionSimple = !is_array($filterArray[0]) && count($filterArray) == 3;

        if ($isFilterConditionSimple) {
            $translatedFilterItem = array(
                'fieldName' => $filterArray[0],
                'operator' => $filterArray[1],
                'value' => $filterArray[2]);

            $result->where(
                $this->formatField($translatedFilterItem['fieldName'], $translatedFilterItem['operator']),
                $this->conditionOperators[$translatedFilterItem['operator']],
                $this->formatValue($translatedFilterItem['value'], $translatedFilterItem['operator'])
            );
        } else {
            foreach ($filterArray as $filterItem) {
                if (is_array($filterItem)) {
                    switch ($logicalOperator) {
                        case 'and':
                            $result->where(function ($query) use ($filterItem) {
                                $this->appendFilter($query, $filterItem);
                            });
                            break;
                        case 'or':
                            $result->orWhere(function ($query) use ($filterItem) {
                                $this->appendFilter($query, $filterItem);
                            });
                            break;
                        default:
                            $result->where(function ($query) use ($filterItem) {
                                $this->appendFilter($query, $filterItem);
                            });
                    }
                } else {
                    $logicalOperator = $this->logicalOperators[$filterItem];
                }
            }
        }

        return $result;
    }

    /**
     * Добавляет в запрос условия, генерируемые в loadOptions метода load класса CustomDataStore компонент DevExtreme
     *
     * @param object $loadOption
     * @return Builder|static
     */
    public function dxLoadOptions($loadOption)
    {
        $result = $this::query();

        if (isset($loadOption->searchOperation) && ($loadOption->searchValue != null)) {
            $this->appendSearchOperation($result, $loadOption->searchOperation, $loadOption->searchValue, $loadOption->searchExpr);
        }

        if (isset($loadOption->skip) && isset($loadOption->take)) {
            $result = $result->skip($loadOption->skip)->take($loadOption->take);
        }

        if (isset($loadOption->sort)) {
            foreach ($loadOption->sort as $sortElement) {
                $result = $result->orderBy($sortElement->selector, $sortElement->desc ? 'desc' : 'asc');
            }
        }

        if (isset($loadOption->filter) && count($loadOption->filter) != 0) {
            $this->appendFilter($result, $loadOption->filter);

        }
        return $result;
    }
}
