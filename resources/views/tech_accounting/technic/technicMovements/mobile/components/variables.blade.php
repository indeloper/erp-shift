<script>
    const toolbarItems = [
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
            {
                name: 'cancelTranspotation',
                widget: 'dxButton',
                toolbar: 'bottom',
                location: 'before',
                options: {
                    text: 'Отменить заявку',
                    stylingMode: 'outlined',
                    type: 'danger',
                },
                onClick(e) {
                    entitiesDataSource.store().update(editingRowId, {"finish_result":"cancelled"}).then(()=>{$('#entitiesListMobile').dxList('instance').reload()})
                    popupMobile.hide()                 
                }
            },
            {
                name: 'completeTranspotation',
                widget: 'dxButton',
                toolbar: 'bottom',
                location: 'before',
                options: {
                    text: 'Завершить транспортировку',
                    stylingMode: 'outlined',
                    type: 'success',
                },
                onClick(e) {
                    entitiesDataSource.store().update(editingRowId, {"finish_result":"completed"}).then(()=>{$('#entitiesListMobile').dxList('instance').reload()})
                    popupMobile.hide()
                }
            }
        ]
</script>