<script>
    function showIncreaseFuelPopup(formItem = {}) {
        $('#mainPopup').dxPopup({
            visible: true,
            title: 'Поступление топлива',
            contentTemplate: () => {return getIncreaseFuelPopupContentTemplate(formItem)}
        })
    }

    const getIncreaseFuelPopupContentTemplate = (formItem) => {
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
                        value: fuelFlowTypesStore.__rawData.find(el=>el.slug==='income').id
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
                    dataField: 'contractor_id',
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: fuelContractorsStore,
                        valueExpr: 'id',
                        displayExpr: 'short_name',
                    }
                },
                {
                    dataField: 'volume',
                    editorType: "dxNumberBox",
                    editorOptions: {
                        min: 0.001
                    },

                },

                {
                    item: 'simple',
                    template: (data, itemElement) => {
                        renderFileUploader(itemElement)
                    }
                },

                {
                    item: 'simple',
                    template: (data, itemElement) => {
                        renderFileDisplayer(itemElement)
                    }
                },
                                
            ]
        })
    }
</script>