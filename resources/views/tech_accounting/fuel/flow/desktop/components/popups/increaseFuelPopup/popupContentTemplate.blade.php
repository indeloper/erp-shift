<script>
    const getIncreaseFuelPopupContentTemplate = () => {
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
                    dataField: 'Поставщик топлива',
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: fuelContractorsStore,
                        valueExpr: 'id',
                        displayExpr: 'short_name',
                    }
                },
                {
                    dataField: 'Объем (л)',
                    editorType: "dxNumberBox",
                    editorOptions: {
                        min: 0.001
                    },

                },
             
            ]
        })
    }
</script>