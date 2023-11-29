<script>
    $(() => {
        $("#dataGridAnchor").dxForm({
            items: [
                {
                    itemType: "group",
                    caption: "Объекты",
                    cssClass: "datagrid-container",
                    items: [{
                        name: "dataGridContainer",
                        editorType: "dxDataGrid",
                        editorOptions: {
                            dataSource: objectsDataSource,
                            ...dataGridSettings,
                            columns: dataGridColumns,
                            elementAttr: {
                                id: "dataGridContainer"
                            }
                        }
                    }]
                }
            ]
        })
    })
</script>
