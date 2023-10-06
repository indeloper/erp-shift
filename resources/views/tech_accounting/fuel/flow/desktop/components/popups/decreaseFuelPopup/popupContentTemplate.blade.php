<script>
    const getDecreaseFuelPopupContentTemplate = () => {
        return $('<div id="mainForm">').dxForm({
            labelMode: 'outside',
            labelLocation: 'left',
            items: [
                {
                    visible: false,
                    dataField: 'fuel_tank_flow_type_id',
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: fuelFlowTypesStore,
                        valueExpr: 'id',
                        displayExpr: 'name',
                        value: fuelFlowTypesStore.__rawData.find(el=>el.slug==='outcome').id
                    }
                },
                {
                    dataField: 'fuel_tank_id',
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: fuelTanksStore,
                        valueExpr: 'id',
                        displayExpr: 'tank_number',
                    }
                },
                {
                    
                    dataField: 'our_technic_id',
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: fuelConsumersStore,
                        valueExpr: 'id',
                        displayExpr: 'name',
                    }
                },
                {
                    dataField: 'volume',
                    editorType: "dxNumberBox",
                    editorOptions: {
                        min: 0.001
                    },
                },
             
            ]
        })
    }
</script>