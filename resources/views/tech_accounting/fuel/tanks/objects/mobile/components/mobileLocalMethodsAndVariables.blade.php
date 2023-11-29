<script>
    let shownMobileFormType = '';
    
    function submitMobileForm() {
        if (!DevExpress.validationEngine.validateGroup("documentExternalValidationGroup").isValid) {
            return;
        }
        
        const formData = $('#externalForm').dxForm('instance').option('formData')
        
        if(shownMobileFormType === 'increaseFuelForm' || shownMobileFormType === 'decreaseFuelForm') {
            submitFuelFlowForm(formData);
        }

        if(shownMobileFormType === 'movingTankForm') {
            submitMovingTankForm(formData);
        }

        if(shownMobileFormType === 'movingConfirmationTankForm') {
            submitMovingConfirmationTankForm(formData);
        }

        console.log(formData);
    }

    function submitFuelFlowForm(formData) {
        if(shownMobileFormType === 'increaseFuelForm') {
            formData.newAttachments = externalNewAttachments;
        }
        externalEntitiesDataSource.store().insert(formData);
    }

    function submitMovingTankForm(formData) {
        moveFuelTank(formData, popupMobile)
    }

    function submitMovingConfirmationTankForm(formData) {
        confirmMovingFuelTank(editingRowId, popupMobile)
    }
</script>