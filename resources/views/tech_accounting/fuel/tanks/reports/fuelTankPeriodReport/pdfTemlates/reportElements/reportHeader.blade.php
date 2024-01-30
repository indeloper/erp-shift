<p style="text-align: center">
    @if($fuelTankModelInstance::find($fuelTankId) && $companyModelInstance::find($fuelTankModelInstance::find($fuelTankId)->company_id)->logo)
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
    <span>Отчет по дизельному топливу<br>{{ $summaryData['dateFrom'] }} - {{ $summaryData['dateTo'] }}</span>
</div>

<div style="margin-bottom: 8px;">
    <b>№ емкости:</b> {{$fuelTankModelInstance::find($fuelTankId)->tank_number}}
    <br>
    <b>Наименование объекта:</b> {{$objectModelInstance::find($objectId)->short_name}}
</div>