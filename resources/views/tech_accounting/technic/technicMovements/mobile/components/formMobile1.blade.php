<script>
    const renderPopupContent = () => {

        $('#mainForm').dxForm({
            elementAttr: {
                id: "mainForm"
            },
            validationGroup: "documentValidationGroup",
            colCount: 1,

            onContentReady(e) {
                e.component.option('formData', choosedItemData)

                if(!editingRowId) {
                    $(".dx-form-group-caption:contains('Транспортировка техники')").closest('.dx-item .dx-box-item').hide()
                    e.component.itemOption("tab1.transportationGroup.responsible_id", "validationRules", []);
                    return;
                } 

                e.component.itemOption("tab1.transportationGroup.responsible_id", "validationRules", [{
                    type: 'required',
                    message: 'Укажите значение',
                }]);

                if(choosedItemData.technic_category_id) {
                    let responsibleEditor = e.component.getEditor("responsible_id")
                    let choosedCategory

                    if(additionalResources.technicCategories.find(el=>el.id === choosedItemData.technic_category_id).name != 'Гусеничные краны') {
                        responsibleEditor.option('dataSource', additionalResources.technicResponsiblesByTypes.standartSize)
                        choosedCategory = 'standartSize';
                    } else {
                        responsibleEditor.option('dataSource', additionalResources.technicResponsiblesByTypes.oversize)
                        choosedCategory = 'oversize';
                    }

                    if(responsibleEditor && !responsibleEditor.option('value')) {
                        if(additionalResources.technicResponsiblesByTypes[choosedCategory].find(el=>el.id === authUserId)) {
                            responsibleEditor.option('value', authUserId)
                        }
                    }
                }

                
            },
            items: [
                {
                    itemType: 'tabbed',
                    tabPanelOptions: {
                        deferRendering: false,
                    },
                    tabs: [
                        infoTabbedGroup,
                        filesTabbedGroup
                    ],
                }
            ]
        })
    }
    
</script>