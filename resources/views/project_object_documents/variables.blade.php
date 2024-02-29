<script>
    editingRowId = 0;
    skipStoppingEditingRow = 0;
    newAttachments = [];
    deletedAttachments = [];
    // if(typeof editingRowId === undefined) {
    //     let editingRowId = 0;
    // }
    // if(typeof skipStoppingEditingRow === 'undefined') {
    //     console.log('typeof skipStoppingEditingRow');
    //     let skipStoppingEditingRow = 0;
    // }
    // if(typeof newAttachments === undefined) {
    //     let newAttachments = [];
    // }
    // if(typeof deletedAttachments === undefined) {
    //     let deletedAttachments = [];
    // }
    let editingRowTypeId = 0;
    let editingRowStatusId = 0;
    let editingRowStartOptions = [];
    let editingRowNewStatusId = 0;
    let editingRowTypeStatusOptions = [];
    let editingRowTypeStatusOptions_tmp = [];
    let editingRowChanges = [];
    
    let projectObjectsFilter = [];
    let customFilter = [];
    customFilter['projectObjectsFilter'] = [];
    customFilter['projectResponsiblesFilter'] = [];
    let filterOptions = {};
    let permissions = {};
    let newCommentsArr = [];
    let choosedDocumentItemData = {};
</script>