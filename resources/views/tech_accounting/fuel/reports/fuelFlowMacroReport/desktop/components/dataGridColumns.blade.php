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
            caption: "Тип операции",
            dataField: "type",
            editorType: "dxSelectBox",
            editorOptions: {
                dataSource: ['Поступление', 'Расход']
            }
        },
        {
            caption: "Объем, л",
            dataField: "volume",
        },

        // {
        //     caption: "Потребитель",
        //     dataField: "our_technic_id",
        //     lookup: {
        //         dataSource: fuelConsumersStore,
        //         valueExpr: "id",
        //         displayExpr: "name"
        //     },
        // },

        // {
        //     caption: "Поставщик",
        //     dataField: "contractor_id",
        //     lookup: {
        //         dataSource: fuelContractorsStore,
        //         valueExpr: "id",
        //         displayExpr: "short_name"
        //     },
        // },

    ];
</script>
