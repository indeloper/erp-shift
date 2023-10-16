<script>
    function showAdjustmentFuelPopup(formItem) {
        externalPopup.option({
            visible: true,
            title: 'Корректировка остатков топлива',
            contentTemplate: () => {return getAdjustmentFuelPopupContentTemplate(formItem)},
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
                        dataSource: fuelFlowTypesStore,
                        valueExpr: 'id',
                        displayExpr: 'name',
                        value: fuelFlowTypesStore.__rawData.find(el=>el.slug==='adjustment').id
                    }
                },
                {
                    visible: false,
                    dataField: 'fuel_tank_id',
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: fuelTanksStore,
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
                    dataField: 'volume',
                    editorType: "dxNumberBox",
                    editorOptions: {
                        // min: 0.001
                    },
                    label: {
                        text: 'Объем (л)'
                    },
                    validationRules: [
                        {
                            type: 'required',
                            message: 'Укажите значение',
                        },
                        
                    ],
                },
                {
                    itemType: "group",
                    caption: 'Документ',
                    colCount: 2,
                    items: [
                        {
                            dataField: 'document',
                            editorType: "dxTextBox",
                            label: {
                                text: 'Номер'
                            },
                        },
                        {
                            dataField: 'document_date',
                            editorType: "dxDateBox",
                            label: {
                                text: 'Дата'
                            },
                        }
                    ]
                },
                {
                    itemType: "group",
                    caption: 'Файлы',
                    items: [
                        {
                            item: 'simple',
                            template: (data, itemElement) => {
                                renderFileUploader(itemElement)
                            }
                        },

                        {
                            item: 'simple',
                            template: (data, itemElement) => {
                                renderFileDisplayer(itemElement)
                            }
                        },
                    ]
                }
             
            ]
        })
    }
</script>