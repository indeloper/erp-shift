<script>
    const dataGridColumns = [

        {
            caption: "Идентификатор",
            dataField: "id",
            dataType: "number",
            width: 75,
            visible: false
        },
        {
            caption: "Дата",
            dataField: "created_at",
            dataType: "date",
            width: 150,
        },
        {
            caption: "Ответственный",
            dataField: "author_id",
            lookup: {
                dataSource: fuelResponsiblesStore,
                valueExpr: "id",
                displayExpr: "user_full_name"
            },
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
            caption: "Поставщик",
            dataField: "contractor_id",
            lookup: {
                dataSource: fuelContractorsStore,
                valueExpr: "id",
                displayExpr: "short_name"
            },
            visible: false
        },
        {
            caption: "Потребитель",
            dataField: "our_technic_id",
            lookup: {
                dataSource: fuelConsumersStore,
                valueExpr: "id",
                displayExpr: "name"
            },
            visible: false
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
            caption: "Объем (л)",
            dataField: "volume",
            dataType: "number",
            customizeText: (data) => {
                return new Intl.NumberFormat('ru-RU').format(data.value * 1000 / 1000);
            }
        },
        
        {
            type: "buttons",
            buttons: [
                'edit',
                'delete'
            ],

            headerCellTemplate: (container, options) => {
                $('<div>')
                    .appendTo(container)
                    .dxButton({
                        text: "Добавить",
                        icon: "fas fa-plus",
                        onClick: (e) => {
                            options.component.addRow();
                            $('#mainDataGrid').dxDataGrid('instance').option("focusedRowKey", undefined);
                            $('#mainDataGrid').dxDataGrid('instance').option("focusedRowIndex", undefined);
                        }
                    })
            }
        }


    ];
</script>
