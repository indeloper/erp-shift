<script>

    const postTariffEditForm = {
        colCount: 1,
        items: [
            {
                itemType: 'group',
                colCount: 2,
                items: [
                    {
                        dataField: "id",
                        colSpan: 2,
                        label: {
                            text: "Действие тарифа"
                        },
                        editorType: "dxDateRangeBox",
                        validationRules: [{
                            type: 'required',
                            message: 'Укажите значение',
                        }],
                    },
                    {
                        dataField: "tariff_date_range",
                        colSpan: 2,
                        label: {
                            text: "Действие тарифа"
                        },
                        editorType: "dxDateRangeBox",
                        validationRules: [{
                            type: 'required',
                            message: 'Укажите значение',
                        }],
                    }
                ]
            },
        ],
    }
</script>
