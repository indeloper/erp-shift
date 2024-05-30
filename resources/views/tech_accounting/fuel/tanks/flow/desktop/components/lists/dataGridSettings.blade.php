<script>
    const dataGridSettings = {
        height: "calc(100vh - 200px)",
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
        grouping: {
            autoExpandAll: true,
            allowCollapsing: true,
            expandMode: 'rowClick',
        },
        paging: {
            enabled: true,
            pageSize: 100,
        },
        editing: {
            allowUpdating: false,
            allowAdding: false,
            allowDeleting: true,
            selectTextOnEditStart: false,
            useIcons: true,
        },

        onRowDblClick: function (e) {
            if (e.rowType === "data" && DevExpress.devices.current().deviceType === 'desktop') {
                editingRowId = e.key;

                let choosedItem = getChoosedItem(e.key)
                let fuelFlowType = additionalResources.fuelFlowTypes.find(el => el.id === choosedItem.fuel_tank_flow_type_id).slug

                if (fuelFlowType === 'outcome' || fuelFlowType === 'simultaneous_income_outcome') {
                    if (!choosedItem.third_party_mark) {
                        choosedItem.fuelConsumerType = 'our_technik_radio_elem'
                    } else {
                        choosedItem.fuelConsumerType = 'third_party_technik_radio_elem'
                    }

                    if (fuelFlowType === 'outcome') {
                        showDecreaseFuelPopup(choosedItem)
                    }
                    if (fuelFlowType === 'simultaneous_income_outcome') {
                        showSimultaneousIncomeOutcomeFuelPopup(choosedItem)
                    }
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
            resetVars();
            resetStores();
        },

        toolbar: {
            visible: true,
            items: [{}]
        },
    }
</script>
