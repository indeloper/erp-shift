<script>
    const dataGridSettings = {
        height: getGridHeight(),

        focusedRowEnabled: false,
        hoverStateEnabled: false,
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

        // onRowDblClick: function(e) {
        //     if (e.rowType === "data" && DevExpress.devices.current().deviceType === 'desktop') {
        //         e.component.editRow(e.rowIndex);
        //     }
        // },
        // onEditingStart(e) {
        //     editingRowId = e.key;
        // },
        onEditorPreparing: (e) => {
            if (e.parentType === `filterRow` && e.lookup)
                createFilterRowTagBoxFilterControlForLookupColumns(e)
        },
        onSaved() {
            resetVars();
            resetStores();
        },

        // onEditCanceling(e) {

        // if (!skipStoppingEditingRow && e.changes.length) {

        //     e.cancel = true
        //     skipStoppingEditingRow = 0

        //     customConfirmDialog("Вы уверены, что отменить изменения?").show().then((dialogResult) => {
        //         if (dialogResult) {
        //             resetVars();
        //             resetStores();
        //             skipStoppingEditingRow = 1;
        //             e.component.cancelEditData();
        //         }
        //     })
        // } else {
        //     resetVars();
        //     resetStores();
        // }

        // },

        toolbar: {
            visible: true,
            items: [{}]
        },
    }
</script>
