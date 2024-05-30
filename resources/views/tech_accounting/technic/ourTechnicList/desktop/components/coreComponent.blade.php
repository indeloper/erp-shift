<script>
    $(()=>{
        $("#dataGridAnchor").dxForm({
            items: [
                {
                    itemType: "group",
                    caption: "{{$sectionTitle}}",
                    cssClass: "datagrid-container",
                    items: [{
                        name: "mainDataGrid",
                        editorType: "skDataGrid",
                        editorOptions: {
                            dataSource: entitiesDataSource,
                            ...dataGridSettings,
                            columns: dataGridColumns,
                            // elementAttr: {
                            //     id: "mainDataGrid"
                            // }
                        }
                    }]
                }
            ]
        })
    })
</script>
