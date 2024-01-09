<script>
    function showDecreaseFuelPopup(formItem = {fuelConsumerType: 'our_technik_radio_elem'}) {
        $('#mainPopup').dxPopup({
            visible: true,
            title: 'Расход топлива',
            contentTemplate: () => {
                fuelFlowFormData = formItem
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
                if (formItem.third_party_mark) {
                    e.component.getEditor("our_technic_id").option('dataSource', fuelConsumers.__rawData.filter(el=>el.third_party_mark===1))
                }
                else {
                    e.component.getEditor("our_technic_id").option('dataSource', fuelConsumers.__rawData.filter(el=>el.third_party_mark===0))
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
                        readOnly: Boolean(isFuelFlowDataFieldUpdateAvailable('fuel_tank_id')),
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
                        readOnly: Boolean(isFuelFlowDataFieldUpdateAvailable('event_date')),
                        value: getEventDate(),
                        max: Date(),
                        min: getDaysEarlierDate(35)
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
                    dataField: 'comment',
                    editorType: "dxTextBox",
                    editorOptions: {
                        // readOnly: Boolean(isFuelFlowDataFieldUpdateAvailable('comment')),
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
                                disabled: Boolean(isFuelFlowDataFieldUpdateAvailable('fuelConsumerType')),
                                
                                onValueChanged(e) {
                                    let technicSelectBox = $('#our_technic_id_dxSelectBox').dxSelectBox('instance')

                                    if (e.value === 'third_party_technik_radio_elem') {
                                        technicSelectBox.option('dataSource', fuelConsumers.__rawData.filter(el=>el.third_party_mark===1))
                                    }
                                    if (e.value === 'our_technik_radio_elem') {
                                        technicSelectBox.option('dataSource', fuelConsumers.__rawData.filter(el=>el.third_party_mark===0))
                                    }
                                }
                            },
                        }, 
                        {
                            dataField: 'our_technic_id',
                            editorType: "dxSelectBox",

                            editorOptions: {
                                elementAttr: {id: "our_technic_id_dxSelectBox"},
                                dataSource: fuelConsumers,
                                valueExpr: 'id',
                                displayExpr: 'name',
                                readOnly: Boolean(isFuelFlowDataFieldUpdateAvailable('our_technic_id')),
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
