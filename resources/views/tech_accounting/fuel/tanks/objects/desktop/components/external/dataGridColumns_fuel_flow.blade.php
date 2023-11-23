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
                dataSource: fuelResponsiblesStore,
                valueExpr: "id",
                displayExpr: "user_full_name"
            },

        },

        {
            caption: "Топливная емкость",
            dataField: "fuel_tank_id",
            lookup: {
                dataSource: fuelTanksStore,
                valueExpr: "id",
                displayExpr: "tank_number"
            },
            visible: false
        },

        {
            caption: "Поставщик",
            dataField: "contractor_id",
            lookup: {
                dataSource: fuelContractorsStore,
                valueExpr: "id",
                displayExpr: "short_name"
            },
            visible: false
        },
        {
            caption: "Потребитель",
            dataField: "our_technic_id",
            lookup: {
                dataSource: fuelConsumersStore,
                valueExpr: "id",
                displayExpr: "name"
            },
            visible: false
        },

        {
            caption: "Тип операции",
            dataField: "fuel_tank_flow_type_id",
            lookup: {
                dataSource: fuelFlowTypesStore,
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
                'edit',
                'delete'
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
