<div class="avoid-break">
    <table style="width: 100%; margin-top: 20px;">
        <tr>
            <td style="padding: 5px 0;">Итого поступило топлива:</td>
            <td style="padding: 5px 0; text-align: right;">{{number_format($totlalOperationsValuesInstance->outcomesTotalAmount ?? 0, 0, ',', ' ')}}
                л
            </td>
            <td style="width: 50%"></td>
            <td></td>
        </tr>
        <tr>
            <td style="padding: 5px 0;">Итого израсходовано топлива:</td>
            <td style="padding: 5px 0; text-align: right;">{{number_format($totlalOperationsValuesInstance->outcomesTotalAmount ?? 0, 0, ',', ' ')}}
                л
            </td>
            <td style="width: 50%"></td>
            <td></td>
        </tr>
    </table>

    <table style="width: 100%; margin-top: 35px;">
        <tr>
            <td style="width: 40%;">Материально ответственное лицо:</td>
            <td style="width: 22%; border-bottom: 1px solid; padding-right:5px; text-align: center;">
                @php
                    $employee = $employeeModelInstance::where([
                            ['user_id', $responsibleId],
                            ['company_id', $companyId]
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
    
    @include('tech_accounting.fuel.tanks.reports.fuelTankPeriodReport.pdfTemlates.reportElements.reportFooter')
</div>