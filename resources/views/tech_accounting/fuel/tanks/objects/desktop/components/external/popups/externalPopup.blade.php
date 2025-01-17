<script>
    let externalPopupSettings = {
        visible: false,
        showTitle: true,
        hideOnOutsideClick: false,
        showCloseButton: true,
        maxWidth: '500px',
        height: 'auto',
        onHiding() {
            resetExternalVars();
            resetExternalStores();
        },
        toolbarItems: [{
            widget: 'dxButton',
            toolbar: 'bottom',
            location: 'after',
            options: {
                text: 'Добавить',
            },
            onClick(e) {

                if (!DevExpress.validationEngine.validateGroup("documentExternalValidationGroup").isValid) {
                    return;
                }

                formData = $('#externalForm').dxForm('instance').option('formData')
                formData.newAttachments = externalNewAttachments;
                formData.deletedAttachments = externalDeletedAttachments;

                externalOperations.push(formData)
                updateFuelFlowDataGrid(formData)
                externalPopup.hide()
            }
        },
            {
                widget: 'dxButton',
                toolbar: 'bottom',
                location: 'after',
                options: {
                    text: 'Отмена',
                },
                onClick() {
                    externalPopup.hide()
                }
            }
        ]
    }

    const externalPopup = $('#externalPopup').dxPopup(externalPopupSettings).dxPopup('instance')
</script>
