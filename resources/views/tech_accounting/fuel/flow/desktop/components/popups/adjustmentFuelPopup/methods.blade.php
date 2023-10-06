<script>
    function showAdjustmentFuelPopup() {
        $('#mainPopup').dxPopup({
            visible: true,
            title: 'Корректировка остатков топлива',
            contentTemplate: getAdjustmentFuelPopupContentTemplate
        })
    }
</script>