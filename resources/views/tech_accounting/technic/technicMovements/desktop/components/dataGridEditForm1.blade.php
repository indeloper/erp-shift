<script>

    const dataGridEditForm = {
        onInitialized() {
            if(editingRowId) {
                dataGrid = $('#mainDataGrid').dxDataGrid('instance')
                choosedItem = entitiesDataSource.items().find(el=>el.id===editingRowId)
                setReadonlyFormElemsProperties(choosedItem.author_id != authUserId, dataGrid)
            }
        },
        onContentReady() {
            if(!editingRowId) {
                return;
            }
            const technicIdDatafieldInstance = $('#technicIdDatafield').dxSelectBox('instance')
            const responsibleIdDatafieldInstance = $('#responsibleIdDatafield').dxSelectBox('instance')
            const technicCategoryIdDatafieldInstance = $('#technicCategoryIdDatafield').dxSelectBox('instance')
            const categoryId = technicCategoryIdDatafieldInstance.option('value')
   
            technicIdDatafieldInstance.option('dataSource', technicsListStore.filter(el=>el.technic_category_id === categoryId));
 
            if(technicCategoriesStore.find(el=>el.id === categoryId).name === 'Гусеничные краны') {
                choosedCategory = 'oversize';
                
            } else {
                choosedCategory = 'standartSize';
            }
            responsibleIdDatafieldInstance.option('dataSource', technicResponsiblesByTypesStore[choosedCategory]);
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
                    height: "75vh"
                },
                tabs: [
                    infoTabbedGroup,
                    filesTabbedGroup
                ],
            }
        ],
    }

</script>
