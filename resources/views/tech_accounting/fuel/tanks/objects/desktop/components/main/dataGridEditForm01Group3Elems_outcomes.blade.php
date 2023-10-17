<script>
    const dataGridEditForm01Group3Elems_outcomes = {
        tabTemplate(data, index, element) {
            return '<div style="display: flex; align-items:center"><div class="fa fa-arrow-down" style="padding-top: 1px; color: #dd5e5e"></div><div style="margin-left:6px">Списания</div></div>'
        },
        onClick(){
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