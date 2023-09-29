<script>
    const dataGridPopup =
        {
            showTitle: true,
            title: "Движение топлива",
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
