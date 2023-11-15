<script>
    $(() => {
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
                            colCount: 3,
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
                                        searchEnabled: true,
                                        dataSource: fuelTanksStore,
                                        valueExpr: 'id',
                                        displayExpr: 'tank_number',
                                        onFocusIn() {
                                            clearCurrentLoadOptionsFilterParam('fuel_tank_id')
                                        }
                                    },
                                },

                                {
                                    dataField: 'object_id',
                                    editorType: "dxSelectBox",
                                    label: {
                                        text: 'Объект'
                                    },
                                    editorOptions: {
                                        searchEnabled: true,
                                        dataSource: projectObjectsStore,
                                        valueExpr: 'id',
                                        displayExpr: 'short_name',
                                        onFocusIn() {
                                            clearCurrentLoadOptionsFilterParam('object_id')
                                        }
                                    },
                                },

                                {
                                    dataField: 'responsible_id',
                                    editorType: "dxSelectBox",
                                    label: {
                                        text: 'Ответственный'
                                    },
                                    editorOptions: {
                                        searchEnabled: true,
                                        dataSource: fuelTanksResponsiblesStore,
                                        valueExpr: 'id',
                                        displayExpr: 'user_full_name',
                                        onFocusIn() {
                                            clearCurrentLoadOptionsFilterParam('responsible_id')
                                        }
                                    },
                                },


                                {
                                    editorType: 'dxButton',
                                    horizontalAlignment: 'right',
                                    editorOptions: {
                                        // elementAttr: {
                                        //     // width:'100%'
                                        // },
                                        text: 'Сформировать',
                                        onClick: function () {
                                            if (!DevExpress.validationEngine.validateGroup("documentValidationGroup").isValid) {
                                                return;
                                            }
                                            const formData = $("#dataGridAncor").dxForm('instance').option('formData')
                                            const dateFrom = new Date(formData.date_from).toLocaleString()
                                            const dateTo = new Date(formData.date_to).toLocaleString()
                                            
                                            if(formData.fuel_tank_id) {
                                                addFilterParamToCurrentLoadOptions('fuel_tank_id', formData.fuel_tank_id)
                                            }
                                            if(formData.object_id) {
                                                addFilterParamToCurrentLoadOptions('object_id', formData.object_id)
                                            }
                                            if(formData.responsible_id) {
                                                addFilterParamToCurrentLoadOptions('responsible_id', formData.responsible_id)
                                            }
                                            console.log('onSubmit', currentLoadOptions);
                                            let url = "{{route('building::tech_acc::fuel::reports::fuelFlowPeriodReport::'.'resource.index')}}?" + '&dateFrom=' + dateFrom + '&dateTo=' + dateTo + '&loadOptions=' + JSON.stringify(currentLoadOptions)

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
