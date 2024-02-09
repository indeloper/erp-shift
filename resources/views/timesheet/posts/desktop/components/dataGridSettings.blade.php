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
        paging: {
            enabled: true,
            pageSize: 100,
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
        onRowDblClick: function(e) {
            if (e.rowType === "data" && DevExpress.devices.current().deviceType === 'desktop') {
                e.component.editRow(e.rowIndex);
            }
        },
        onEditingStart: openFormForEditing,
        onRowInserting: openFormForInserting,
        onEditorPreparing: (e) => {
            if (e.parentType === `filterRow` && e.lookup)
                createFilterRowTagBoxFilterControlForLookupColumns(e)
        },
        onSaved() {

        },
        onEditCanceling(e) {

        },
        toolbar: {
            visible: true,
            items: [{}]
        },
    }

    function openFormForInserting(rowInsertingEventArguments) {

    }

    function openFormForEditing(editingStartEventArguments) {
        editingStartEventArguments.cancel = true;

        editingStartEventArguments.component.beginCustomLoading('Загрузка карточки документа...');

        editingStartEventArguments.component.getDataSource().store().byKey(editingStartEventArguments.key).done((data) => {
            let formOptions = {
                formData: data,
                editingState: formEditStates.UPDATE,
                dataGridInstance: editingStartEventArguments.component,
                ...editingStartEventArguments.component.option('editing.form')
            };

            const form = $('<div>').dxForm(formOptions);

            let popupOptions = {
                contentTemplate: () => {
                    return form
                },
                ...editingStartEventArguments.component.option('editing.popup')
            };

            let popupInstance = ($('<div>').appendTo('body')).dxPopup(popupOptions).dxPopup("instance");

            form.dxForm('instance').option('popupInstance', popupInstance);

            popupInstance.show();

            editingStartEventArguments.component.endCustomLoading();
        });
    }
</script>
