<script>
    const dataGridColumns_fuel_flow = [

        {
            caption: "Идентификатор",
            dataField: "id",
            dataType: "number",
            width: 75,
            visible: false
        },
        {
            caption: "Дата операции",
            dataField: "event_date",
            dataType: "date",
            width: 150,
            sortOrder: 'desc',
        },
        {
            caption: "Ответственный",
            dataField: "responsible_id",
            lookup: {
                dataSource: additionalResources.fuelResponsibles,
                valueExpr: "id",
                displayExpr: "user_full_name"
            },

        },

        {
            caption: "Топливная емкость",
            dataField: "fuel_tank_id",
            lookup: {
                dataSource: additionalResources.fuelTanks,
                valueExpr: "id",
                displayExpr: "tank_number"
            },
            visible: false
        },

        {
            caption: "Поставщик",
            dataField: "contractor_id",
            lookup: {
                dataSource: additionalResources.fuelFlowTypes,
                valueExpr: "id",
                displayExpr: "short_name"
            },
            visible: false
        },
        {
            caption: "Потребитель",
            dataField: "our_technic_id",
            lookup: {
                dataSource: additionalResources.fuelConsumers,
                valueExpr: "id",
                displayExpr: "name"
            },
            visible: false
        },

        {
            caption: "Тип операции",
            dataField: "fuel_tank_flow_type_id",
            lookup: {
                dataSource: additionalResources.fuelFlowTypes,
                valueExpr: "id",
                displayExpr: "name"
            },
            visible: false
        },

        {
            caption: "Объем (л)",
            dataField: "volume",
            editorType: 'dxNumberBox',
            editorOptions: {
                // min: 0.001
            },
            dataType: "number",
            customizeText: (data) => {
                return new Intl.NumberFormat('ru-RU').format(data.value * 1000 / 1000);
            }
        },

        {
            type: "buttons",
            buttons: [
                {
                    icon: 'fas fa-list-alt dx-link-icon',
                    visible(e) {
                        const dateDiff = getDatesDaysDiff(e.row.data.created_at, Date())
                        if (dateDiff > 1) {
                            return true
                        }
                        if (Boolean("{{App::environment('local')}}")) {
                            return false;
                        }
                        if (!Boolean(+e.row.data.author_id === +authUserId)) {
                            return true;
                        }
                        return false
                    },
                    
                    onClick(e) {

                        externalEditingRowId = e.row.data.id;

                        let dataGrid = {}

                        if (choosedFormTab === 'fuelIncomes')
                                dataGridItems = $('#mainDataGrid_fuel_flow_incomes')

                        if (choosedFormTab === 'fuelOutcomes')
                            dataGridItems = $('#mainDataGrid_fuel_flow_outcomes')

                        if (choosedFormTab === 'fuelAdjustments')
                            dataGridItems = $('#mainDataGrid_fuel_flow_adjusments')

                        let choosedItem = dataGridItems.dxDataGrid('instance').getDataSource().items().find(el => el.id === e.row.data.id)
                        let fuelFlowType = additionalResources.fuelFlowTypes.find(el => el.id === choosedItem.fuel_tank_flow_type_id).slug                 

                        if (fuelFlowType === 'outcome') {
                            if(choosedItem.our_technic_id) {
                                choosedItem.fuelConsumerType = 'our_technik_radio_elem'
                            } else {
                                choosedItem.fuelConsumerType = 'third_party_technik_radio_elem'
                            }
                            
                            showDecreaseFuelPopup(choosedItem)
                        }
                            
                        if (fuelFlowType === 'income')
                            showIncreaseFuelPopup(choosedItem)

                        if (fuelFlowType === 'adjustment')
                            showAdjustmentFuelPopup(choosedItem)
                    }
                    
                },
                // 'edit',
                {
                    name: 'delete',
                    visible(e) {
                        const dateDiff = getDatesDaysDiff(e.row.data.created_at, Date())
                        if (dateDiff > 1) {
                            return false
                        }
                        if (Boolean("{{App::environment('local')}}")) {
                            return true;
                        }
                        if (!Boolean(+e.row.data.author_id === +authUserId)) {
                            return false;
                        }
                        return true;
                    }, 
                    onClick(e) {
                        customConfirmDialog("Вы уверены, что хотите удалить запись?")
                            .show().then((dialogResult) => {
                                if (dialogResult) {
                                    externalEntitiesDataSource.store().remove(e.row.data.id)
                                }
                            })
                    }
                }
            ],

            headerCellTemplate: (container, options) => {
                $('<div>')
                    .appendTo(container)
                    .dxButton({
                        text: "Добавить",
                        icon: "fas fa-plus",

                        visible: userPermissions.create_fuel_tank_flows_for_reportable_tanks || userPermissions.create_fuel_tank_flows_for_any_tank,

                        onClick: (e) => {
                            if (choosedFormTab === 'fuelIncomes') {
                                showIncreaseFuelPopup();
                                $('#mainDataGrid_fuel_flow_incomes').dxDataGrid('instance').option("focusedRowKey", undefined);
                                $('#mainDataGrid_fuel_flow_incomes').dxDataGrid('instance').option("focusedRowIndex", undefined);
                            }

                            if (choosedFormTab === 'fuelOutcomes') {
                                showDecreaseFuelPopup();
                                $('#mainDataGrid_fuel_flow_outcomes').dxDataGrid('instance').option("focusedRowKey", undefined);
                                $('#mainDataGrid_fuel_flow_outcomes').dxDataGrid('instance').option("focusedRowIndex", undefined);
                            }

                            if (choosedFormTab === 'fuelAdjustments') {
                                showAdjustmentFuelPopup();
                                $('#mainDataGrid_fuel_flow_adjusments').dxDataGrid('instance').option("focusedRowKey", undefined);
                                $('#mainDataGrid_fuel_flow_adjusments').dxDataGrid('instance').option("focusedRowIndex", undefined);
                            }

                        }
                    })
            }

        }


    ];
</script>
