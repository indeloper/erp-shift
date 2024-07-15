<script>

    const dataGridEditForm = {
        onInitialized(e) {
            if(editingRowId) {
                dataGrid = $('#mainDataGrid').dxDataGrid('instance')
                choosedItem = entitiesDataSource.items().find(el=>el.id===editingRowId)
                // setReadonlyFormElemsProperties(choosedItem.author_id != authUserId && choosedItem.responsible_id != authUserId, dataGrid)
                
                const movementStatus = additionalResources.technicMovementStatuses.find(el=>el.id === choosedItem.technic_movement_status_id)
                if(movementStatus.slug === 'completed') {
                    choosedItem.finish_result = 'completed'
                }
                if(movementStatus.slug === 'cancelled') {
                    choosedItem.finish_result = 'cancelled'
                }
            }
        },
        onContentReady(e) {
            if(!editingRowId) {

                $('#finishResultRadioGroup').dxRadioGroup('instance')?.option('visible', false)
                $('#technicMovementStatusId').dxSelectBox('instance')?.option('value', additionalResources.technicMovementStatuses.find(el=>el.slug === 'created').id)
                $(".dx-form-group-caption:contains('Транспортировка техники')").closest('.dx-item .dx-box-item').hide()
                
                return;
            }
            
            const technicIdDatafieldInstance = $('#technicIdDatafield').dxSelectBox('instance')
            const responsibleIdDatafieldInstance = $('#responsibleIdDatafield').dxSelectBox('instance')
            const technicCategoryIdDatafieldInstance = $('#technicCategoryIdDatafield').dxSelectBox('instance')
            const categoryId = technicCategoryIdDatafieldInstance.option('value')
               
            technicIdDatafieldInstance.option('dataSource', additionalResources.technicsList.filter(el=>el.technic_category_id === categoryId));
 
            if(additionalResources.technicCategories.find(el=>el.id === categoryId).name === 'Гусеничные краны') {
                choosedCategory = 'oversize';
            } else {
                choosedCategory = 'standartSize';
            }
            
            responsibleIdDatafieldInstance?.option('dataSource', additionalResources.technicResponsiblesByTypes[choosedCategory]);

            if(responsibleIdDatafieldInstance && !responsibleIdDatafieldInstance.option('value')) {
                if(additionalResources.technicResponsiblesByTypes[choosedCategory].find(el=>el.id === authUserId)) {
                    responsibleIdDatafieldInstance.option('value', authUserId)
                }
            }
        },
        elementAttr: {
            id: "mainForm"
        },
        colCount: 1,
        items: [
            {
                itemType: 'tabbed',
                tabPanelOptions: {
                    deferRendering: false,
                    height: '426px'
                },
                tabs: [
                    infoTabbedGroup,
                    filesTabbedGroup
                ],
            }
        ],
    }

</script>
