<script>
    const dataGridEditForm01Group3Elems_outcomes = {
        tabTemplate(data, index, element) {
            return '<div class="tab-template-header-wrapper"><div class="fa fa-arrow-down tab-template-header-icon-elem text-color-red"></div><div>Списания</div></div>'
        },
        visible: userPermissions.fuel_tank_flows_access,
        onClick() {
            choosedFormTab = 'fuelOutcomes'
        },
        items: [
            {
                // itemType: "group",
                // caption: "Расход топлива",
                // cssClass: "datagrid-container",
                // items: [{
                name: "mainDataGrid_fuel_flow_outcomes",
                editorType: "dxDataGrid",
                editorOptions: {
                    height: 372,
                    dataSource: tankFuelOutcomesStore,
                    ...dataGridSettings_fuel_flow,
                    columns: dataGridColumns_fuel_flow,
                    elementAttr: {
                        id: "mainDataGrid_fuel_flow_outcomes"
                    }
                }
                // }]
            }
        ]
    }
</script>
