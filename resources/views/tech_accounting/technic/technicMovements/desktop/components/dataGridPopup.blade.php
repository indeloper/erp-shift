<script>
    const dataGridPopup =
        {
            showTitle: true,
            title: "Карточка перемещения техники",
            hideOnOutsideClick: false,
            showCloseButton: true,
            maxWidth: '60vw',
            height: 'auto',
            onHiding() {
                resetVars();
                resetStores();
                dataGrid = $('#mainDataGrid').dxDataGrid('instance')
                setReadonlyFormElemsProperties(false, dataGrid)
            },
        }
</script>
