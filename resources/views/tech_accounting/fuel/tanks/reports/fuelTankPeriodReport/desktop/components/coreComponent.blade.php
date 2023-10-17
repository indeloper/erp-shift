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
                                    dataField: 'object_id',
                                    editorType: "dxSelectBox",
                                    label: {
                                        text: 'Объект'
                                    },
                                    editorOptions: {
                                        dataSource: projectObjectsStore,
                                        valueExpr: 'id',
                                        displayExpr: 'short_name',
                                    },
                                    validationRules: [{
                                        type: 'required',
                                        message: 'Укажите значение',
                                    }],
                                },

                                {
                                    dataField: 'responsible_id',
                                    editorType: "dxSelectBox",
                                    label: {
                                        text: 'Ответственный'
                                    },
                                    editorOptions: {
                                        dataSource: fuelTanksResponsiblesStore,
                                        valueExpr: 'id',
                                        displayExpr: 'user_full_name',
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
                                            // const dateTo = formData.date_to.toISOString()
                                            const fuelTankId = formData.fuel_tank_id
                                            const objectId = formData.object_id
                                            const responsibleId = formData.responsible_id

                                            const url = "{{route($routeNameFixedPart.'resource.index')}}?" + 'fuelTankId=' + fuelTankId + '&dateFrom=' + dateFrom + '&dateTo=' + dateTo + '&objectId=' + objectId + '&responsibleId=' + responsibleId
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