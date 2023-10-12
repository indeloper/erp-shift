<script>
    function resetExternalVars() {
        externalUploadingFiles = [];
        externalNewAttachments = [];
        externalEditingRowId = 0;
        externalDeletedAttachments = [];
    }
    function resetExternalStores() {
        externalEntityInfoByID.store().clearRawDataCache()
        externalEntityInfoByID._isLoaded = false
    }
    
</script>