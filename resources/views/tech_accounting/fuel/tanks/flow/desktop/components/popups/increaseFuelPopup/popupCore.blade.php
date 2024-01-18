<script>
    function showIncreaseFuelPopup(formItem = {}) {
        $('#mainPopup').dxPopup({
            visible: true,
            title: 'Приход топлива',
            contentTemplate: () => {
                formItem.fuel_tank_flow_type_id = additionalResources.fuelFlowTypes.find(el => el.slug === 'income').id
                fuelFlowFormData = formItem
                return getIncreaseFuelPopupContentTemplate(formItem)
            },
        })
    }
    
    const getIncreaseFuelPopupContentTemplate = (formItem) => {

        return $('<div id="mainForm">').dxForm({
            validationGroup: "documentValidationGroup",
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
