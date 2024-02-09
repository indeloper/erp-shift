
@include('tech_accounting.fuel.tanks.reports.fuelTankPeriodReport.pdfTemlates.reportElements.reportHeader')

@include('tech_accounting.fuel.tanks.reports.fuelTankPeriodReport.pdfTemlates.reportElements.reportTable')

@include('tech_accounting.fuel.tanks.reports.fuelTankPeriodReport.pdfTemlates.reportElements.reportFooter')

@if(!($loop->last && $loop->parent->last && $loop->parent->parent->last && $loop->parent->parent->parent->last))
    <pagebreak/>
@endif

