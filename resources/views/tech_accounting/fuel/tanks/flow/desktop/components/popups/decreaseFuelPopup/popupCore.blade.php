<script>
    function showDecreaseFuelPopup(formItem = {}) {

        $('#mainPopup').dxPopup({
            visible: true,
            title: 'Расход топлива',
            contentTemplate: () => {return getDecreaseFuelPopupContentTemplate(formItem)},
        })       
    }

    const getDecreaseFuelPopupContentTemplate = (formItem) => {
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
                        value: fuelFlowTypesStore.__rawData.find(el=>el.slug==='outcome').id
                    }
                },
                {
                    dataField: 'fuel_tank_id',
                    editorType: 'dxSelectBox',
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
                    
                    dataField: 'our_technic_id',
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: fuelConsumersStore,
                        valueExpr: 'id',
                        displayExpr: 'name',
                        readOnly: editingRowId,
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
                    dataField: 'document_date',
                    editorType: "dxDateBox",
                    label: {
                        text: 'Дата операции'
                    },
                    validationRules: [{
                        type: 'required',
                        message: 'Укажите значение',
                    }],
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