<script>
    const popupMobileSettings = {
        fullScreen: true,
        visible: false,
        dragEnabled: false,
        hideOnOutsideClick: false,
        showCloseButton: false,
        dragAndResizeArea: false,
        dragEnabled: false,
        dragOutsideBoundary: false,
        enableBodyScroll: false,

        onHiding() {
            resetVars();
        },

        toolbarItems: [
            {
                location: "before",
                widget: 'dxButton',
                options: {
                    icon: 'back',
                    stylingMode: 'text',
                    elementAttr: {
                        style: 'padding-top:4px'
                    }
                },
                onClick(e) {
                    popupMobile.hide()
                }

            },
            {
                location: "after",
                widget: 'dxButton',
                validationGroup: "entityValidationGroup",
                options: {
                    template: '<div class="text-color-blue">Сохранить</div>',
                    stylingMode: 'text',
                    elementAttr: {
                        'id': 'popupSaveButton'
                    }
                },
                onClick(e) {
                    submitMobileForm()
                }
            },
        ]
    }

    const popupMobile = $('#popupMobile').dxPopup(popupMobileSettings).dxPopup('instance')
</script>
