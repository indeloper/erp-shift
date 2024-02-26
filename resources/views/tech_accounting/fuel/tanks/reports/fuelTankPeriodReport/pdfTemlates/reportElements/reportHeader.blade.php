<p style="text-align: center">
    @if(($fuelTankModelInstance::find($fuelTankId) && $companyModelInstance::find($companyId)->logo) || ($fuelTankId === 'no_tank_direct_fuel_flow' && $companyModelInstance::find($companyId)->logo))
        <img
            style="width:220px;"
            src="{{asset('/')}}{{$companyModelInstance::find($companyId)->logo}}"
        >
    @endif
</p>
<p style="text-align: center">
    {{$companyModelInstance::find($companyId)->name}}
</p>

<div class="report-caption">
    @if($fuelTankId != 'no_tank_direct_fuel_flow')
        <span>Отчет по дизельному топливу<br>{{ $summaryData['dateFrom'] }} - {{ $summaryData['dateTo'] }}</span>
    @else
        <span>Отчет по прямым заправкам дизельного топлива<br>{{ $summaryData['dateFrom'] }} - {{ $summaryData['dateTo'] }}</span>
    @endif
</div>

<div style="margin-bottom: 8px;">
    @if($fuelTankId != 'no_tank_direct_fuel_flow')
        <b>№ емкости:</b> {{$fuelTankModelInstance::find($fuelTankId)->tank_number}}
    @endif
    <br>
    <b>Наименование объекта:</b> {{$objectModelInstance::find($objectId)->short_name}}
</div>