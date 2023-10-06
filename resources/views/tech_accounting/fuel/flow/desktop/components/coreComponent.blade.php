<script>
    $(()=>{
        const mainPopup = $('#mainPopup').dxPopup({
            visible: false,
            showTitle: true,
            hideOnOutsideClick: true,
            showCloseButton: true,
            maxWidth: '30vw',
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
                        formData = $('#mainForm').dxForm('instance').option('formData')
                        formData.newAttachments = newAttachments;
                        entitiesDataSource.store().insert(formData);
                        entitiesDataSource.reload()
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
                    caption: "Движение топлива по ёмкостям",
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