<script>
    const form1infoTab = {
        tabTemplate(data, index, element) {
            return '<div style="display: flex; align-items:center"><div class="fa fa-info-circle info-circle-icon-color" style="padding-top: 1px;"></div><div style="margin-left:6px">Инфо</div></div>'
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
                    value: fuelFlowTypesStore?.__rawData?.find(el => el.slug === 'income')?.id
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

        ]
    }
</script>