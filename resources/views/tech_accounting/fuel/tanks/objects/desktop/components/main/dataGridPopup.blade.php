<script>
    const dataGridPopup =
        {
            showTitle: true,
            title: "Информация о топливной емкости",
            hideOnOutsideClick: true,
            showCloseButton: true,
            maxWidth: '40vw',
            height: 'auto',
            onShowing() {
                setReadonlyFormElemsProperties(!userPermissions.update_fuel_tanks, 'mainDataGrid')
            },
            onHiding() {
                resetVars();
                resetStores();
            },
        }
</script>
