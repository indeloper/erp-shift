<script>
    $(()=>{
        $('#dataGridContainer').dxDataGrid({
            dataSource: objectsDataSource,
            ...dataGridSettings,
            columns: dataGridColumns,
        })
    })
</script>
