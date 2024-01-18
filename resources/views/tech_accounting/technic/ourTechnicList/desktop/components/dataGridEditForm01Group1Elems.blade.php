<script>
    
    function switchTechnicAffiliation(value) {
        const dataGrid = $('#mainDataGrid').dxDataGrid('instance');

        if (value === 'third_party_technik_radio_elem') {
            dataGrid.cellValue(dataGrid.getRowIndexByKey(editingRowId), 'third_party_mark', true)
            choosedThirdPartyTechnic()
        } 

        if (value === 'our_technik_radio_elem') {
            dataGrid.cellValue(dataGrid.getRowIndexByKey(editingRowId), 'third_party_mark', false)
            choosedOurTechnic()
        }
    } 

    function choosedThirdPartyTechnic() {
        const mainForm = $('#mainForm').dxForm('instance')

        datafieldsTechnicOwnerGroupGroup1.forEach(el => mainForm.itemOption('technicOwnerGroup.' + el, 'visible', false))
        datafieldsTechnicOwnerGroupGroup2.forEach(el => mainForm.itemOption('technicOwnerGroup.' + el, 'visible', true))
        mainForm.itemOption('technicOwnerGroup.third_party_mark', 'value', true)
    }

    function choosedOurTechnic() {
        const mainForm = $('#mainForm').dxForm('instance')

        datafieldsTechnicOwnerGroupGroup1.forEach(el => mainForm.itemOption('technicOwnerGroup.' + el, 'visible', true))
        datafieldsTechnicOwnerGroupGroup2.forEach(el => mainForm.itemOption('technicOwnerGroup.' + el, 'visible', false))
        mainForm.itemOption('technicOwnerGroup.third_party_mark', 'value', false)
    }
    
    const dataGridEditForm01Group1Elems = [
        {
            dataField: "name",
            colSpan: 2,
            validationRules: [{
                type: 'required',
                message: 'Укажите значение',
            }],
        },
                
        {
            dataField: "technic_brand_id",
            colSpan: 1,
            editorOptions: {
                onSelectionChanged(e){
                    
                    if(e.selectedItem.id) {
                        let interval = setInterval(() => {
                            if($('#technic_brand_model_id').dxSelectBox('instance')) {
                                clearInterval(interval)
                                if(userPermissions.technics_create_update_delete) {
                                    $('#technic_brand_model_id').dxSelectBox('instance')?.option('readOnly', false);
                                }
                                $('#technic_brand_model_id').dxSelectBox('instance')?.option('dataSource', additionalResources.technicModels.filter(el=>el.technic_brand_id ===e.selectedItem.id));
                            }
                        }, 100)
                    }
                },
                
            },
            validationRules: [{
                type: 'required',
                message: 'Укажите значение',
            }],
        },

        {
            dataField: "technic_brand_model_id",
            colSpan: 1,
            editorType: 'dxSelectBox',
            editorOptions: {
                readOnly: true,
              elementAttr: {
                id: 'technic_brand_model_id'
              }  
            },
            validationRules: [{
                type: 'required',
                message: 'Укажите значение',
            }],
        },

        {
            dataField: "technic_category_id",
            colSpan: 1,
            validationRules: [{
                type: 'required',
                message: 'Укажите значение',
            }],
        },
        {
            itemType: "empty"
        },
        {
            itemType: "group",
            colSpan: 2,
            colCount: 2,
            caption: 'Принадлежность техники',
            name: "technicOwnerGroup",
            items: [
                {
                    dataField: 'third_party_mark_2',
                    editorType: "dxRadioGroup",
                    label: {
                            visible: false
                    },                                
                    editorOptions: {
                        items: [
                            {id: 'our_technik_radio_elem', text: 'Своя техника'},
                            {id: 'third_party_technik_radio_elem', text: 'Сторонняя техника'}, 
                        ],
                        valueExpr: 'id',
                        displayExpr: 'text',
                        layout: 'horizontal',
                        onInitialized(e) {
                            const dataGrid = $('#mainDataGrid').dxDataGrid('instance');
                            
                            if (!dataGrid.cellValue(dataGrid.getRowIndexByKey(editingRowId), 'third_party_mark')) {
                                e.component.option('value','our_technik_radio_elem');
                            } 
                            else {
                                e.component.option('value','third_party_technik_radio_elem');
                            }
                        },
                        // disabled: Boolean(isFuelFlowDataFieldUpdateAvailable('fuelConsumerType')),
                        onValueChanged(e) {
                            switchTechnicAffiliation(e.value)
                        }
                    },
                }, 
                {
                    itemType: "empty"
                },
                {
                    dataField: 'company_id',
                    editorType: "dxSelectBox",
                    editorOptions: {
                        elementAttr: {id: "our_technic_id_dxSelectBox"},
                        dataSource: additionalResources.companies,
                        valueExpr: 'id',
                        displayExpr: 'name',
                        // readOnly: Boolean(isFuelFlowDataFieldUpdateAvailable('our_technic_id')),
                    },
                    label: {
                        text: 'Компания'
                    },
                    validationRules: [{
                        type: 'required',
                        message: 'Укажите значение',
                    }],
                },
                {
                    dataField: "responsible_id",
                    colSpan: 1,
                    validationRules: [{
                        type: 'required',
                        message: 'Укажите значение',
                    }],
                },
                {
                    dataField: 'manufacture_year',
                    editorType: "dxNumberBox",
                },
                
                {
                    dataField: "exploitation_start",
                    caption: "Начало эксплуатации",
                    dataType: "date",
                    colSpan: 1,
                },
                
                {
                    dataField: 'contractor_id',
                    editorType: "dxSelectBox",
                    // visible: Boolean(!formItem.our_technic_id && editingRowId),
                    editorOptions: {
                        dataSource: additionalResources.contractors,
                        // readOnly: Boolean(isFuelFlowDataFieldUpdateAvailable('third_party_consumer')), 
                    },
                    label: {
                        text: 'Контрагент'
                    },
                    validationRules: [{
                        type: 'required',
                        message: 'Укажите значение',
                    }],
                },
                
                {
                    dataField: 'serial_number',
                },
                {
                    dataField: 'registration_number',
                },
                {
                    dataField: "inventory_number",
                    caption: "Инвентарный номер",
                    colSpan: 1,
                },
            ]
        }
    ];
</script>
