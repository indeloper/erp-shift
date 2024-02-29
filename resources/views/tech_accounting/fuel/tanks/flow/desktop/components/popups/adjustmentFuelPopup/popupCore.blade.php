<script>
    function showAdjustmentFuelPopup(formItem) {
        $('#mainPopup').dxPopup({
            visible: true,
            title: 'Корректировка остатков топлива',
            contentTemplate: () => {
                fuelFlowFormData = formItem
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

            onContentReady(e) {
                if(!editingRowId) {
                    const componentFormData = e.component.option('formData')
                    componentFormData.event_date = new Date()
                    e.component.option('formData', componentFormData)
                }
            },
            
            items: [
                {
                    visible: false,
                    dataField: 'fuel_tank_flow_type_id',
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: additionalResources.fuelFlowTypes,
                        valueExpr: 'id',
                        displayExpr: 'name',
                        value: additionalResources.fuelFlowTypes.find(el => el.slug === 'adjustment').id
                    }
                },
                {
                    dataField: 'fuel_tank_id',
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: getAvailableFuelTanksToCreateFlow('isAdjustmentPopup'),
                        valueExpr: 'id',
                        displayExpr: 'tank_number',
                        readOnly: editingRowId,
                        // onSelectionChanged(e) {
                        //     setEventDateSelectBoxOptions(e.selectedItem.id, 'eventDateSelectBox')
                        // }
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
                    // visible: false,
                    dataField: 'event_date',
                    editorType: "dxDateBox",
                    editorOptions: {
                        readOnly: editingRowId,
                        max: Date(),
                        // onInitialized(e) {
                        //     if(!editingRowId) {
                        //         e.component.option('value', new Date())
                        //     }
                        // }

                        // value: new Date()
                        // elementAttr: {
                        //     id: "eventDateSelectBox",
                        // },
                    },
                    label: {
                        text: 'Дата операции'
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
