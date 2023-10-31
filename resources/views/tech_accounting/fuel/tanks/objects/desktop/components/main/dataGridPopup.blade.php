<script>
    const dataGridPopup =
        {
            showTitle: true,
            title: "Информация о топливной емкости",
            hideOnOutsideClick: true,
            showCloseButton: true,
            maxWidth: '40vw',
            height: 'auto',
            onHiding() {
                resetVars();
                resetStores();
            },
        }
</script>
