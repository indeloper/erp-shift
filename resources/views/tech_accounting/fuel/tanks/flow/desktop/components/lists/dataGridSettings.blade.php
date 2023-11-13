<script>
    const dataGridSettings = {
        height: getGridHeight(),

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
            // mode: "popup",
            // popup: dataGridPopup,
            // form: dataGridEditForm,
            allowUpdating: false,
            allowAdding: false,
            allowDeleting: true,
            selectTextOnEditStart: false,
            useIcons: true,
        },

        onRowDblClick: function (e) {
            if (e.rowType === "data" && DevExpress.devices.current().deviceType === 'desktop') {
                editingRowId = e.key;
                let choosedItem = $('#mainDataGrid').dxDataGrid('instance').getDataSource().items()[0].items.find(el => el.id === e.key)
                let fuelFlowType = fuelFlowTypesStore.__rawData.find(el => el.id === choosedItem.fuel_tank_flow_type_id).slug

                if (fuelFlowType === 'outcome')
                    showDecreaseFuelPopup(choosedItem)

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
