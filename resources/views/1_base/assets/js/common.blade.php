<script>

// Общие
    function getUrlWithId(url, id) {
        return url.replace("/setId", "/" + id)
    }

    function getGridHeight() {
        let content = document.getElementsByClassName('content')[0]
        return 0.82 * content.clientHeight;
    }

    function resetVars() {
        editingRowId = 0;
        uploadingFiles = [];
        newAttachments = [];
        deletedAttachments = [];
        choosedItemData = {};
    }

    function resetStores() {
        if (typeof(entityInfoByID) !== 'undefined') {
            entityInfoByID.store()?.clearRawDataCache()
            entityInfoByID._isLoaded = false;
        }
    }

    function customConfirmDialog(message) {
        return DevExpress.ui.dialog.custom({
            showTitle: false,
            messageHtml: message,
            buttons: [{
                text: "Да",
                onClick: () => true
            }, {
                text: "Нет",
                onClick: () => false
            }]
        })
    }

    function setLoadedEntityInfo() {
        entityInfoByID.reload().done((data)=>{
            entityInfo = entityInfoByID.store().__rawData;
            //
        })
    }

    function fixDataBeforeFormRepaint ()  {
        //
    }

    function operationsWithFormDataAfterFormRepaint(choosedDataGridRowIndex, someData) {
        //
    }

    function setPopupItemVariablesMobile(itemData) {
        editingRowId = itemData.id
    }

    // function setReadonlyFormElemsProperties(isReadonly, dataGrid) {
    //     dataGrid.option("columns").forEach((columnItem) => {
    //         dataGrid.columnOption(columnItem.dataField, "allowEditing", !isReadonly)
    //     });
    // }

    function addAttachmentsAndCommentsToSendingForm(changes)
    {
        if (changes.length === 0 && !newAttachments.length && !deletedAttachments.length && !newComments.length)
            return;

        if (changes.length === 0 || !changes[0].data) {
            changes[0] = {
                'data': {}
            };

            if (newAttachments.length || deletedAttachments.length || newComments.length) {
                changes[0].key = editingRowId;
                changes[0].type = 'update'
            }
        }

        changes[0].data.newAttachments = newAttachments
        changes[0].data.deletedAttachments = deletedAttachments
        changes[0].data.newComments = newComments
    }
    // Конец Общие

</script>
