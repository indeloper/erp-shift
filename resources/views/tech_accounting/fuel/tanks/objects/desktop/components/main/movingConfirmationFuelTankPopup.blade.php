<script>
    const getConfirmPopupContentTemplate = (fuelTankFormData) => {
        return $('<div id="movingConfirmationFuelTankFormPopup">').dxForm({
            formData: fuelTankFormData,
            validationGroup: "documentValidationGroup",
            labelMode: 'outside',
            labelLocation: 'left',
            items: [

                {
                    dataField: "id",
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: additionalResources.fuelTanks,
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
                        dataSource: additionalResources.projectObjects,
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
                        dataSource: additionalResources.fuelTanksResponsibles,
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

            ]
        })
    }

    const showMovingConfirmationFuelTankPopup = (fuelTankFormData) => {
        const movingConfirmationFuelTankFormPopup = $("#externalPopup").dxPopup({
            visible: true,
            title: 'Подтверждение перемещения емкости',
            fullScreen: DevExpress.devices.current().deviceType === 'phone',
            toolbarItems: [
                {
                    widget: 'dxButton',
                    toolbar: 'bottom',
                    location: 'after',
                    options: {
                        text: 'Подтвердить',
                    },
                    onClick(e) {
                        confirmMovingFuelTank(fuelTankFormData.id, movingConfirmationFuelTankFormPopup)
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
                        movingConfirmationFuelTankFormPopup.hide()
                    }
                }
            ],
            contentTemplate: () => {
                return getConfirmPopupContentTemplate(fuelTankFormData)
            }
        }).dxPopup('instance')


    }
</script>
