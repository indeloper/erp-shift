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
                        dataSource: fuelTanksStore,
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
      
            ]
        })
    }

    const showMovingConfirmationFuelTankPopup = (fuelTankFormData) => {
        const movingConfirmationFuelTankFormPopup = $("#externalPopup").dxPopup({
            visible: true,
            title: 'Подтверждение перемещения емкости',
            
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
                        movemingFuelTankForm.hide()
                    }
                }
            ],
            contentTemplate: () => {return getConfirmPopupContentTemplate(fuelTankFormData)}
        }).dxPopup('instance')

        
    }
</script>