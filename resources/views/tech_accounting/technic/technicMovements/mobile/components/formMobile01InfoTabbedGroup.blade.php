<script>
   
    const infoTabbedGroup = {
        tabTemplate(data, index, element) {
            return '<div class="tab-template-header-wrapper"><div class="fa fa-info-circle info-circle-icon-color tab-template-header-icon-elem"></div><div>Инфо</div></div>'
        },
        title:'tab1',       
        items: [
            {
                dataField: "technic_category_id",
                editorType: 'dxSelectBox',
                label: {
                    text: 'Категория техники'
                },
                
                editorOptions: {
                    dataSource: additionalResources.technicCategories,
                    valueExpr: "id",
                    displayExpr: "name",

                    elementAttr: {
                        id: 'technicCategoryIdDatafield'
                    },

                    onInitialized(e) {
                        e.component.option('readOnly', Boolean(editingRowId > 0))
                    },

                    itemTemplate(e) {
                        return `<div class="dx-list-item-line-break">${e.name}</div>`
                    },
                    
                    onSelectionChanged(e) {
                        if(!e.selectedItem)
                        return;

                        const technicIdDatafieldInstance = $('#technicIdDatafield').dxSelectBox('instance')
                                
                        technicIdDatafieldInstance?.option('dataSource', additionalResources.technicsList.filter(el=>el.technic_category_id === e.selectedItem.id));

                        if(!technicIdDatafieldInstance?.option('value')) {
                            technicIdDatafieldInstance?.option('value', null)
                        }

                        // if(!technicIdDatafieldInstance?.option('dataSource').find(el=>el.id === technicIdDatafieldInstance?.option('value'))) {
                        //     technicIdDatafieldInstance?.option('value', null)
                        // }
                        
                        // ************************
                        // const responsibleIdDatafieldInstance = $('#responsibleIdDatafield').dxSelectBox('instance')
                        // let choosedCategory;
                        // if(e.selectedItem.name === 'Гусеничные краны') {
                        //     choosedCategory = 'oversize';
                            
                        // } else {
                        //     choosedCategory = 'standartSize';
                        // }
                        // responsibleIdDatafieldInstance?.option('dataSource', additionalResources.technicResponsiblesByTypes[choosedCategory]);
                        
                        // if(!additionalResources.technicResponsiblesByTypes[choosedCategory].find(el=>el.id === responsibleIdDatafieldInstance?.option('value'))) {
                        //     responsibleIdDatafieldInstance?.option('value', null);
                        // }

                    //     if(!e.selectedItem)
                    //     return;

                    //     const technicIdDatafieldInstance = $('#technicIdDatafield').dxSelectBox('instance')
                    //     technicIdDatafieldInstance?.option('dataSource', additionalResources.technicsList.filter(el=>el.technic_category_id === e.selectedItem.id));
                    //     technicIdDatafieldInstance?.option('value', null)

                    //     const responsibleIdDatafieldInstance = $('#responsibleIdDatafield').dxSelectBox('instance')
                    //     let choosedCategory;
                    //     if(e.selectedItem.name === 'Гусеничные краны') {
                    //         choosedCategory = 'oversize';
                            
                    //     } else {
                    //         choosedCategory = 'standartSize';
                    //     }
                    //     responsibleIdDatafieldInstance?.option('dataSource', additionalResources.technicResponsiblesByTypes[choosedCategory]);
                        
                    //     if(!additionalResources.technicResponsiblesByTypes[choosedCategory].find(el=>el.id === responsibleIdDatafieldInstance?.option('value'))) {
                    //         responsibleIdDatafieldInstance?.option('value', null);
                    //     }
                    }
                },
                validationRules: [{
                    type: 'required',
                    message: 'Укажите значение',
                }],
            },
            {
                dataField: "technic_id",
                editorType: 'dxSelectBox',
                label: {
                    text: 'Техника'
                },
                editorOptions: {
                    elementAttr: {
                        id: 'technicIdDatafield'
                    },

                    dataSource: additionalResources.technicsList,
                    valueExpr: "id",
                    displayExpr: "name",

                    onInitialized(e) {
                        e.component.option('readOnly', Boolean(editingRowId > 0))
                    },

                    itemTemplate(e) {
                        return `<div class="dx-list-item-line-break">${e.name}</div>`
                    },

                    onSelectionChanged(e) {
                        const technicCategoryIdDatafieldInstance = $('#technicCategoryIdDatafield').dxSelectBox('instance')
                        const choosedTechnicCategory = additionalResources.technicCategories.find(el=>el.id === e.selectedItem?.technic_category_id)
                        if(choosedTechnicCategory) {    
                            technicCategoryIdDatafieldInstance?.option('value', choosedTechnicCategory?.id)
                            technicCategoryIdDatafieldInstance?.option('datsSource', [])
                        }
                    },
                },
                validationRules: [{
                    type: 'required',
                    message: 'Укажите значение',
                }],
            },
            {
                dataField: "object_id",
                editorType: 'dxSelectBox',
                label: {
                    text: 'Объект назначения'
                },
                editorOptions: {
                    dataSource: additionalResources.projectObjects,
                    valueExpr: "id",
                    displayExpr: "short_name",

                    onInitialized(e) {
                        e.component.option('readOnly', Boolean(editingRowId > 0))
                    },

                    itemTemplate(e) {
                        return `<div class="dx-list-item-line-break">${e.short_name}</div>`
                    },
                },
                validationRules: [{
                    type: 'required',
                    message: 'Укажите значение',
                }],
            },
            {
                dataField: "previous_object_id",
                editorType: 'dxSelectBox',
                label: {
                    text: 'Объект отправки'
                },
                editorOptions: {
                    dataSource: additionalResources.projectObjects,
                    valueExpr: "id",
                    displayExpr: "short_name",

                    onInitialized(e) {
                        e.component.option('readOnly', Boolean(editingRowId > 0))
                    },

                    itemTemplate(e) {
                        return `<div class="dx-list-item-line-break">${e.short_name}</div>`
                    },
                },
                validationRules: [{
                    type: 'required',
                    message: 'Укажите значение',
                }],
            },
            {
                dataField: "order_comment",
                editorType: "dxTextBox",
                label: {
                    text: 'Комментарий'
                },
                editorOptions: {
                    onInitialized(e) {
                        e.component.option('readOnly', Boolean(editingRowId > 0))
                    },
                }
            },

            {
                itemType: "group",
                caption: 'Плановый период эксплуатации',
                items: [
                    {
                        dataField: "order_start_date",
                        editorType: "dxDateBox",
                        label: {
                            text: 'Начало'
                        },
                        editorOptions: {
                            elementAttr: {
                                id: 'orderStartDate'
                            },
                            onInitialized(e) {
                                e.component.option('readOnly', Boolean(editingRowId > 0))
                            },

                            onInput(e) {
                                const orderEndDate = $('#orderEndDate').dxDateBox('instance')
                                orderEndDate.option('min', e.event.target.value)
                            },
                            min: new Date()
                        },                        
                        validationRules: [{
                            type: 'required',
                            message: 'Укажите значение',
                        }],
                    },
                    {
                        dataField: "order_end_date",
                        editorType: "dxDateBox",
                        label: {
                            text: 'Окончание'
                        },
                        editorOptions: {
                            elementAttr: {
                                id: 'orderEndDate'
                            }, 
                            onInitialized(e) {
                                e.component.option('readOnly', Boolean(editingRowId > 0))
                            },
                            onContentReady(e) {
                                if($('#orderStartDate').dxDateBox('instance')?.option('value')){
                                    e.component.option('min', $('#orderStartDate').dxDateBox('instance').option('value'))
                                }
                            }
                        },                  
                    },
                ]
            },
            {
                itemType: "group",
                caption: 'Транспортировка техники',
                name: 'transportationGroup',
                items: [
                    {
                        dataField: "movement_start_datetime",
                        editorType: "dxDateBox",
                        label: {
                            text: 'Дата транспортировки'
                        },
                        editorOptions: {
                            type: "datetime",
                            min: new Date()
                        }
                    },
                    
                    {
                        dataField: "responsible_id",
                        editorType: 'dxSelectBox',
                        label: {
                            text: 'Ответственный'
                        },

                        editorOptions: {
                            dataSource: additionalResources.technicResponsiblesAllTypes,
                            valueExpr: "id",
                            displayExpr: "user_full_name",
                            elementAttr: {
                                id: 'responsibleIdDatafield'
                            },
                        },                        
                    },
                    
                    {
                        dataField: "contractor_id",
                        editorType: 'dxSelectBox',
                        label: {
                            text: 'Перевозчик'
                        },
                        editorOptions: {
                            dataSource: additionalResources.technicCarriers,
                            valueExpr: "id",
                            displayExpr: "short_name"
                        },
                    },
                    // {
                    //     dataField: "finish_result",
                    //     editorType: "dxRadioGroup",
                        
                    //     label: {
                    //         visible: false
                    //     },    
                    //     editorOptions: {
                    //         items: [
                    //             {id: 'completed', text: 'Исполнена'},
                    //             {id: 'cancelled', text: 'Отменена'}, 
                    //         ],
                    //         valueExpr: 'id',
                    //         displayExpr: 'text',
                    //         layout: 'horizontal',
                    //         elementAttr: {
                    //             id: 'finishResultRadioGroup'
                    //         },
                    //     }
                    // },
                ]
            },
        ]

    }
</script>