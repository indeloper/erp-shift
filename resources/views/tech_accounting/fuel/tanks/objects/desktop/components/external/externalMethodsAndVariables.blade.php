<script>
    let externalEditingRowId = 0;
    
    function resetExternalVars() {
        externalUploadingFiles = [];
        externalNewAttachments = [];
        externalEditingRowId = 0;
        externalDeletedAttachments = [];
        externalPermissions = {can_delete_project_object_document_files: true};
    }
    function resetExternalStores() {
        externalEntityInfoByID.store().clearRawDataCache()
        externalEntityInfoByID._isLoaded = false
    }
    
</script>