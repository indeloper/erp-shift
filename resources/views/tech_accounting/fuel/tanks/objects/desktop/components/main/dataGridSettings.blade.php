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
        remoteOperations: false,

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
        grouping: {
            autoExpandAll: true,
        },

        editing: {
            mode: "popup",
            popup: dataGridPopup,
            form: dataGridEditForm,
            allowUpdating: true,
            allowAdding: true,
            allowDeleting: true,
            selectTextOnEditStart: false,
            useIcons: true,
        },

        onRowDblClick: function (e) {
            if (e.rowType === "data" && DevExpress.devices.current().deviceType === 'desktop') {
                e.component.editRow(e.rowIndex);
            }
        },
        onEditingStart(e) {
            editingRowId = e.key;
        },
        onEditorPreparing: (e) => {
            if (e.parentType === `filterRow` && e.lookup)
                createFilterRowTagBoxFilterControlForLookupColumns(e)
        },
        onSaved() {
            resetVars();
            resetStores();
        },

        onSaving(e) {
            let changes = e.changes

            if (changes.length === 0 && !externalOperations.length && !externalDeletedOperations.length)
            return;

            if (changes.length === 0 || !changes[0].data) {
                changes[0] = {
                    'data': {}
                };

                if (externalOperations.length || externalDeletedOperations.length) {
                    changes[0].key = editingRowId;
                    changes[0].type = 'update'
                }
            }

            changes[0].data.externalOperations = externalOperations
            changes[0].data.externalDeletedOperations = externalDeletedOperations
        },

        onEditCanceling(e) {

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

        },

        toolbar: {
            visible: true,
            items: [{}]
        },

    }
</script>
