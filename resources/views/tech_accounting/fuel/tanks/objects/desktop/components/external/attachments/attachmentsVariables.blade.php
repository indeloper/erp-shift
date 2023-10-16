<script>
    let externalUploadingFiles = [];
    let externalNewAttachments = [];
    let externalEditingRowId = 0;
    let externalDeletedAttachments = [];
    let externalPermissions = {can_delete_project_object_document_files: true};
    const progressBarSettings = {
        min: 0,
        max: 100,
        width: '90%',
        showStatus: false,
        visible: true,
    }
</script>