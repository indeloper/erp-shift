<script>
    const renderFilesTemplate = function () {
        const container = $('#popupContainer')
        container.html('')
        const containerScrollableWrapper = $('<div id="containerScrollableWrapper">').appendTo(container)
        popupLoadPanel.option('visible', true)
        projectObjectDocumentInfoByID.reload().done(() => {
            renderFilesUploader(containerScrollableWrapper)
            renderFilesList(containerScrollableWrapper)
        })
    }
</script>
