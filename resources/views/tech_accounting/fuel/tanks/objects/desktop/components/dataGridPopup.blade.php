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
                resetVars();
                resetStores();
            },
        }
</script>
