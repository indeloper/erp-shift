<script>
    function showDecreaseFuelPopup(formItem = {fuelConsumerType: 'our_technik_radio_elem'}) {
        $('#mainPopup').dxPopup({
            visible: true,
            title: 'Расход топлива',
            contentTemplate: () => {
                return getDecreaseFuelPopupContentTemplate(formItem)
            },
        })
    }

    const getDecreaseFuelPopupContentTemplate = (formItem) => {
        return $('<div id="mainForm">').dxForm({
            validationGroup: "documentValidationGroup",
            labelMode: 'outside',
            labelLocation: 'left',
            formData: formItem,
            onContentReady(e) {
                if(!editingRowId) {
                    e.component.itemOption('fuelConsumerGroup.third_party_consumer', 'visible', false)
                }
            },
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
                    dataField: 'fuel_tank_id',
                    editorType: 'dxSelectBox',
                    editorOptions: {
                        dataSource: getAvailableFuelTanksForFlowOperations(),
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
                    dataField: 'event_date',
                    editorType: "dxDateBox",
                    editorOptions: {
                        readOnly: editingRowId,
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
                    dataField: 'comment',
                    editorType: "dxTextBox",
                    editorOptions: {
                        readOnly: editingRowId,
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
                                disabled: editingRowId,
                                onValueChanged(e) {

                                    const mainForm = $('#mainForm').dxForm('instance')

                                    if (e.value === 'third_party_technik_radio_elem') {
                                        mainForm.itemOption('fuelConsumerGroup.our_technic_id', 'visible', false)
                                        mainForm.itemOption('fuelConsumerGroup.third_party_consumer', 'visible', true)
                                        delete mainForm.option('formData').our_technic_id
                                    } 
                                    
                                    if (e.value === 'our_technik_radio_elem') { 
                                        mainForm.itemOption('fuelConsumerGroup.our_technic_id', 'visible', true)
                                        mainForm.itemOption('fuelConsumerGroup.third_party_consumer', 'visible', false)
                                        delete mainForm.option('formData').third_party_consumer
                                    }
                                }
                            },
                        }, 
                        {
                            dataField: 'our_technic_id',
                            editorType: "dxSelectBox",
                            visible: formItem.our_technic_id || !editingRowId,
                            editorOptions: {
                                elementAttr: {id: "our_technic_id_dxSelectBox"},
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
                            dataField: 'third_party_consumer',
                            editorType: "dxAutocomplete",
                            visible: !formItem.our_technic_id && editingRowId,
                            editorOptions: {
                                dataSource: thirdPartyFuelConsumers,
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
                    ]
                }

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
            // onFieldDataChanged: (e) => {
            // console.log("field value changed");
            //     if (e.dataField === 'fuelConsumerType') {
            //         if (e.value === 'third_party_technik_radio_elem') {
            //             e.component.itemOption('fuelConsumerGroup.our_technic_id', 'visible', false)
            //             e.component.itemOption('fuelConsumerGroup.third_party_consumer', 'visible', true)
            //             delete e.component.option('formData').our_technic_id
            //         } 
                    
            //         if (e.value === 'our_technik_radio_elem') { 
            //             e.component.itemOption('fuelConsumerGroup.our_technic_id', 'visible', true)
            //             e.component.itemOption('fuelConsumerGroup.third_party_consumer', 'visible', false)
            //             delete e.component.option('formData').third_party_consumer
            //         }
            //     }
            //  },           
        })
    }
</script>
