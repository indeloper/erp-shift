<script>
    const getAdjustmentFuelPopupContentTemplate = () => {
        return $('<div>').dxForm({
            items: [
                {
                    dataField: 'Ёмкость',
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: fuelTanksStore,
                        valueExpr: 'id',
                        displayExpr: 'tank_number',
                    }
                },
                
                {
                    dataField: 'Объем (л)',
                    editorType: "dxNumberBox",
                    editorOptions: {
                        // min: 0.001
                    },

                },
             
            ]
        })
    }
</script>