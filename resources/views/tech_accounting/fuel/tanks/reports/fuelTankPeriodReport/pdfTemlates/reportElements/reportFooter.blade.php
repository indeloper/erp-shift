<table style="width: 100%; margin-top: 20px;">
    <tr>
        <td style="padding: 5px 0;">Остаток на начало периода:</td>
        <td style="padding: 5px 0; text-align: right;">{{number_format($summaryData['fuelLevelPeriodStart'], 0, ',', ' ')}}
            л
        </td>
        <td style="padding: 5px 0 5px 20px; ">Итого поступило топлива:</td>
        <td style="padding: 5px 0; text-align: right">{{number_format($totlalOperationsValuesInstance->incomesTotalAmount ?? 0, 0, ',', ' ')}}
            л
        </td>
    </tr>
    <tr>
        <td style="padding: 5px 0;">Остаток на конец периода:</td>
        <td style="padding: 5px 0; text-align: right">{{number_format($summaryData['fuelLevelPeriodFinish'], 0, ',', ' ')}}
            л
        </td>
        <td style="padding:  5px 0 5px 20px;">Итого израсходовано топлива:</td>
        <td style="padding: 5px 0; text-align: right">{{number_format($totlalOperationsValuesInstance->outcomesTotalAmount ?? 0, 0, ',', ' ')}}
            л
        </td>
    </tr>
</table>

@foreach($summaryData['confirmedTankMovements'] as $event)
    @continue(!isset($event['object_id']))
    @continue($event['object_id']===$event['previous_object_id'])
    @continue(!$objectModelInstance::find($event['previous_object_id']))
    @continue(!$objectModelInstance::find($event['tank_moving_confirmation']))
    @continue($event['object_id'] != $objectId && $event['previous_object_id'] != $objectId)
    
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

<footer>
    {{$carbonInstance::create(now())->format('d.m.Y H:i')}}, 
    {{$userModelInstance::find(Auth::user()->id)->user_full_name}},
    отчет за период {{$dateFrom}} - {{$dateTo}}
</footer>
