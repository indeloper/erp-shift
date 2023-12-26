<script>
    const infoTabbedGroup = {
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
                caption: 'Техника',
                name: 'technicFormGroup',
                items: [
                    {
                        dataField: "technic_category_id",
                        editorOptions: {
                            elementAttr: {
                                id: 'technicCategoryIdDatafield'
                            },
                            onSelectionChanged(e) {

                                const technicIdDatafieldInstance = $('#technicIdDatafield').dxSelectBox('instance')
                                technicIdDatafieldInstance?.option('dataSource', technicsListStore.filter(el=>el.technic_category_id === e.selectedItem.id));
                                technicIdDatafieldInstance?.option('value', null)

                                const responsibleIdDatafieldInstance = $('#responsibleIdDatafield').dxSelectBox('instance')
                                let choosedCategory;
                                if(e.selectedItem.name === 'Гусеничные краны') {
                                    choosedCategory = 'oversize';
                                    
                                } else {
                                    choosedCategory = 'standartSize';
                                }
                                responsibleIdDatafieldInstance?.option('dataSource', technicResponsiblesByTypesStore[choosedCategory]);
                                
                                if(!technicResponsiblesByTypesStore[choosedCategory].find(el=>el.id === responsibleIdDatafieldInstance?.option('value'))) {
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
                        editorOptions: {
                            elementAttr: {
                                id: 'technicIdDatafield'
                            }
                        }
                    },
                    {
                        dataField: "object_id",
                        colSpan: 2,
                        validationRules: [{
                            type: 'required',
                            message: 'Укажите значение',
                        }],
                    },
                    {
                        dataField: "previous_object_id",
                        colSpan: 2,
                    },
                    {
                        dataField: "order_comment",
                        colSpan: 2,
                    },

                ]
            },
            {
                itemType: "group",
                colSpan: 2,
                colCount: 2,
                caption: 'Период эксплуатации план',
                items: [
                    {
                        dataField: "order_start_date",
                        validationRules: [{
                            type: 'required',
                            message: 'Укажите значение',
                        }],
                    },
                    {
                        dataField: "order_end_date",
                    },

                ]
            },
            {
                itemType: "group",
                colSpan: 2,
                colCount: 2,
                caption: 'Перевозка',
                items: [
                    {
                        dataField: "technic_movement_status_id",
                        validationRules: [{
                            type: 'required',
                            message: 'Укажите значение',
                        }],
                    },
                
                    {
                        dataField: "responsible_id",
                        editorOptions: {
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
                        editorOptions: {
                            type: "datetime",
                        }
                    },
                    {
                        dataField: "contractor_id",
                    },
                ]
            },
        ]

    }
</script>