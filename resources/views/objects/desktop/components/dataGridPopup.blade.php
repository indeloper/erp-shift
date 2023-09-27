<script>
    const dataGridPopup =
        {
            showTitle: true,
            title: "Информация об объекте",
            hideOnOutsideClick: true,
            showCloseButton: true,
            maxWidth: '60vw',
            height: 'auto',
            onHiding() {
                // $('#dataGridContainer').dxDataGrid('instance').option("focusedRowKey", undefined);
                // $('#dataGridContainer').dxDataGrid('instance').option("focusedRowIndex", undefined);
                resetVars();
                resetStores();
            },
            onShowing() {
                checkSaveButtonAvailable()
               
            }
        }
</script>
