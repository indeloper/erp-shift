<script>
    const form22filesTab = {
        tabTemplate (data, index, element){
            return '<div style="display: flex; align-items:center"><span class="fa fa-file" style="padding-top: 1px;"></span><span style="margin-left:6px">Файлы</span></div>'
        },
        items: [
            {
                item: 'simple',
                template: (data, itemElement) => {
                    renderFileUploader(itemElement)
                }
            },

            {
                item: 'simple',
                template: (data, itemElement) => {
                    renderFileDisplayer(itemElement, Boolean(isFuelFlowDataFieldUpdateAvailable('attachment')))
                }
            },
        ]
    }
</script>