<script>
    $(() => {
        $("#dataGridAncor").dxForm({
            items: [
                {
                    itemType: "group",
                    caption: "Детальный табель",
                    cssClass: "datagrid-container",
                    colCount: 2,
                    items: [
                        {
                            colSpan: 2,
                            name: "mainDataGrid",
                            editorType: "dxDataGrid",
                            editorOptions: {
                                dataSource: entitiesDataSource,
                                ...dataGridSettings,
                                elementAttr: {
                                    id: "timesheet-datagrid"
                                }
                            }
                        },
                        {
                            itemType: "empty"
                        },
                        {
                            itemType: "button",
                            buttonOptions: {
                                text: "Сохранить",
                                type: "success",
                                onClick: () => {
                                    alert('Saving');
                                    $("#timesheet-datagrid").dxDataGrid("instance").saveEditData();
                                }
                            }
                        }
                    ]
                }
            ]
        })
    })
</script>
