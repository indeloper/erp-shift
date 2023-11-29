<script>
    function showMovingConfirmationTankPopup(fuelTankFormData) {
        popupMobile.option({
            visible: true,
            title: 'Подтверждение перемещения емкости',
            contentTemplate: () => {
                return getMovingConfirmationTankPopupContentTemplate(fuelTankFormData)
            },
            onContentReady(e) {
                $('#popupSaveButton').dxButton({
                    template: '<div class="text-color-blue">Подтвердить</div>'
                })
            }
        })
    }

    const getMovingConfirmationTankPopupContentTemplate = (fuelTankFormData) => {
        return $('<div id="externalForm">').dxForm({
            validationGroup: "documentExternalValidationGroup",
            labelMode: 'outside',
            labelLocation: 'left',
            formData: fuelTankFormData,
            items: [
                {
                    dataField: "id",
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: entitiesDataSource,
                        valueExpr: "id",
                        displayExpr: "tank_number",
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
                        displayExpr: "short_name",
                        value: fuelTankFormData.object_id,
                        readOnly: true
                    },
                    label: {
                        text: 'Принял на объекте'
                    },
                },
                {
                    dataField: "responsible_id",
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: fuelTanksResponsiblesStore,
                        valueExpr: "id",
                        displayExpr: "user_full_name",
                        readOnly: true
                    },
                    label: {
                        text: 'Ответственный'
                    },
                },
                {
                    dataField: 'fuel_level',
                    editorType: "dxNumberBox",
                    editorOptions: {
                        readOnly: true
                    },
                    label: {
                        text: 'Остаток топлива',
                    },
                },
                {
                    dataField: 'comment_movement_tmp',
                    editorType: "dxTextBox",
                    editorOptions: {
                        readOnly: true
                    },
                    label: {
                        text: 'Комментарий'
                    },
                },
                {
                    itemType: "group",
                    caption: 'Предыдущий объект',
                    items: [
                        {
                            dataField: "object_id",
                            editorType: "dxSelectBox",
                            editorOptions: {
                                dataSource: projectObjectsStore,
                                valueExpr: "id",
                                displayExpr: "short_name",
                                value: fuelTankFormData.previous_object_id,
                                readOnly: true
                            },
                            label: {
                                text: 'Объект'
                            },
                        },
                        {
                            dataField: "responsible_id",
                            editorType: "dxSelectBox",
                            editorOptions: {
                                dataSource: fuelTanksResponsiblesStore,
                                valueExpr: "id",
                                displayExpr: "user_full_name",
                                value: fuelTankFormData.previous_responsible_id,
                                readOnly: true
                            },
                            label: {
                                text: 'Ответственный'
                            },
                        },
                    ]
                }
                
            ]
        })
    }
</script>
