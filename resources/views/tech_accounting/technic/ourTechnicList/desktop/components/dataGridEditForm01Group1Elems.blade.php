<script>

    function switchTechnicAffiliation(value, formInstance) {
        if (value === 'third_party_technik_radio_elem') {
            choosedThirdPartyTechnic(formInstance)
        }
        if (value === 'our_technik_radio_elem') {
            choosedOurTechnic(formInstance)
        }
    }

    function choosedThirdPartyTechnic(formInstance) {
        datafieldsTechnicOwnerGroupGroup1.forEach(el => formInstance.itemOption('technicOwnerGroup.' + el, 'visible', false))
        datafieldsTechnicOwnerGroupGroup2.forEach(el => formInstance.itemOption('technicOwnerGroup.' + el, 'visible', true))
        formInstance.option('formData').third_party_mark = true
    }

    function choosedOurTechnic(formInstance) {
        datafieldsTechnicOwnerGroupGroup1.forEach(el => formInstance.itemOption('technicOwnerGroup.' + el, 'visible', true))
        datafieldsTechnicOwnerGroupGroup2.forEach(el => formInstance.itemOption('technicOwnerGroup.' + el, 'visible', false))
        formInstance.option('formData').third_party_mark = false
        formInstance.option('formData').contractor_id = null
    }

    function getThirdPartyMarkRadioGroupItems() {
        return [
                    {id: 'our_technik_radio_elem', text: 'Своя техника'},
                    {id: 'third_party_technik_radio_elem', text: 'Сторонняя техника'},
                ]
    }

    const dataGridEditForm01Group1Elems = [
        {
            dataField: "name",
            label: {text: 'Наименование'},
            colSpan: 2,
            validationRules: [{
                type: 'required',
                message: 'Укажите значение',
            }],
        },
        {
            dataField: "technic_brand_id",
            editorType: "dxSelectBox",
            label: {text: 'Марка'},
            editorOptions: {
                dataSource: additionalResources.technicBrands,
                valueExpr: "id",
                displayExpr: "name",
                onSelectionChanged(e){
                    const formInstance = getParentFormInstanceByElement(e.element)
                    const modelsSelectBox = formInstance.getEditor('technic_brand_model_id')
                    console.log(formInstance.option('formData'));
                    
                    if(typeof modelsSelectBox === 'undefined') {
                        return
                    }

                    if(e.selectedItem.id) {
                        if(userPermissions.technics_create_update_delete) {
                            modelsSelectBox.option('readOnly', false);
                        }
                        modelsSelectBox.option('dataSource', additionalResources.technicModels.filter(el=>el.technic_brand_id ===e.selectedItem.id));
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
            label: {text: 'Модель'},
            editorType: 'dxSelectBox',
            editorOptions: {
                dataSource: additionalResources.technicModels,
                valueExpr: "id",
                displayExpr: "name",
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
            label: {text: 'Категория'},
            editorType: 'dxSelectBox',
            editorOptions: {
                dataSource: additionalResources.technicCategories,
                valueExpr: "id",
                displayExpr: "name",
            },
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
                        items: getThirdPartyMarkRadioGroupItems(),
                        valueExpr: 'id',
                        displayExpr: 'text',
                        layout: 'horizontal',
                        // disabled: Boolean(isFuelFlowDataFieldUpdateAvailable('fuelConsumerType')),
                        onValueChanged(e) {
                            formInstance = getParentFormInstanceByElement(e.element)
                            switchTechnicAffiliation(e.value, formInstance)
                        }
                    },
                },
                {
                    itemType: "empty"
                },
                {
                    dataField: 'company_id',
                    label: {text: 'Компания'},
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: additionalResources.companies,
                        valueExpr: 'id',
                        displayExpr: 'name',
                    },
                    validationRules: [{
                        type: 'required',
                        message: 'Укажите значение',
                    }],
                },
                {
                    dataField: "responsible_id",
                    label: {text: 'Ответственный'},
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: additionalResources.technicResponsibles,
                        valueExpr: "id",
                        displayExpr: "user_full_name"
                    },
                    validationRules: [{
                        type: 'required',
                        message: 'Укажите значение',
                    }],
                },
                {
                    dataField: 'manufacture_year',
                    label: {text: 'Год производства'},
                    editorType: "dxNumberBox",
                },

                {
                    dataField: "exploitation_start",
                    label: {text: 'Начало эксплуатации'},
                    dataType: "date",
                },

                {
                    dataField: 'contractor_id',
                    editorType: "dxSelectBox",
                    label: {text: 'Контрагент'},
                    // visible: Boolean(!formItem.our_technic_id && editingRowId),
                    editorOptions: {
                        dataSource: additionalResources.contractors,
                        valueExpr: "id",
                        displayExpr: "short_name"
                        // readOnly: Boolean(isFuelFlowDataFieldUpdateAvailable('third_party_consumer')),
                    },
                    validationRules: [{
                        type: 'required',
                        message: 'Укажите значение',
                    }],
                },

                {
                    dataField: 'serial_number',
                    label: {text: 'Заводской номер'},
                },
                {
                    dataField: 'registration_number',
                    label: {text: 'Гос. номер'},
                },
                {
                    dataField: "inventory_number",
                    label: {text: 'Бортовой номер'},
                },
            ]
        }
    ];
</script>
