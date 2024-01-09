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
        contentTemplate: '<div id="mainForm"></div>',
        onContentReady(e) {
            renderPopupContent()
        },
        onShowing(e) {
            $('#mainForm').dxForm('instance').repaint();
            $('#mainForm').dxForm('instance').option('formData', choosedItemData)
        },
        onHiding(e) {
            resetVars();
            resetStores();
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
                    submitDxForm($('#mainForm').dxForm('instance'))
                }
            },
        ]
    }

    const popupMobile = $('#popupMobile').dxPopup(popupMobileSettings).dxPopup('instance')
</script>
