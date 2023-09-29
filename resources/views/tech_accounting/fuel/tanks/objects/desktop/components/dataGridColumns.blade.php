<script>
    const dataGridColumns = [

        {
            caption: "Идентификатор",
            dataField: "id",
            width: 75,
        },
        
        {
            caption: "Наименование",
            dataField: "name",
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
                            $('#dataGrid').dxDataGrid('instance').option("focusedRowKey", undefined);
                            $('#dataGrid').dxDataGrid('instance').option("focusedRowIndex", undefined);
                        }
                    })
            }
        }


    ];
</script>
