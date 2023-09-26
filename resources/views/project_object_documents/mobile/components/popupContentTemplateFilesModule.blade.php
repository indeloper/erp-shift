<script>
    const renderFilesTemplate = function() {
        // $('.dx-toolbar-center .dx-item-content').html('<div>Файлы</div>')
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