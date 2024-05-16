<script>
    function showSimultaneousIncomeOutcomeFuelPopup(formItem = {fuelConsumerType: 'our_technik_radio_elem'}) {
        $('#mainPopup').dxPopup({
            visible: true,
            title: 'Прямая заправка топлива',
            contentTemplate: () => {
                formItem.fuel_tank_flow_type_id = additionalResources.fuelFlowTypes.find(el => el.slug === 'simultaneous_income_outcome').id
                fuelFlowFormData = formItem
                return getSimultaneousIncomeOutcomeFuelPopupContentTemplate(formItem)
            },
            minWidth: '800px'
        })
    }
    
    const getSimultaneousIncomeOutcomeFuelPopupContentTemplate = (formItem) => {

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
                        form11infoTab,
                        form22filesTab
                    ]
                }
            ]
                
        })
    }
</script>
