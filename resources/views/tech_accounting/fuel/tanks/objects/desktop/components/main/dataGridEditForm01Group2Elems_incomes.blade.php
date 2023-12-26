<script>
    const dataGridEditForm01Group2Elems_incomes = {
        tabTemplate(data, index, element) {
            return '<div class="tab-template-header-wrapper"><div class="fa fa-arrow-up tab-template-header-icon-elem text-color-green"></div><div>Приход</div></div>'
        },
        visible: userPermissions.fuel_tank_flows_access,
        onClick(e) {
            choosedFormTab = 'fuelIncomes'
        },
        items: [
            {  
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
            }
        ]
    }
</script>
