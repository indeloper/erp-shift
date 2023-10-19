<script>
    let externalEditingRowId = 0;
    externalPermissions = {can_delete_project_object_document_files: true};

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