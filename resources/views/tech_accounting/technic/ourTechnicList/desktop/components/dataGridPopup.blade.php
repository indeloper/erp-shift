<script>
    const dataGridPopup =
        {
            showTitle: true,
            title: "Карточка единицы техники",
            hideOnOutsideClick: true,
            showCloseButton: true,
            maxWidth: '60vw',
            height: 'auto',
            onHiding() {
                resetVars();
                resetStores();
            },
        }
</script>
