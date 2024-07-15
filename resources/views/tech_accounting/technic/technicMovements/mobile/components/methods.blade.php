<script>
    const getTechnicCategoryName = (categoryId) => {
        const rawCategoryName = additionalResources.technicCategories.find(el=>el.id===categoryId).name.toLowerCase()
        let categoryName
        additionalResources.technicCategoryNameAttrs.forEach(el=>{
            if(rawCategoryName.startsWith(el.starts) && rawCategoryName.includes(el.contains))
            categoryName = el.result
            else
            categoryName = rawCategoryName
        })    
        return categoryName
    }

    const submitDxForm = (dxForm) => {
        if (!DevExpress.validationEngine.validateGroup(dxForm.option('validationGroup')).isValid) {
            return;
        }

        const formData = dxForm.option('formData');
        formData.newComments = newComments;
        formData.newAttachments = newAttachments;
        formData.deletedAttachments = deletedAttachments;

        if(!editingRowId) {
            entitiesDataSource.store().insert(formData);
        } 
        else {
            entitiesDataSource.store().update(editingRowId, formData, true);
        }

        popupMobile.hide()
        
    }

    const getPopupTitle = () => {
        return editingRowId ? `Заявка #${editingRowId}` : 'Новая заявка'
    }

    const setPopupToolbarItems = () => {

        let toolbarItemsTmp = Array.from(toolbarItems)

        const statusId = choosedItemData.technic_movement_status_id
        const statusSlug = additionalResources.technicMovementStatuses.find(el=>el.id===statusId)?.slug
        
        if(statusSlug === 'completed' || statusSlug === 'cancelled' || !statusSlug) {
            toolbarItemsTmp = toolbarItemsTmp.filter(el=>el.name != 'completeTranspotation' && el.name != 'cancelTranspotation')
        }

        if(statusSlug != 'inProgress') {
            toolbarItemsTmp = toolbarItemsTmp.filter(el=>el.name != 'completeTranspotation')
        }

        $('#popupMobile').dxPopup({
            toolbarItems: toolbarItemsTmp
        })        
    }

</script>