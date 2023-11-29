<script>

    const popupEditingModeContentTemplate = function () {
        const popupContentWrapper = $('<div id="popupContentWrapper">')
            .addClass('popup-content-wrapper-mobile')
            
        $('<div id="popupContainer">')
            .appendTo(popupContentWrapper)

        return popupContentWrapper;
    }

</script>
