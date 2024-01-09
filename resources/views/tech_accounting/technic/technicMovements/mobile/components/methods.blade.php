<script>
    const getTechnicCategoryName = (categoryId) => {
        const rawCategoryName = technicCategoriesStore.find(el=>el.id===categoryId).name.toLowerCase()
        let categoryName
        technicCategoryNameAttrsStore.forEach(el=>{
            if(rawCategoryName.startsWith(el.starts) && rawCategoryName.includes(el.contains))
            categoryName = el.result
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
</script>