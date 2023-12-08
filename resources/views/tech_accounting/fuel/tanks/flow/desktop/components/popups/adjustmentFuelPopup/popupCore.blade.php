<script>
    function showAdjustmentFuelPopup(formItem) {
        $('#mainPopup').dxPopup({
            visible: true,
            title: 'Корректировка остатков топлива',
            contentTemplate: () => {
                return getAdjustmentFuelPopupContentTemplate(formItem)
            },
        })
    }

    const getAdjustmentFuelPopupContentTemplate = (formItem) => {
        return $('<div id="mainForm">').dxForm({
            validationGroup: "documentValidationGroup",
            labelMode: 'outside',
            labelLocation: 'left',
            formData: formItem,
            items: [
                {
                    visible: false,
                    dataField: 'fuel_tank_flow_type_id',
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: fuelFlowTypesStore,
                        valueExpr: 'id',
                        displayExpr: 'name',
                        value: fuelFlowTypesStore.__rawData.find(el => el.slug === 'adjustment').id
                    }
                },
                {
                    dataField: 'fuel_tank_id',
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: getAvailableFuelTanksForFlowOperations(),
                        valueExpr: 'id',
                        displayExpr: 'tank_number',
                        readOnly: editingRowId,
                    },
                    label: {
                        text: 'Емкость'
                    },
                    validationRules: [{
                        type: 'required',
                        message: 'Укажите значение',
                    }],
                },

                {
                    dataField: 'volume',
                    editorType: "dxNumberBox",
                    editorOptions: {
                        readOnly: editingRowId,
                        format: "#0 л"
                    },
                    label: {
                        text: 'Объем'
                    },
                    validationRules: [
                        {
                            type: 'required',
                            message: 'Укажите значение',
                        },
                    ],
                },

                {
                    dataField: 'comment',
                    editorType: "dxTextBox",
                    editorOptions: {
                        readOnly: Boolean(isFuelFlowDataFieldUpdateAvailable('comment')),
                    },
                    label: {
                        text: 'Комментарий'
                    },
                },

                {
                    visible: false,
                    dataField: 'event_date',
                    editorType: "dxDateBox",
                    editorOptions: {
                        readOnly: editingRowId,
                        value: new Date(),
                    },
                    label: {
                        text: 'Дата операции'
                    },
                    validationRules: [{
                        type: 'required',
                        message: 'Укажите значение',
                    }],
                },

                // {
                //     item: 'simple',
                //     template: (data, itemElement) => {
                //         renderFileUploader(itemElement)
                //     }
                // },

            ]
        })
    }
</script>
