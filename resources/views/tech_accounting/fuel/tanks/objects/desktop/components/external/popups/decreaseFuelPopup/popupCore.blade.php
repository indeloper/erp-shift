<script>
    function showDecreaseFuelPopup(formItem = {fuelConsumerType: 'our_technik_radio_elem'}) {

        externalPopup.option({
            visible: true,
            title: 'Расход топлива',
            contentTemplate: () => {
                fuelFlowFormData = formItem
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
            onContentReady(e) {
                
                if (formItem.third_party_mark) {
                    e.component.getEditor("our_technic_id").option('dataSource', additionalResources.fuelConsumers.filter(el=>el.third_party_mark===1))
                }
                else {
                    e.component.getEditor("our_technic_id").option('dataSource', additionalResources.fuelConsumers.filter(el=>el.third_party_mark===0))
                }
            },
            items: [
                {
                    visible: false,
                    dataField: 'fuel_tank_flow_type_id',
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: additionalResources.fuelFlowTypes,
                        valueExpr: 'id',
                        displayExpr: 'name',
                        value: additionalResources.fuelFlowTypes.find(el => el.slug === 'outcome').id
                    }
                },
                {
                    visible: false,
                    dataField: 'fuel_tank_id',
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: additionalResources.fuelTanks,
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
                                        technicSelectBox.option('dataSource', additionalResources.fuelConsumers.filter(el=>el.third_party_mark===1))
                                    }
                                    if (e.value === 'our_technik_radio_elem') {
                                        technicSelectBox.option('dataSource', additionalResources.fuelConsumers.filter(el=>el.third_party_mark===0))
                                    }
                                }
                            }
                        }, 
                        {
                            dataField: 'our_technic_id',
                            editorType: "dxSelectBox",
                            visible: formItem.our_technic_id || !externalEditingRowId,
                            editorOptions: {
                                elementAttr: {id: "our_technic_id_dxSelectBox"},
                                dataSource: additionalResources.fuelConsumers,
                                valueExpr: 'id',
                                displayExpr: 'name',
                                itemTemplate(e) {
                                    return `<div class="dx-list-item-line-break">${e.name}</div>`
                                },
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
            ],
        })
    }
</script>
