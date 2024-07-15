<script>
    const form11infoTab = {
        tabTemplate(data, index, element) {
            return '<div style="display: flex; align-items:center"><div class="fa fa-info-circle info-circle-icon-color" style="padding-top: 1px;"></div><div style="margin-left:6px">Инфо</div></div>'
        },
        items: [
                    {
                        itemType: "group",
                        name: "baseInfoGroup",
                        colCount: 2,
                        items: [
                            {
                        visible: false,
                        dataField: 'fuel_tank_flow_type_id',
                        editorType: "dxSelectBox",
                        editorOptions: {
                            dataSource: additionalResources.fuelFlowTypes,
                            valueExpr: 'id',
                            displayExpr: 'name',
                        }
                    },
                    {
                        colSpan: 2,
                        dataField: 'object_id',
                        editorType: "dxSelectBox",
                        editorOptions: {
                            dataSource: additionalResources.projectObjects,
                            valueExpr: 'id',
                            displayExpr: 'short_name',
                            itemTemplate(e) {
                                return `<div class="dx-list-item-line-break">${e.short_name}</div>`
                            },
                        },
                        label: {
                            text: 'Объект'
                        },
                        validationRules: [{
                            type: 'required',
                            message: 'Укажите значение',
                        }],
                    },

                    {
                        colSpan: 1,
                        dataField: 'event_date',
                        editorType: "dxDateBox",
                        editorOptions: {
                            readOnly: Boolean(isFuelFlowDataFieldUpdateAvailable('event_date')),
                            max: Date(),
                            value: getEventDate(),
                            elementAttr: {
                                id: "eventDateSelectBox",
                            },
                        },
                        label: {
                            text: 'Дата'
                        },
                        validationRules: [{
                            type: 'required',
                            message: 'Укажите значение',
                        }],
                    },
                    {
                        colSpan: 1,
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
                        colSpan: 2,
                        dataField: 'comment',
                        editorType: "dxTextBox",
                        editorOptions: {
                            // readOnly: Boolean(isFuelFlowDataFieldUpdateAvailable('comment')),
                        },
                        label: {
                            text: 'Комментарий'
                        },
                    },

                ]
            },

            
            {
                itemType: "group",
                caption: 'Поставка топлива',
                name: "fuelIncomeGroup",
                colCount: 2,
                items: [
                    {
                        dataField: 'contractor_id',
                        editorType: "dxSelectBox",
                        editorOptions: {
                            dataSource: additionalResources.fuelContractors,
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
                        dataField: 'company_id',
                        editorType: "dxSelectBox",
                        editorOptions: {
                            dataSource: additionalResources.companies,
                            valueExpr: 'id',
                            displayExpr: 'name',
                            readOnly: Boolean(isFuelFlowDataFieldUpdateAvailable('company_id')),
                        },
                        label: {
                            text: 'Компания'
                        },
                        validationRules: [{
                            type: 'required',
                            message: 'Укажите значение',
                        }],
                    },
                    {
                        colSpan: 2,
                        dataField: 'document',
                        editorType: "dxTextBox",
                        editorOptions: {

                        },
                        label: {
                            text: 'Номер документа'
                        },
                    },
                ]
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
                        },
                    }, 
                    {
                        dataField: 'our_technic_id',
                        editorType: "dxSelectBox",

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

        ]
    }
</script>
