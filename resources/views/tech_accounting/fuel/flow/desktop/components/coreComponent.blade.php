<script>
    $(()=>{
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