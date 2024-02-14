<script>
    const dataGridSettings = {
        height: "calc(100vh - 200px)",
        lalala: "pampampam",
        editing: {
            mode: "skPopup",
            popup: dataGridPopup,
            form: dataGridEditForm,
        },

        // onRowDblClick: function(e) {
        //     if (e.rowType === "data" && DevExpress.devices.current().deviceType === 'desktop') {
        //         e.component.editRow(e.rowIndex);
        //     }
        // },
        onEditingStart(e) {
            editingRowId = e.key;
        },
        // onEditorPreparing: (e) => {
        //     if (e.parentType === `filterRow` && e.lookup)
        //         createFilterRowTagBoxFilterControlForLookupColumns(e)
        // }
    }
</script>
