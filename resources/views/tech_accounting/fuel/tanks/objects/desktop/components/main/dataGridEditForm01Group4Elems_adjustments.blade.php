<script>
    const dataGridEditForm01Group4Elems_adjustments = {
        tabTemplate(data, index, element) {
            return '<div style="display: flex; align-items:center"><div class="fas fa-exchange-alt" style="padding-top: 1px; color: #3a6fcb"></div><div style="margin-left:6px">Корректировки</div></div>'
        },
        onClick(){
            choosedFormTab = 'fuelAdjustments'
        },
        items: [
            {
                // itemType: "group",
                // caption: "Корректировки",
                // cssClass: "datagrid-container",
                // items: [{
                    name: "mainDataGrid_fuel_flow_adjusments",
                    editorType: "dxDataGrid",
                    editorOptions: {
                        dataSource: tankFuelAdjustmentsStore,
                        ...dataGridSettings_fuel_flow,
                        columns: dataGridColumns_fuel_flow,
                        elementAttr: {
                            id: "mainDataGrid_fuel_flow_adjusments"
                        }
                    }
                // }]
            }
        ]
    }
</script>