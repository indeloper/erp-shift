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
            e.component.option('title', getPopupTitle()) 
            setPopupToolbarItems()
        },
        onHiding(e) {
            resetVars();
            resetStores();
        },

        // toolbarItems: [
        //     {
        //         location: "before",
        //         widget: 'dxButton',
        //         options: {
        //             icon: 'back',
        //             stylingMode: 'text',
        //             elementAttr: {
        //                 style: 'padding-top:4px'
        //             }
        //         },
        //         onClick(e) {
        //             popupMobile.hide()
        //         }

        //     },
        //     {
        //         location: "after",
        //         widget: 'dxButton',
        //         validationGroup: "entityValidationGroup",
        //         options: {
        //             template: '<div class="text-color-blue">Сохранить</div>',
        //             stylingMode: 'text',
        //             elementAttr: {
        //                 'id': 'popupSaveButton'
        //             }
        //         },
        //         onClick(e) {
        //             submitDxForm($('#mainForm').dxForm('instance'))
        //         }
        //     },
        //     {
        //         name: 'cancelTranspotation',
        //         widget: 'dxButton',
        //         toolbar: 'bottom',
        //         location: 'before',
        //         options: {
        //             text: 'Отменить заявку',
        //             stylingMode: 'outlined',
        //             type: 'danger',
        //         },
        //         onClick(e) {
        //             entitiesDataSource.store().update(editingRowId, {"finish_result":"cancelled"})
        //             popupMobile.hide()                    
        //         }
        //     },
        //     {
        //         name: 'completeTranspotation',
        //         widget: 'dxButton',
        //         toolbar: 'bottom',
        //         location: 'before',
        //         options: {
        //             text: 'Завершить транспортировку',
        //             stylingMode: 'outlined',
        //             type: 'success',
        //         },
        //         onClick(e) {
        //             entitiesDataSource.store().update(editingRowId, {"finish_result":"completed"})
        //             popupMobile.hide()
        //         }
        //     }
        // ]
    }

    const popupMobile = $('#popupMobile').dxPopup(popupMobileSettings).dxPopup('instance')
</script>
