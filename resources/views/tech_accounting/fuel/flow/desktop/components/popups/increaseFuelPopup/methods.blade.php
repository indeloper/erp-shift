<script>
    function showIncreaseFuelPopup() {
        $('#mainPopup').dxPopup({
            visible: true,
            title: 'Поступление топлива',
            contentTemplate: getIncreaseFuelPopupContentTemplate
        })
    }
</script>