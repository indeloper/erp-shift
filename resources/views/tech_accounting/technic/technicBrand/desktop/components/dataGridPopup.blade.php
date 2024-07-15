<script>
    const dataGridPopup =
        {
            showTitle: true,
            title: "Заголовок карточки",
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
