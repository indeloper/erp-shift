<script>
    const buttonsColumn = {
        visible: Boolean(userPermissions.technics_movement_crud || userPermissions.technics_processing_movement_standart_sized_equipment || userPermissions.technics_processing_movement_oversized_equipment),
        type: "buttons",
        buttons: [
            'edit',
            {
                name: 'delete',
                visible: Boolean(userPermissions.technics_movement_crud),
            }
            
        ],

        headerCellTemplate: (container, options) => {
            $('<div>')
                .appendTo(container)
                .dxButton({
                    text: "Добавить",
                    icon: "fas fa-plus",
                    visible: Boolean(userPermissions.technics_movement_crud),
                    onClick: (e) => {
                        options.component.addRow();
                        $('#mainDataGrid').dxDataGrid('instance').option("focusedRowKey", undefined);
                        $('#mainDataGrid').dxDataGrid('instance').option("focusedRowIndex", undefined);
                    }
                })
        }
    }
</script>