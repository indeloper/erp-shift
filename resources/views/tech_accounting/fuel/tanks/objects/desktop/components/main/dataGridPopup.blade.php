<script>
    const dataGridPopup =
        {
            showTitle: true,
            title: "Информация о топливной емкости",
            hideOnOutsideClick: true,
            showCloseButton: true,
            maxWidth: '40vw',
            height: 'auto',
            onInitialized: (e) => {
                console.log("onInitialized", e.component.container)
            },
            onHiding() {
                resetVars();
                resetStores();
            },
        }
</script>
