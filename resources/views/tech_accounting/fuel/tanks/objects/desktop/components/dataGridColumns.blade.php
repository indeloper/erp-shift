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
            caption: "Номер емкоскти",
            dataField: "tank_number",
            width: 75,
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
            caption: "Текущий остаток",
            dataField: "fuel_level",
 
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
