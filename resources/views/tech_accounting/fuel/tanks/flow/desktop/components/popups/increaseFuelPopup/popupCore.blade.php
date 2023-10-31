<script>
    function showIncreaseFuelPopup(formItem = {}) {
        $('#mainPopup').dxPopup({
            visible: true,
            title: 'Поступление топлива',
            contentTemplate: () => {return getIncreaseFuelPopupContentTemplate(formItem)},
        })
    }

    const getIncreaseFuelPopupContentTemplate = (formItem) => {
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
                        value: fuelFlowTypesStore.__rawData.find(el=>el.slug==='income').id
                    }
                },
                {
                    dataField: 'fuel_tank_id',
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: fuelTanksStore,
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
                    dataField: 'contractor_id',
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: fuelContractorsStore,
                        valueExpr: 'id',
                        displayExpr: 'short_name',
                        readOnly: editingRowId,
                    },
                    label: {
                        text: 'Поставщик'
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
                        min: 1,
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
                        {
                            type: 'range',
                            min: 1,
                            message: 'Минимальное значение 1',
                        }
                    ],
                },
                {
                    dataField: 'event_date',
                    editorType: "dxDateBox",
                    editorOptions: {
                        
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
                    dataField: 'document',
                    editorType: "dxTextBox",
                    label: {
                        text: 'Номер документа'
                    },
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