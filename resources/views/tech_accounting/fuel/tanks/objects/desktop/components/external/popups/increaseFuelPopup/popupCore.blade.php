<script>

    function showIncreaseFuelPopup(formItem = {}) {
        externalPopup.option({
            visible: true,
            title: 'Приход топлива',
            contentTemplate: () => {
                fuelFlowFormData = formItem
                return getIncreaseFuelPopupContentTemplate(formItem)
            },
        })
    }

    const getIncreaseFuelPopupContentTemplate = (formItem) => {
        return $('<div id="externalForm">').dxForm({
            validationGroup: "documentExternalValidationGroup",
            labelMode: 'outside',
            labelLocation: 'left',
            formData: formItem,

            items: [
                {
                    itemType: 'tabbed',
                    colSpan: 6,
                    tabPanelOptions: {
                        deferRendering: false,
                        height: "60vh"
                    },
                    tabs: [
                        form1infoTab,
                        form2filesTab
                    ]
                }
            ]
        })
    }
</script>
