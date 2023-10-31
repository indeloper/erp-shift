<script>
    $(() => {
        const mainPopup = $('#mainPopup').dxPopup({
            visible: false,
            showTitle: true,
            hideOnOutsideClick: false,
            showCloseButton: true,
            maxWidth: '500px',
            height: 'auto',
            onHiding() {
                resetVars();
                resetStores();
            },
            toolbarItems: [
                {
                    widget: 'dxButton',
                    toolbar: 'bottom',
                    location: 'after',
                    options: {
                        text: 'Сохранить',
                    },
                    onClick() {
                        if (!DevExpress.validationEngine.validateGroup("documentValidationGroup").isValid) {
                            return;
                        }
                        formData = $('#mainForm').dxForm('instance').option('formData')
                        formData.newAttachments = newAttachments;
                        formData.deletedAttachments = deletedAttachments;

                        if (!editingRowId)
                            entitiesDataSource.store().insert(formData);
                        else
                            entitiesDataSource.store().update(editingRowId, formData);

                        mainPopup.hide()
                    }
                },
                {
                    widget: 'dxButton',
                    toolbar: 'bottom',
                    location: 'after',
                    options: {
                        text: 'Отмена',
                    },
                    onClick() {
                        mainPopup.hide()
                    }
                }
            ]
        }).dxPopup('instance')

        $("#dataGridAncor").dxForm({
            items: [
                {
                    itemType: "group",
                    caption: "Движение топлива по емкостям",
                    cssClass: "datagrid-container",
                    items: [{
                        name: "mainDataGrid",
                        editorType: "dxDataGrid",
                        editorOptions: {
                            dataSource: entitiesDataSource,
                            ...dataGridSettings,
                            columns: dataGridColumns,
                            elementAttr: {
                                id: "mainDataGrid"
                            }
                        }
                    }]
                }
            ]
        })
    })
</script>
