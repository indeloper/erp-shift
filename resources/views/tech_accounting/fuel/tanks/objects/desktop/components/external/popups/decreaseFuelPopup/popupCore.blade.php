<script>
    function showDecreaseFuelPopup(formItem = {fuelConsumerType: 'our_technik_radio_elem'}) {

        externalPopup.option({
            visible: true,
            title: 'Расход топлива',
            contentTemplate: () => {
                return getDecreaseFuelPopupContentTemplate(formItem)
            },
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
                    dataField: 'event_date',
                    editorType: "dxDateBox",
                    editorOptions: {
                        readOnly: externalEditingRowId,
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

                
                {
                    dataField: 'volume',
                    editorType: "dxNumberBox",
                    editorOptions: {
                        min: 1,
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
                        readOnly: externalEditingRowId,
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
                                disabled: externalEditingRowId
                            }
                        }, 
                        {
                            dataField: 'our_technic_id',
                            editorType: "dxSelectBox",
                            visible: formItem.our_technic_id || !externalEditingRowId,
                            editorOptions: {
                                elementAttr: {id: "our_technic_id_dxSelectBox"},
                                dataSource: fuelConsumersStore,
                                valueExpr: 'id',
                                displayExpr: 'name',
                                readOnly: externalEditingRowId,
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
                            visible: !formItem.our_technic_id && externalEditingRowId,
                            editorOptions: {
                                elementAttr: {id: "third_party_consumer_dxAutocomplete"},
                                valueExpr: 'id',
                                displayExpr: 'name',
                                readOnly: externalEditingRowId,
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
                


                // {
                //     dataField: 'our_technic_id',
                //     editorType: "dxSelectBox",
                //     editorOptions: {
                //         dataSource: fuelConsumersStore,
                //         valueExpr: 'id',
                //         displayExpr: 'name',
                //         readOnly: externalEditingRowId,
                //     },
                //     label: {
                //         text: 'Потребитель'
                //     },
                //     validationRules: [{
                //         type: 'required',
                //         message: 'Укажите значение',
                //     }],
                // },

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
