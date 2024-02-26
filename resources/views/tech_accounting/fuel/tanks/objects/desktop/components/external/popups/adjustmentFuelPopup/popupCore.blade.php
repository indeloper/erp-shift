<script>
    function showAdjustmentFuelPopup(formItem = {}) {
        externalPopup.option({
            visible: true,
            title: 'Корректировка остатков топлива',
            contentTemplate: () => {
                fuelFlowFormData = formItem
                return getAdjustmentFuelPopupContentTemplate(formItem)
            },
        })
    }

    const getAdjustmentFuelPopupContentTemplate = (formItem) => {
        return $('<div id="externalForm">').dxForm({
            validationGroup: "documentExternalValidationGroup",
            labelMode: 'outside',
            labelLocation: 'left',
            formData: formItem,
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
                    visible: false,
                    dataField: 'fuel_tank_id',
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: additionalResources.fuelTanks,
                        valueExpr: 'id',
                        displayExpr: 'tank_number',
                        value: editingRowId
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
                        readOnly: externalEditingRowId,
                        max: Date(),
                        // value: new Date()
                        // onContentReady(e) {
                        //     setEventDateSelectBoxOptions(editingRowId, e.component)
                        // },
                        onInitialized(e) {
                            if(!externalEditingRowId) {
                                e.component.option('value', new Date())
                            }
                        }
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
                        readOnly: externalEditingRowId,
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
                        readOnly: externalEditingRowId,
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
                //     itemType: "group",
                //     caption: 'Файлы',
                //     items: [
                //         {
                //             item: 'simple',
                //             template: (data, itemElement) => {
                //                 renderFileUploader(itemElement)
                //             }
                //         },

                //         {
                //             item: 'simple',
                //             template: (data, itemElement) => {
                //                 renderFileDisplayer(itemElement)
                //             }
                //         },
                //     ]
                // }

            ]
        })
    }
</script>
