<script>
    const dataGridColumns = [

        {
            caption: 'Дата',
            dataField: "created_at",
            dataType: "date",
            width: 100
        },
        {
            caption: "Топливная емкость",
            dataField: "fuel_tank_id",
            // alignment: "left",
            lookup: {
                dataSource: additionalResources.fuelTanks,
                valueExpr: "id",
                displayExpr: "tank_number"
            },
            width: 100
        },
        {
            caption: "Предыдущий объект",
            dataField: "previous_object_id",
            lookup: {
                dataSource: additionalResources.projectObjects,
                valueExpr: "id",
                displayExpr: "short_name"
            },
        },
        {
            caption: "Объект",
            dataField: "object_id",
            lookup: {
                dataSource: additionalResources.projectObjects,
                valueExpr: "id",
                displayExpr: "short_name"
            },
        },

        {
            caption: "Остаток (л)",
            dataField: "fuel_level",
            editorType: 'dxNumberBox',
            editorOptions: {
                min: 0.001
            },
            customizeText: (data) => {
                return new Intl.NumberFormat('ru-RU').format(data.value * 1000 / 1000);
            },
            width: 100
        },
    ];
</script>
