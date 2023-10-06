<script>
    const getDecreaseFuelPopupContentTemplate = () => {
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
                    dataField: 'Потребитель топлива',
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: fuelConsumersStore,
                        valueExpr: 'id',
                        displayExpr: 'name',
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