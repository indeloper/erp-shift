<script>
    function showAdjustmentFuelPopup(formItem) {
        $('#mainPopup').dxPopup({
            visible: true,
            title: 'Корректировка остатков топлива',
            contentTemplate: () => {return getAdjustmentFuelPopupContentTemplate(formItem)},
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
                        value: fuelFlowTypesStore.__rawData.find(el=>el.slug==='adjustment').id
                    }
                },
                {
                    dataField: 'fuel_tank_id',
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: fuelTanksStore,
                        valueExpr: 'id',
                        displayExpr: 'tank_number',
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
                        {
                            type: 'range',
                            min: 1,
                            message: 'Минимальное значение 1',
                        }
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
                    item: 'simple',
                    template: (data, itemElement) => {
                        renderFileUploader(itemElement)
                    }
                },
             
            ]
        })
    }
</script>