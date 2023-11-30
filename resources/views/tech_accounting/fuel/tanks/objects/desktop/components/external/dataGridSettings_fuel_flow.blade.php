<script>
    const dataGridSettings_fuel_flow = {
        // height(){
        //     return 0.85*$('.dx-overlay-wrapper').find('.dx-popup-content').height()
        // },
        focusedRowEnabled: true,
        hoverStateEnabled: true,
        columnAutoWidth: false,
        showBorders: true,
        showColumnLines: true,
        columnMinWidth: 50,
        columnResizingMode: 'nextColumn',
        syncLookupFilterValues: false,
        columnHidingEnabled: false,
        showRowLines: true,
        remoteOperations: true,
        scrolling: {
            mode: 'infinite',
            rowRenderingMode: 'virtual',
        },
        filterRow: {
            visible: true,
            applyFilter: "auto"
        },
        headerFilter: {
            visible: false,
        },
        filterPanel: {
            visible: false,
            customizeText: (e) => {
                filterText = e.text;
            }
        },
        paging: {
            enabled: true,
            pageSize: 100,
        },
        editing: {
            // mode: "popup",
            // popup: dataGridPopup,
            // form: dataGridEditForm,
            allowUpdating: false,
            allowAdding: false,
            allowDeleting: false,
            selectTextOnEditStart: false,
            useIcons: true,
        },

        onRowDblClick: function (e) {
            if (e.rowType === "data" && DevExpress.devices.current().deviceType === 'desktop') {
                externalEditingRowId = e.key;

                let dataGrid = {}

                if (choosedFormTab === 'fuelIncomes')
                    dataGridItems = $('#mainDataGrid_fuel_flow_incomes')

                if (choosedFormTab === 'fuelOutcomes')
                    dataGridItems = $('#mainDataGrid_fuel_flow_outcomes')

                if (choosedFormTab === 'fuelAdjustments')
                    dataGridItems = $('#mainDataGrid_fuel_flow_adjusments')

                let choosedItem = dataGridItems.dxDataGrid('instance').getDataSource().items().find(el => el.id === e.key)

                // let choosedItem = $('#externalDataGrid').dxDataGrid('instance').getDataSource().items().find(el=>el.id === e.key)
                let fuelFlowType = fuelFlowTypesStore.__rawData.find(el => el.id === choosedItem.fuel_tank_flow_type_id).slug

                if (fuelFlowType === 'outcome') {
                    if(choosedItem.our_technic_id) {
                        choosedItem.fuelConsumerType = 'our_technik_radio_elem'
                    } else {
                        choosedItem.fuelConsumerType = 'third_party_technik_radio_elem'
                    }
                    
                    showDecreaseFuelPopup(choosedItem)
                }
                    
                if (fuelFlowType === 'income')
                    showIncreaseFuelPopup(choosedItem)

                if (fuelFlowType === 'adjustment')
                    showAdjustmentFuelPopup(choosedItem)
            }
        },

        onEditorPreparing: (e) => {
            if (e.parentType === `filterRow` && e.lookup)
                createFilterRowTagBoxFilterControlForLookupColumns(e)
        },
        onSaved() {
            resetExternalVars();
            resetExternalStores();
        },

        toolbar: {
            visible: false,
            items: [{}]
        },
    }
</script>
