<script>
    function setReadonlyFormElemsProperties(isReadonly, dataGrid) {
        dataGrid.option("columns").forEach((columnItem) => {
            dataGrid.columnOption(columnItem.dataField, "allowEditing", !isReadonly)
        });
    }
</script>