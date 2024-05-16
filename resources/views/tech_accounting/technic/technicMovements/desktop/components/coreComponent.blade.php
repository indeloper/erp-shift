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
                        editorType: "dxDataGrid",
                        editorOptions: {
                            dataSource: entitiesDataSource,
                            ...dataGridSettings,
                            columns: dataGridColumns,
                            elementAttr: {
                                id: "mainDataGrid"
                            },
                            onOptionChanged(e) {
                                if(!e.value || !e.value[0] || !e.value[0].data || !e.value[0].data.order_start_date) 
                                return

                                const orderEndDate = $('#orderEndDate').dxDateBox('instance')
                                orderEndDate.option('min', e.value[0].data.order_start_date)
                            },
                            // onEditingStart(e) {
                            //     console.log(e.component.columnOption("previous_object_id", "validationRules"));
                            // }
                        }
                    }]
                }
            ]
        })
    })
</script>