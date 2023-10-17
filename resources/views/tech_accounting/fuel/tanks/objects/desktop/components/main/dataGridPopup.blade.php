<script>
    const dataGridPopup =
        {
            showTitle: true,
            title: "Информация о топливной емкости",
            hideOnOutsideClick: true,
            showCloseButton: true,
            maxWidth: '40vw',
            height: '80vh',
            onHiding() {
                resetVars();
                resetStores();
            },
        }
</script>
