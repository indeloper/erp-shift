<script>
    const renderPopupContent = () => {

        $('#mainForm').dxForm({
            elementAttr: {
                id: "mainForm"
            },
            validationGroup: "documentValidationGroup",
            colCount: 1,

            onContentReady(e) {
                e.component.option('formData', choosedItemData)
            },
            items: [
                {
                    itemType: 'tabbed',
                    tabPanelOptions: {
                        deferRendering: false,
                    },
                    tabs: [
                        infoTabbedGroup,
                        filesTabbedGroup
                    ],
                }
            ]
        })
    }
    
</script>