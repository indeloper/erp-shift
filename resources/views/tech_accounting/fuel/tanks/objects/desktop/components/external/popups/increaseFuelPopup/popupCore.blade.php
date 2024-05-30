<script>

    function showIncreaseFuelPopup(formItem = {}) {
        externalPopup.option({
            visible: true,
            title: 'Приход топлива',
            contentTemplate: () => {
                formItem.fuel_tank_flow_type_id = additionalResources.fuelFlowTypes.find(el => el.slug === 'income').id
                formItem.fuel_tank_id = editingRowId
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

            onContentReady(e) {
                if(!externalEditingRowId) {
                    const componentFormData = e.component.option('formData')
                    componentFormData.event_date = new Date()
                    e.component.option('formData', componentFormData)
                }
            },

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
