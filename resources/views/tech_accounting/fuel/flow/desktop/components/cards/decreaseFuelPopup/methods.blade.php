<script>
    function showDecreaseFuelPopup() {
        $('#mainPopup').dxPopup('instance').option({
            visible: true,
            title: 'Расход топлива',
            contentTemplate: decreaseFuelPopupContentTemplate()
        })
    }
</script>