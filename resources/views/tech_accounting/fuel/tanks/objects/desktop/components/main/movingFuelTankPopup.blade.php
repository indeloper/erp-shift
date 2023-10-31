<script>

    const getMovingPopupContentTemplate = (rowData) => {
                return $('<div id="movingFuelTankFormPopup">').dxForm({
                    validationGroup: "documentValidationGroup",
                    labelMode: 'outside',
                    labelLocation: 'left',
                    items: [
                        {
                            dataField: "id",
                            editorType: "dxSelectBox",
                            editorOptions: {
                                dataSource: fuelTanksStore,
                                valueExpr: "id",
                                displayExpr: "tank_number",
                                value: rowData.id,
                                readOnly: true
                            },
                            label: {
                                text: 'Топливная емкость'
                            }, 
                        },
                        {
                            dataField: "object_id",
                            editorType: "dxSelectBox",
                            editorOptions: {
                                dataSource: projectObjectsStore,
                                valueExpr: "id",
                                displayExpr: "short_name"
                            },
                            label: {
                                text: 'Перемещение на объект'
                            }, 
                            validationRules: [{
                                type: 'required',
                                message: 'Укажите значение',
                            }],
                        },
                        {
                            dataField: "responsible_id",
                            editorType: "dxSelectBox",
                            editorOptions: {
                                dataSource: fuelTanksResponsiblesStore,
                                valueExpr: "id",
                                displayExpr: "user_full_name"
                            },
                            label: {
                                text: 'Передача ответственному'
                            }, 
                            validationRules: [{
                                type: 'required',
                                message: 'Укажите значение',
                            }],
                        },
                        
                        {
                            dataField: 'event_date',
                            editorType: "dxDateBox",
                            editorOptions: {
                                value: new Date(),
                                max: new Date(),
                                min: rowData.max_event_date
                            },
                            label: {
                                text: 'Дата отправки',
                            },
                            validationRules: [{
                                type: 'required',
                                message: 'Укажите значение',
                            }],
                        },
                        
                                        
                    ]
                })
            }


    const showMovingFuelTankPopup = (rowData) => {
        console.log(rowData);
        const movingFuelTankFormPopup = $("#externalPopup").dxPopup({
            visible: true,
            title: 'Перемещение емкости',
            contentTemplate: () => {return getMovingPopupContentTemplate(rowData)},
            toolbarItems: [
                {
                    widget: 'dxButton',
                    toolbar: 'bottom',
                    location: 'after',
                    options: {
                        text: 'Сохранить',
                    },
                    onClick(e) {
                        if (!DevExpress.validationEngine.validateGroup("documentValidationGroup").isValid) {
                            return;
                        }
                        formData = $('#movingFuelTankFormPopup').dxForm('instance').option('formData')
                        moveFuelTank(formData, movingFuelTankFormPopup)
                    }
                },
                {
                    widget: 'dxButton',
                    toolbar: 'bottom',
                    location: 'after',
                    options: {
                        text: 'Отмена',
                    },
                    onClick() {
                        movingFuelTankFormPopup.hide()
                    }
                }
            ]
            
        }).dxPopup('instance')
    }
</script>