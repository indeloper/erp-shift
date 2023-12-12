<script>
    function showDecreaseFuelPopup(formItem = {fuelConsumerType: 'our_technik_radio_elem'}) {
        popupMobile.option({
            visible: true,
            title: 'Расход топлива',
            contentTemplate: () => {
                return getDecreaseFuelPopupContentTemplate(formItem)
            },
            onContentReady(e) {
                $('#popupSaveButton').dxButton({
                    template: '<div class="text-color-blue">Сохранить</div>'
                })
            }
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
                        value: fuelFlowTypesStore.__rawData.find(el => el.slug === 'outcome').id
                    }
                },
                {
                    visible: false,
                    dataField: 'fuel_tank_id',
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: entitiesDataSource,
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
                    dataField: 'event_date',
                    editorType: "dxDateBox",
                    editorOptions: {
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
                    dataField: 'volume',
                    editorType: "dxNumberBox",
                    editorOptions: {
                        min: 1,
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
                    caption: 'Потребитель топлива',
                    name: "fuelConsumerGroup",
                    items: [
                        {
                            dataField: 'fuelConsumerType',
                            editorType: "dxRadioGroup",
                            label: {
                                    visible: false
                            },                                
                            editorOptions: {
                                items: [
                                    {id: 'our_technik_radio_elem', text: 'Своя техника'},
                                    {id: 'third_party_technik_radio_elem', text: 'Сторонняя техника'}, 
                                ],
                                valueExpr: 'id',
                                displayExpr: 'text',
                                layout: 'horizontal',
                            }
                        }, 
                        {
                            dataField: 'our_technic_id',
                            editorType: "dxSelectBox",
                            editorOptions: {
                                elementAttr: {id: "our_technic_id_dxSelectBox"},
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
                            dataField: 'third_party_consumer',
                            editorType: "dxAutocomplete",
                            visible: false,
                            editorOptions: {
                                dataSource: thirdPartyFuelConsumers,
                            },
                            label: {
                                text: 'Потребитель'
                            },
                            validationRules: [{
                                type: 'required',
                                message: 'Укажите значение',
                            }],
                        },
                    ]
                }
                
            ],
            onFieldDataChanged: (e) => {

                if (e.dataField === 'fuelConsumerType') {
                    if (e.value === 'third_party_technik_radio_elem') {
                        e.component.itemOption('fuelConsumerGroup.our_technic_id', 'visible', false)
                        e.component.itemOption('fuelConsumerGroup.third_party_consumer', 'visible', true)
                        delete e.component.option('formData').our_technic_id
                    } 
                    
                    if (e.value === 'our_technik_radio_elem') { 
                        e.component.itemOption('fuelConsumerGroup.our_technic_id', 'visible', true)
                        e.component.itemOption('fuelConsumerGroup.third_party_consumer', 'visible', false)
                        delete e.component.option('formData').third_party_consumer
                    }
                }
            }
        })
    }
</script>
