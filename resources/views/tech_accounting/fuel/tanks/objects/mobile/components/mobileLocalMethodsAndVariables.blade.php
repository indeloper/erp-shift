<script>
    let shownMobileFormType = '';
    
    function submitMobileForm() {
        if (!DevExpress.validationEngine.validateGroup("documentExternalValidationGroup").isValid) {
            return;
        }
        
        const formData = $('#externalForm').dxForm('instance').option('formData')
        
        if(shownMobileFormType === 'increaseFuelForm' || shownMobileFormType === 'decreaseFuelForm') {
            submitIncreaseFuelForm(formData);
        }

        console.log(formData);
    }

    function submitIncreaseFuelForm(formData) {
        if(shownMobileFormType === 'increaseFuelForm') {
            formData.newAttachments = externalNewAttachments;
        }
        externalEntitiesDataSource.store().insert(formData);
    }
</script>