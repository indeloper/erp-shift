<script>
    $(()=>{
        $("#dataGridAncor").dxForm({
            validationGroup: "documentValidationGroup",
            items: [
                {
                    itemType: "group",
                    caption: "{{$sectionTitle}}",
                    cssClass: "datagrid-container",
                    items: [
                        {
                            itemType: "empty",
                        },
                        {
                            itemType: "group",
                            colCount: 4,
                            items: [
                                {
                                    editorType: 'dxDateBox',
                                    dataField: 'date_from',
                                    label: {
                                        text: 'Дата с'
                                    },
                                    validationRules: [{
                                        type: 'required',
                                        message: 'Укажите значение',
                                    }],
                                   
                                },
                                {
                                    editorType: 'dxDateBox',
                                    dataField: 'date_to',
                                    label: {
                                        text: 'Дата по'
                                    },
                                    validationRules: [{
                                        type: 'required',
                                        message: 'Укажите значение',
                                    }],
                                },
                                {
                                    dataField: 'fuel_tank_id',
                                    editorType: "dxSelectBox",
                                    label: {
                                        text: 'Емкость'
                                    },
                                    editorOptions: {
                                        dataSource: fuelTanksStore,
                                        valueExpr: 'id',
                                        displayExpr: 'tank_number',
                                    },
                                    validationRules: [{
                                        type: 'required',
                                        message: 'Укажите значение',
                                    }],
                                },
                                
                                {
                                    editorType: 'dxButton',
                                    horizontalAlignment: 'right',
                                    editorOptions: {
                                        text: 'Сформировать',
                                        onClick: function () {
                                            if (!DevExpress.validationEngine.validateGroup("documentValidationGroup").isValid) {
                                                return;
                                            }
                                            const formData = $("#dataGridAncor").dxForm('instance').option('formData')
                                            const dateFrom = formData.date_from.toISOString()
                                            const dateTo = formData.date_to.toISOString()
                                            const fuelTankId = formData.fuel_tank_id
                                            const url = "{{route($routeNameFixedPart.'resource.index')}}?" + 'fuelTankId=' + fuelTankId + '&dateFrom=' + dateFrom + '&dateTo=' + dateTo
                                            window.open(url, '_blank');
                                        },
                                       
                                    }

                                }
                            ]
                        }
                        
                
                    ]
                }
            ]
        })
    })
</script>