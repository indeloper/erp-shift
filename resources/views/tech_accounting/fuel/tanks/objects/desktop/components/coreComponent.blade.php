<script>
    $("#dataGridAncor").dxForm({
        items: [
            {
                itemType: "group",
                caption: "Заголовок страницы",
                cssClass: "material-snapshot-grid",
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

</script>
