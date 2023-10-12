<script>
    function showDecreaseFuelPopup(formItem = {}) {

        externalPopup.option({
            visible: true,
            title: 'Расход топлива',
            contentTemplate: () => {return getDecreaseFuelPopupContentTemplate(formItem)},
        })       
    }

    const getDecreaseFuelPopupContentTemplate = (formItem) => {
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
                        value: fuelFlowTypesStore.__rawData.find(el=>el.slug==='outcome').id
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
                    
                    dataField: 'our_technic_id',
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: fuelConsumersStore,
                        valueExpr: 'id',
                        displayExpr: 'name',
                    },
                    label: {
                        text: 'Потребитель'
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
                        min: 0.001
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

                {
                    item: 'simple',
                    template: (data, itemElement) => {
                        renderFileDisplayer(itemElement)
                    }
                },
             
            ]
        })
    }
</script>