<script>
    function showAdjustmentFuelPopup(formItem) {
        $('#mainPopup').dxPopup({
            visible: true,
            title: 'Корректировка остатков топлива',
            contentTemplate: () => {return getAdjustmentFuelPopupContentTemplate(formItem)}
        })
    }

    const getAdjustmentFuelPopupContentTemplate = (formItem) => {
        return $('<div id="mainForm">').dxForm({
            labelMode: 'outside',
            labelLocation: 'left',
            formData: formItem,
            items: [
                {
                    visible: false,
                    dataField: 'fuel_tank_flow_type_id',
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: fuelFlowTypesStore,
                        valueExpr: 'id',
                        displayExpr: 'name',
                        value: fuelFlowTypesStore.__rawData.find(el=>el.slug==='adjustment').id
                    }
                },
                {
                    dataField: 'fuel_tank_id',
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: fuelTanksStore,
                        valueExpr: 'id',
                        displayExpr: 'tank_number',
                    }
                },
                
                {
                    dataField: 'volume',
                    editorType: "dxNumberBox",
                    editorOptions: {
                        // min: 0.001
                    },

                },
                {
                    item: 'simple',
                    template: (data, itemElement) => {
                        renderFileUploader(itemElement)
                    }
                },
             
            ]
        })
    }
</script>