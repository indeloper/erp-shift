<head>
    <title>
        Отчет по дизельному топливу
    </title>

    <style>
        body {
            font-size: 14;
            font-family: 'calibri', sans-serif;
        }

        footer {
            width: 100%;
            font-size: 12;
            color: grey;
        }

        .avoid-break {
            page-break-inside: avoid;
        }

        @page {
            header: page-header;
            footer: page-footer;
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
    @php
        class totlalOperationsValues {
            public $incomesTotalAmount;
            public $outcomesTotalAmount;
            public function __construct()
            {
                $this->incomesTotalAmount = 0;
                $this->outcomesTotalAmount = 0;
            }
        }
    @endphp
    @foreach ($baseReportArray as $responsibleId=>$responsibleUserData)
        @foreach ($responsibleUserData as $fuelTankId=>$fuelTankIdData)
            @foreach ($fuelTankIdData as $objectId=>$objectData)
                @foreach($objectData as $key=>$objectTransferGroups)
                    @php
                        $summaryData = $reportControllerInstance->getSummaryDataFuelFlowPeriodReport($objectTransferGroups, $responsibleId, $fuelTankId, $objectId, $dateFrom, $dateTo);
                        $totlalOperationsValuesInstance = new totlalOperationsValues();
                        $companyId = $fuelTankId != 'no_tank_direct_fuel_flow' ?
                            $fuelTankModelInstance::find($fuelTankId)->company_id
                            : array_keys($objectData)[0];
                    @endphp

                    @continue(!$summaryData)
                    @include('tech_accounting.fuel.tanks.reports.fuelTankPeriodReport.pdfTemlates.reportTemplateLayout')

                @endforeach
            @endforeach
        @endforeach
    @endforeach
</body>
