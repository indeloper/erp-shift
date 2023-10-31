<script>
    const dataGridColumns = [

        {
            caption: "Идентификатор",
            dataField: "id",
            width: 75,
            visible: false,
        },

        {
            caption: "Начало эксплуатации",
            dataField: "explotation_start",
            dataType: "date",
            visible: false,
        },
        {
            caption: "Компания",
            dataField: "company_id",
            lookup: {
                dataSource: companiesStore,
                valueExpr: "id",
                displayExpr: "name"
            },
            // groupIndex: 0,
        },

        {
            caption: "Номер емкости",
            dataField: "tank_number",
            width: 150,
            sortIndex: 0,
            sortOrder: "asc"
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
            caption: "Ответственный",
            dataField: "responsible_id",
            lookup: {
                dataSource: fuelTanksResponsiblesStore,
                valueExpr: "id",
                displayExpr: "user_full_name"
            },
        },
        {
            caption: "Текущий остаток (л)",
            dataField: "fuel_level",
            editorType: 'dxNumberBox',
            editorOptions: {
                min: 0.001
            },
            cellTemplate(container, options) {
                fontColor = 'black'
                if (options.text > 0)
                    fontColor = '#1f931f';
                if (options.text < 0)
                    fontColor = '#dd5e5e'

                $('<span>')
                    .css('color', fontColor)
                    .text(new Intl.NumberFormat('ru-RU').format(options.text * 1000 / 1000))
                    .appendTo(container)

            }

        },

        {
            type: "buttons",
            buttons: [
                {
                    hint: 'Переместить',
                    icon: 'fas fa-exchange-alt',
                    onClick(e) {
                        if (!e.row.data.awaiting_confirmation) {
                            showMovingFuelTankPopup(e.row.data)
                        } else {
                            showMovingConfirmationFuelTankPopup(e.row.data)
                        }
                    },
                },
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
