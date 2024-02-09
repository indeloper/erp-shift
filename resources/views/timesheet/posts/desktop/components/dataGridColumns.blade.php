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
            //visible: userPermissions.technics_create_update_delete,
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
