<script>

    function showIncreaseFuelPopup(formItem = {}) {
        externalPopup.option({
            visible: true,
            title: 'Поступление топлива',
            contentTemplate: () => {
                fuelFlowFormData = formItem
                return getIncreaseFuelPopupContentTemplate(formItem)
            },
        })
    }

    const getIncreaseFuelPopupContentTemplate = (formItem) => {
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
                        value: fuelFlowTypesStore.__rawData.find(el => el.slug === 'income').id
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
                    dataField: 'contractor_id',
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: fuelContractorsStore,
                        valueExpr: 'id',
                        displayExpr: 'short_name',
                        readOnly: Boolean(isFuelFlowDataFieldUpdateAvailable('contractor_id')),
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
                        readOnly: Boolean(isFuelFlowDataFieldUpdateAvailable('volume')),
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
                        readOnly: Boolean(isFuelFlowDataFieldUpdateAvailable('event_date')),
                        value: getEventDate(),
                        max: Date(),
                        min: getThreeDaysEarlierDate()
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
                    editorOptions: {
                        
                    },
                    label: {
                        text: 'Номер документа'
                    },
                },

                {
                    dataField: 'comment',
                    editorType: "dxTextBox",
                    editorOptions: {
                        
                    },
                    label: {
                        text: 'Комментарий'
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
                                renderFileDisplayer(itemElement, Boolean(isFuelFlowDataFieldUpdateAvailable('attachment')))
                            }
                        },
                    ]
                }


            ]
        })
    }
</script>
