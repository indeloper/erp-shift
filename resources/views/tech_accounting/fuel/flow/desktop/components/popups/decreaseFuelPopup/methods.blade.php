<script>
    function showDecreaseFuelPopup() {
        $('#mainPopup').dxPopup({
            visible: true,
            title: 'Расход топлива',
            contentTemplate: getDecreaseFuelPopupContentTemplate
        })
    }
</script>