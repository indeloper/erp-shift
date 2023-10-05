<script>
    const dataGridColumns = [

        {
            caption: 'Дата',
            dataField: "created_at",
            dataType: "date",
        },
        {
            caption: "Топливная емкость",
            dataField: "fuel_tank_id",
            lookup: {
                dataSource: fuelTanksStore,
                valueExpr: "id",
                displayExpr: "tank_number"
            },
        },
        {
            caption: "Объект",
            dataField: "object_id",
            lookup: {
                dataSource: projectObjectsStore,
                valueExpr: "id",
                displayExpr: "short_name"
            },
        },
                
        {
            caption: "Остаток (л)",
            dataField: "fuel_level",
            customizeText: (data) => {
                return new Intl.NumberFormat('ru-RU').format(data.value * 1000 / 1000);
            }
        },
    ];
</script>
