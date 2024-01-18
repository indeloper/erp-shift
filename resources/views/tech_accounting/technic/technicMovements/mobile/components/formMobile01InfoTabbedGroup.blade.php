<script>
   
    const infoTabbedGroup = {
        tabTemplate(data, index, element) {
            return '<div class="tab-template-header-wrapper"><div class="fa fa-info-circle info-circle-icon-color tab-template-header-icon-elem"></div><div>Инфо</div></div>'
        },
       
        items: [
            {
                itemType: "group",
                caption: 'Заявка',
                name: 'technicFormGroup',
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
                            
                            onSelectionChanged(e) {

                                if(!e.selectedItem)
                                return;

                                const technicIdDatafieldInstance = $('#technicIdDatafield').dxSelectBox('instance')
                                technicIdDatafieldInstance?.option('dataSource', additionalResources.technicsList.filter(el=>el.technic_category_id === e.selectedItem.id));
                                technicIdDatafieldInstance?.option('value', null)

                                const responsibleIdDatafieldInstance = $('#responsibleIdDatafield').dxSelectBox('instance')
                                let choosedCategory;
                                if(e.selectedItem.name === 'Гусеничные краны') {
                                    choosedCategory = 'oversize';
                                    
                                } else {
                                    choosedCategory = 'standartSize';
                                }
                                responsibleIdDatafieldInstance?.option('dataSource', additionalResources.technicResponsiblesByTypes[choosedCategory]);
                                
                                if(!additionalResources.technicResponsiblesByTypes[choosedCategory].find(el=>el.id === responsibleIdDatafieldInstance?.option('value'))) {
                                    responsibleIdDatafieldInstance?.option('value', null);
                                }
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
                            displayExpr: "name"
                        }
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
                            displayExpr: "short_name"
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
                            displayExpr: "short_name"
                        },
                    },
                    {
                        dataField: "order_comment",
                        editorType: "dxTextBox",
                        label: {
                            text: 'Комментарий'
                        },
                    },

                ]
            },
            {
                itemType: "group",
                caption: 'Период эксплуатации план',
                items: [
                    {
                        dataField: "order_start_date",
                        editorType: "dxDateBox",
                        label: {
                            text: 'Начало'
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
                    },
                ]
            },
            {
                itemType: "group",
                caption: 'Перевозка',
                name: 'transportationGroup',
                items: [
                    {
                        dataField: "technic_movement_status_id",
                        editorType: 'dxSelectBox',
                        label: {
                            text: 'Статус'
                        },
                        editorOptions: {
                            dataSource: additionalResources.technicMovementStatuses,
                            valueExpr: "id",
                            displayExpr: "name",
                            readOnly: true,
                            elementAttr: {
                                id: 'technicMovementStatusId'
                            }
                        },
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
                            }
                        },
                        validationRules: [{
                            type: 'required',
                            message: 'Укажите значение',
                        }],
                        
                    },
                    {
                        dataField: "movement_start_datetime",
                        editorType: "dxDateBox",
                        label: {
                            text: 'Начало перевозки'
                        },
                        editorOptions: {
                            type: "datetime",
                        }
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
                    {
                        dataField: "finish_result",
                        editorType: "dxRadioGroup",
                        
                        label: {
                            visible: false
                        },    
                        editorOptions: {
                            items: [
                                {id: 'completed', text: 'Исполнена'},
                                {id: 'cancelled', text: 'Отменена'}, 
                            ],
                            valueExpr: 'id',
                            displayExpr: 'text',
                            layout: 'horizontal',
                            elementAttr: {
                                id: 'finishResultRadioGroup'
                            },
                        }
                    },
                ]
            },
        ]

    }
</script>