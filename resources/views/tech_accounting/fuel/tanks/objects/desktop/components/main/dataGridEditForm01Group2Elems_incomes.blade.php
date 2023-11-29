<script>
    const dataGridEditForm01Group2Elems_incomes = {
        tabTemplate(data, index, element) {
            return '<div class="tab-template-header-wrapper"><div class="fa fa-arrow-up tab-template-header-icon-elem text-color-green"></div><div>Поступления</div></div>'
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
