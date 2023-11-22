<script>
    const popupContentTemplate = function () {
        const popupContentWrapper =
            $('<div id="popupContentWrapper">')
                .css({
                    'width': '100%',
                    'height': '100%',
                    'positon': 'relative'
                })

        $('<div id="popupContainer">')
            .appendTo(popupContentWrapper)

        return popupContentWrapper;
    }
</script>
