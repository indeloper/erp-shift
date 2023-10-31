<script>
    const dataGridEditForm01Group2Elems_incomes = {
        tabTemplate(data, index, element) {
            return '<div style="display: flex; align-items:center"><div class="fa fa-arrow-up" style="padding-top: 1px; color: #1f931f"></div><div style="margin-left:6px">Поступления</div></div>'
        },
        onClick(e) {
            choosedFormTab = 'fuelIncomes'
        },
        items: [
            {
                // itemType: "group",
                // caption: "Поступления топлива",
                // cssClass: "datagrid-container",
                // items: [{
                name: "mainDataGrid_fuel_flow_incomes",
                editorType: "dxDataGrid",
                editorOptions: {
                    height: 372,
                    dataSource: tankFuelIncomesStore,
                    ...dataGridSettings_fuel_flow,
                    columns: dataGridColumns_fuel_flow,
                    elementAttr: {
                        id: "mainDataGrid_fuel_flow_incomes"
                    }
                }
                // }]
            }
        ]
    }
</script>
