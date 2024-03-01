<script>

    const infoTabbedGroup = {
        name: 'infoTab',
        tabTemplate(data, index, element) {
            return '<div class="tab-template-header-wrapper"><div class="fa fa-info-circle info-circle-icon-color tab-template-header-icon-elem"></div><div>Инфо</div></div>'
        },
        colCount: 2,
        onClick() {
            choosedFormTab = 'info'
        },
        items: [
            {
                itemType: "group",
                colSpan: 2,
                colCount: 2,
                // caption: 'Заявка',
                name: 'technicFormGroup',
                items: [
                    {
                        dataField: "technic_category_id",     
                        editorOptions: {
                            onInitialized(e) {
                                e.component.option('readOnly', Boolean(editingRowId > 0))
                            },
                            elementAttr: {
                                id: 'technicCategoryIdDatafield'
                            },
                            itemTemplate(e) {
                                return `<div class="dx-list-item-line-break">${e.name}</div>`
                            },
                            onSelectionChanged(e) {

                                const technicIdDatafieldInstance = $('#technicIdDatafield').dxSelectBox('instance')
                                
                                technicIdDatafieldInstance?.option('dataSource', additionalResources.technicsList.filter(el=>el.technic_category_id === e.selectedItem.id));

                                if(!technicIdDatafieldInstance?.option('value')) {
                                    technicIdDatafieldInstance?.option('value', null)
                                }

                                if(!technicIdDatafieldInstance?.option('dataSource').find(el=>el.id === technicIdDatafieldInstance?.option('value'))) {
                                    technicIdDatafieldInstance?.option('value', null)
                                }
                                
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
                        editorOptions: {
                            onInitialized(e) {
                                e.component.option('readOnly', Boolean(editingRowId > 0))
                            },
                            elementAttr: {
                                id: 'technicIdDatafield'
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
                            }
                        },
                        validationRules: [{
                            type: 'required',
                            message: 'Укажите значение',
                        }],
                    },
                    {
                        dataField: "object_id",
                        colSpan: 1,
                        editorOptions: {
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
                        colSpan: 1,
                        editorOptions: {
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
                        editorOptions: {
                            onInitialized(e) {
                                e.component.option('readOnly', Boolean(editingRowId > 0))
                            },
                        },
                        colSpan: 2,
                    },

                ]
            },
            {
                itemType: "group",
                name: 'explotationPeriod',
                colSpan: 2,
                colCount: 2,
                caption: 'Плановый период эксплуатации',
                items: [
                    {
                        dataField: "order_start_date",
                        label: {
                            text: 'Начало'
                        },
                        editorOptions: {
                            onInitialized(e) {
                                e.component.option('readOnly', Boolean(editingRowId > 0))
                            },
                            min: Date(),
                            elementAttr: {
                                id: 'orderStartDate'
                            },
                        },
                        validationRules: [{
                            type: 'required',
                            message: 'Укажите значение',
                        }]
                    },
                    {   
                        dataField: "order_end_date",
                        editorOptions: {
                            onInitialized(e) {
                                e.component.option('readOnly', Boolean(editingRowId > 0))
                            },
                            elementAttr: {
                                id: 'orderEndDate'
                            }, 
                            onContentReady(e) {
                                if($('#orderStartDate').dxDateBox('instance')?.option('value')){
                                    e.component.option('min', $('#orderStartDate').dxDateBox('instance').option('value'))
                                }
                            }
                        }

                    },

                ]
            },
            {
                itemType: "group",
                colSpan: 2,
                colCount: 3,
                caption: 'Транспортировка техники',
                name: 'transportationGroup',
                items: [
                    // {
                    //     dataField: "technic_movement_status_id",
                    //     editorOptions: {
                    //         readOnly: true,
                    //         elementAttr: {
                    //             id: 'technicMovementStatusId'
                    //         }
                    //     },
                    // },
                    {
                        dataField: "movement_start_datetime",
                        editorType: "dxDateBox",
                        editorOptions: {
                            type: "datetime",
                            min: new Date()
                        }
                    },

                    {
                        dataField: "contractor_id",
                    },
                
                    {
                        dataField: "responsible_id",
                        editorOptions: {
                            elementAttr: {
                                id: 'responsibleIdDatafield'
                            },
                            // onContentReady(e) {
                            //     console.log($());
                            // }
                        },
                        // validationRules: [{
                        //     type: 'required',
                        //     message: 'Укажите значение',
                        // }],
                    },
                    
                    
                    // {
                    //     dataField: "finish_result",
                    //     colSpan: 2,
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