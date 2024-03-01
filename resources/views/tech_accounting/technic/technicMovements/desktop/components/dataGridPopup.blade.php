<script>
    
    const dataGridPopup =
        {
            showTitle: true,
            hideOnOutsideClick: false,
            showCloseButton: true,
            maxWidth: '60vw',
            height: 'auto',
            onHiding() {
                resetVars();
                resetStores();
                dataGrid = $('#mainDataGrid').dxDataGrid('instance')
                setReadonlyFormElemsProperties(false, dataGrid)
            },
            onShowing(e) {
                let title = 'Заявка на перемещение техники'
                title += editingRowId ? ` #${editingRowId}` : ''
                e.component.option('title', title)
                
                const dataGrid = $('#mainDataGrid').dxDataGrid('instance')

                if(!editingRowId) {
                    dataGrid.columnOption('responsible_id', 'validationRules', [])
                    return
                } else {
                    dataGrid.columnOption('responsible_id', 'validationRules', [{type: 'required', message: 'Укажите значение'}])
                }

                const statusId = dataGrid.cellValue(dataGrid.getRowIndexByKey(editingRowId), 'technic_movement_status_id')
                const statusSlug = additionalResources.technicMovementStatuses.find(el=>el.id===statusId).slug

                if(statusSlug === 'completed' || statusSlug === 'cancelled') {
                    return
                }
                 
                let toolbarItems = e.component.option('toolbarItems')
                
                toolbarItems.push({
                    widget: 'dxButton',
                    toolbar: 'bottom',
                    location: 'before',
                    options: {
                        text: 'Отменить заявку',
                        stylingMode: 'outlined',
                        type: 'danger',
                    },
                    onClick(e) {
                        entitiesDataSource.store().update(editingRowId, {"finish_result":"cancelled"})
                        $('#mainDataGrid').dxDataGrid('instance').cancelEditData()
                    }
                })

                if(statusSlug === 'inProgress') {
                    toolbarItems.push({
                        widget: 'dxButton',
                        toolbar: 'bottom',
                        location: 'before',
                        options: {
                            text: 'Завершить транспортировку',
                            stylingMode: 'outlined',
                            type: 'success',
                        },
                        onClick(e) {
                            entitiesDataSource.store().update(editingRowId, {"finish_result":"completed"})
                            $('#mainDataGrid').dxDataGrid('instance').cancelEditData()
                        }
                    })
                }

                e.component.option('toolbarItems', toolbarItems)  
            },
        }

    
</script>
