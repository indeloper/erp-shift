<head>
    <title>
        Отчет по дизельному топливу {{$dateFrom}} - {{$dateTo}}
    </title>

    <style>
        body {
            font-size: 14;
            font-family: 'calibri', sans-serif;
        }

        footer {
            position: fixed; 
            bottom: -40; 
            width: 100%;
            font-size: 12;
            color: grey;
        }

        .report-caption {
            font-size: 16;
            width: 100%;
            text-align: center;
            font-weight: bold;
        }

        .td-normal {
            border: 1px solid;
            padding: 5px 10px;
        }

        .td-center {
            border: 1px solid;
            padding: 5px 10px;
            text-align: center;
        }

        th {
            font-weight: normal;
        }

        .table-summary {
            color: #3f3f3f;
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .row-without-borders {
            border-left: 0;
            border-right: 0;
        }
    </style>
</head>


<body>

@foreach ($baseReportArray as $responsibleId=>$responsibleUserData)
    @foreach ($responsibleUserData as $fuelTankId=>$fuelTankIdData)
        @foreach ($fuelTankIdData as $objectId=>$objectData)
            @foreach($objectData as $objectTransferGroups)
                @php
                    $summaryData = $reportControllerInstance->getSummaryDataFuelFlowPeriodReport($objectTransferGroups, $responsibleId, $fuelTankId, $objectId, $dateFrom, $dateTo);
                    $incomesTotalAmount = 0;
                    $outcomesTotalAmount = 0;
                @endphp

                @if(!($loop->first && $loop->parent->first && $loop->parent->parent->first && $loop->parent->parent->parent->first))
                    <pagebreak/>
                @endif

                <p style="text-align: center">
                    @if($companyModelInstance::find($fuelTankModelInstance::find($fuelTankId)->company_id)->logo)
                        <img
                            style="width:220px;"
                            src="{{asset('/')}}{{$companyModelInstance::find($fuelTankModelInstance::find($fuelTankId)->company_id)->logo}}"
                        >
                    @endif
                </p>
                <p style="text-align: center">
                    {{$companyModelInstance::find($fuelTankModelInstance::find($fuelTankId)->company_id)->name}}
                </p>

                <div class="report-caption">
                    <span>Отчет по дизельному топливу<br>{{$dateFrom}} - {{$dateTo}}</span>
                </div>

                <div style="margin-bottom: 8px;">
                    <b>№ емкости:</b> {{$fuelTankModelInstance::find($fuelTankId)->tank_number}}
                    <br>
                    <b>Наименование объекта:</b> {{$objectModelInstance::find($objectId)->short_name}}
                </div>

                <table style="border-collapse: collapse; width: 100%; font-weight: normal">

                    <tr>
                        <th class="td-normal " rowspan="2" style="width:60%">
                            Наименование
                        </th>
                        <th colspan="2" class="td-normal" style="width:25%">
                            Документ
                        </th>
                        <th rowspan="2" class="td-normal" style="width:15%">
                            Количество топлива (л)
                        </th>

                    </tr>

                    <tr>
                        <th class="td-normal">
                            Дата
                        </th>
                        <th class="td-normal">
                            Номер
                        </th>
                    </tr>

                    <tr class="table-summary">
                        <td class="td-normal table-summary" colspan=3>
                            <b>Остаток в баке на {{$summaryData['dateFrom']}}</b>
                        </td>
                        <td class="td-normal table-summary" style="text-align:right">
                            <b>{{number_format($summaryData['fuelLevelPeriodStart'], 0, ',', ' ')}}</b>
                        </td>

                    </tr>

                    @php
                        $isEmptyIncomeRegionRendered = false;
                        $isEmptyOutcomeRegionRendered = false;
                    @endphp

                    @foreach ($objectTransferGroups as $flowTypeSlug=>$objectTransferGroup)
                        {{--
                                        @if(!$flowTypeSlug)
                                            @continue
                                        @endif
                        --}}

                        @if (!isset($objectTransferGroups['income']) && !$isEmptyIncomeRegionRendered)
                            @php $isEmptyIncomeRegionRendered = true; @endphp
                            <tr>
                                <td class="td-normal row-without-borders" colspan=4></td>
                            </tr>
                            <tr class="table-summary">
                                <td class="td-center table-summary" colspan=4>
                                    Приход
                                </td>
                            </tr>
                            <tr>
                                <td class="td-normal" colspan=4>Нет данных</td>
                            </tr>
                            <tr class="table-summary">
                                <td class="td-normal table-summary" colspan=3>
                                    Итого по остаткам
                                </td>
                                <td class="td-normal table-summary" style=" text-align:right">
                                    {{number_format($summaryData['fuelLevelPeriodStart'], 0, ',', ' ')}}
                                </td>
                            </tr>

                        @endif


                    {{--   @continue($flowTypeSlug === 'notIncludedTank' || !$flowTypeSlug) --}} 
                    

                        @if($flowTypeSlug==='income' || $flowTypeSlug==='outcome')
                            <tr>
                                <td class="td-normal row-without-borders" colspan=4></td>
                            </tr>

                            <tr class="table-summary">
                                <td class="td-center table-summary" colspan=4>
                                    @if(empty($isEmptyIncomeRegionRendered) && $flowTypeSlug==='income')
                                        Приход
                                    @endif
                                    @if(empty($isEmptyOutcomeRegionRendered) && $flowTypeSlug==='outcome')
                                        Расход
                                    @endif

                                </td>
                            </tr>
                        @endif

                        @foreach($objectTransferGroup as $fuelFlowOperation)

                            <tr>
                                <td class="td-normal" style="width:60%">
                                    @if($flowTypeSlug==='income')
                                        {{$fuelFlowOperation['contractor']}}
                                    @endif
                                    @if($flowTypeSlug==='outcome')
                                        {{$fuelFlowOperation['fuel_consumer']}}
                                    @endif
                                </td>
                                <td class="td-center">
                                    {{$fuelFlowOperation['event_date'] ? $carbonInstance::create($fuelFlowOperation['event_date'])->format('d.m.Y') : ''}}
                                </td>
                                <td class="td-normal">{{$fuelFlowOperation['document']}}</td>
                                <td class="td-normal"
                                    style="text-align:right">{{number_format($fuelFlowOperation['volume'], 0, ',', ' ')}}</td>
                            </tr>

                            @php
                                if($flowTypeSlug==='income') {
                                    $incomesTotalAmount += $fuelFlowOperation['volume'];
                                }
                                if($flowTypeSlug==='outcome') {
                                    $outcomesTotalAmount += $fuelFlowOperation['volume'];
                                }
                            @endphp

                        @endforeach

                        @if($incomesTotalAmount || $outcomesTotalAmount)
                            <tr class="table-summary">
                                <td class="td-normal table-summary">
                                    Итого по @if($flowTypeSlug==='income')
                                        приходу
                                    @else
                                        расходу
                                    @endif
                                </td>
                                <td class="td-center table-summary">
                                    ×
                                </td>
                                <td class="td-center table-summary">
                                    ×
                                </td>
                                <td class="td-normal table-summary" style="font-weight:bolder; text-align:right">
                                    <b>
                                        @if($flowTypeSlug==='income')
                                            {{number_format($incomesTotalAmount, 0, ',', ' ')}}
                                        @else
                                            {{number_format($outcomesTotalAmount, 0, ',', ' ')}}
                                        @endif
                                    </b>
                                </td>

                            </tr>
                        @endif

                        @if($flowTypeSlug==='income')
                            <tr class="table-summary">
                                <td class="td-normal table-summary">
                                    Итого по остаткам
                                </td>

                                <td class="td-center">
                                    ×
                                </td>
                                <td class="td-center">
                                    ×
                                </td>
                                <td class="td-normal table-summary" style=" text-align:right">
                                    {{number_format($incomesTotalAmount + $summaryData['fuelLevelPeriodStart'], 0, ',', ' ')}}
                                </td>
                            </tr>

                        @endif

                        @if (!isset($objectTransferGroups['outcome'])  && !$isEmptyOutcomeRegionRendered)
                            @php $isEmptyOutcomeRegionRendered = true; @endphp
                            <tr>
                                <td class="td-normal row-without-borders" colspan=4></td>
                            </tr>
                            <tr class="table-summary">
                                <td class="td-center" colspan=4>
                                    Расход
                                </td>
                            </tr>
                            <tr>
                                <td class="td-normal" colspan=4>Нет данных</td>
                            </tr>
                        @endif

                    @endforeach


                    <tr>
                        <td class="td-normal row-without-borders" colspan=4></td>
                    </tr>

                    <tr class="table-summary">
                        <td class="td-normal table-summary" colspan=3>
                            <b>Остаток в баке на {{$summaryData['dateTo']}}</b>
                        </td>
                        <td class="td-normal table-summary" style="font-weight:bolder; text-align:right">
                            <b>{{number_format($summaryData['fuelLevelPeriodFinish'], 0, ',', ' ')}}</b>
                        </td>

                    </tr>

                </table>

                <table style="width: 100%; margin-top: 20px;">
                    <tr>
                        <td style="padding: 5px 0;">Остаток на начало периода:</td>
                        <td style="padding: 5px 0; text-align: right;">{{number_format($summaryData['fuelLevelPeriodStart'], 0, ',', ' ')}}
                            л
                        </td>
                        <td style="padding: 5px 0 5px 20px; ">Итого поступило топлива:</td>
                        <td style="padding: 5px 0; text-align: right">{{number_format($incomesTotalAmount ?? 0, 0, ',', ' ')}}
                            л
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;">Остаток на конец периода:</td>
                        <td style="padding: 5px 0; text-align: right">{{number_format($summaryData['fuelLevelPeriodFinish'], 0, ',', ' ')}}
                            л
                        </td>
                        <td style="padding:  5px 0 5px 20px;">Итого израсходовано топлива:</td>
                        <td style="padding: 5px 0; text-align: right">{{number_format($outcomesTotalAmount ?? 0, 0, ',', ' ')}}
                            л
                        </td>
                    </tr>
                </table>
                
                @foreach($summaryData['confirmedTankMovements'] as $event)
                    @continue(!isset($event['object_id']))
                    @continue($event['object_id']===$event['previous_object_id'])
                    @continue(!$objectModelInstance::find($event['previous_object_id']))
                    @continue(!$objectModelInstance::find($event['tank_moving_confirmation']))
                    
                    <table style="margin-top: 10px;">
                        <tr>
                            <td>
                                <b>
                                    {{$event['event_date'] ? $carbonInstance::create($event['event_date'])->format('d.m.Y') : ''}}
                                </b>
                                Емкость № {{$fuelTankModelInstance::find($fuelTankId)->tank_number}}
                                @if($event['object_id'] === $objectId)
                                    перемещена с объекта
                                    <b>{{$objectModelInstance::find($event['previous_object_id'])->short_name}}</b>
                                @else
                                    перемещена на объект
                                    <b>{{$objectModelInstance::find($event['object_id'])->short_name}}</b>
                                @endif
                            </td>
                        </tr>
                    </table>

                @endforeach


                <table style="width: 100%; margin-top: 35px;">
                    <tr>
                        <td style="width: 40%;">Материально ответственное лицо:</td>
                        <td style="width: 22%; border-bottom: 1px solid; padding-right:5px; text-align: center;">
                            @php
                                $employee = $employeeModelInstance::where([
                                        ['user_id', $responsibleId],
                                        ['company_id', $fuelTankModelInstance::find($fuelTankId)->company_id]
                                    ])->first();

                                if($employee) {
                                    $employeePosition = $employees1cPostModelInstance::find(
                                        $employee->employee_1c_post_id
                                    )->name;
                                } else {
                                    $employeePosition = '';
                                }
                            @endphp

                            {{$employeePosition}}

                        </td>
                        <td style="width:2%"></td>
                        <td style="width: 12%; border-bottom: 1px solid"></td>
                        <td style="width:2%"></td>
                        <td style="width: 22%; border-bottom: 1px solid;  text-align: center;">
                            {{$userModelInstance::find($responsibleId)->user_full_name }}
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td style=" text-align: center; font-size: 10; color: #3f3f3f"><i>должность</i></td>
                        <td></td>
                        <td style=" text-align: center; font-size: 10; color: #3f3f3f"><i>подпись</i></td>
                        <td></td>
                        <td style="text-align: center; font-size: 10; color: #3f3f3f"><i>Ф.И.О.</i></td>
                    </tr>
                    <tr>
                        <td style="width: 40%; padding-top: 25px;">Отчет с документами принял и проверил:</td>
                        <td style="width: 22%; border-bottom: 1px solid; padding-right:5px; padding-top: 25px;"></td>
                        <td style="width:2%; padding-top: 25px;"></td>
                        <td style="width: 12%; border-bottom: 1px solid; padding-top: 25px;"></td>
                        <td style="width:2%; padding-top: 25px;"></td>
                        <td style="width: 22%; border-bottom: 1px solid;  text-align: center; padding-top: 25px;">
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td style=" text-align: center; font-size: 10; color: #3f3f3f"><i>должность</i></td>
                        <td></td>
                        <td style=" text-align: center; font-size: 10; color: #3f3f3f"><i>подпись</i></td>
                        <td></td>
                        <td style="text-align: center; font-size: 10; color: #3f3f3f"><i>Ф.И.О.</i></td>
                    </tr>
                </table>

                <footer>{{$carbonInstance::create(now())->format('d.m.Y H:i')}}, {{$userModelInstance::find(Auth::user()->id)->user_full_name}}</footer>

            @endforeach
        @endforeach
    @endforeach
@endforeach
</body>
