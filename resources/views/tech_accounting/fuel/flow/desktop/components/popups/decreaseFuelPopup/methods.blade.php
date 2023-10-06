<script>
    function showDecreaseFuelPopup(key = null) {
        $('#mainPopup').dxPopup({
            visible: true,
            title: 'Расход топлива',
            contentTemplate: getDecreaseFuelPopupContentTemplate
        })

        if(key) {
            $('#mainForm').dxForm('instance').itemOption('fuel_tank_id', 'value', 7);
            
            // $('#mainPopup').dxPopup('instance').option(
            //     {
            //         fuel_tank_id: 3,
            //         our_technic_id: 1,
            //         volume: 43656
            //     }
            // )
        }
    }
</script>