<script>
    const dataGridColumns = [
        
        {
            visible: false,
            dataField: "exploitation_start",
            caption: "Начало эксплуатации",
            dataType: "date",
        },

        {
            visible: false,
            caption: "Марка (old)",
            dataField: "brand",
            editorOptions: {
                readOnly:true
            }
        },
        {
            visible: false,
            caption: "Модель(old)",
            dataField: "model",
            editorOptions: {
                readOnly:true
            }
        },

        {
            visible: false,
            caption: "Компания",
            dataField: "company_id",
            lookup: {
                dataSource: companiesStore,
                valueExpr: "id",
                displayExpr: "name"
            },
        },
        {
            visible: false,
            caption: "Контрагент",
            dataField: "contractor_id",
            lookup: {
                dataSource: contractorsStore,
                valueExpr: "id",
                displayExpr: "short_name"
            },
        },
        {
            visible: false,
            caption: "Год выпуска",
            dataField: "manufacture_year",
            dataType: "number",
        },
        {
            visible: false,
            caption: "Заводской номер",
            dataField: "serial_number",
        },
        {
            visible: false,
            caption: "Гос. номер",
            dataField: "registration_number",
        },
        {
            caption: "Сторонняя техника",
            dataField: "third_party_mark",
            dataType: "boolean",
            // editorType: 'dxCheckBox',
            editorOptions: {
                enableThreeStateBehavior: false
            },
            width: 75,
        },
        {
            caption: "Идентификатор",
            dataField: "id",
            width: 75,
        },
        {
            dataField: "inventory_number",
            caption: "Инвентарный номер",
            width: 75,
        },
        {
            caption: "Категория",
            dataField: "technic_category_id",
            lookup: {
                dataSource: technicCategoriesStore,
                valueExpr: "id",
                displayExpr: "name"
            },
        },
        {
            caption: "Ответственный",
            dataField: "responsible_id",
            lookup: {
                dataSource: technicResponsiblesStore,
                valueExpr: "id",
                displayExpr: "user_full_name"
            },
        },
        {
            caption: "Марка",
            dataField: "technic_brand_id",
            lookup: {
                dataSource: technicBrandsStore,
                valueExpr: "id",
                displayExpr: "name"
            },
        },

        {
            caption: "Модель",
            dataField: "technic_brand_model_id",
            lookup: {
                dataSource: technicModelsStore,
                valueExpr: "id",
                displayExpr: "name"
            },
        },
        
        
        
        {
            caption: "Наименование",
            dataField: "name",
        },
        
        
        {
            visible: userPermissions.technics_create_update_delete,
            type: "buttons",
            buttons: [
                'edit',
                'delete'
            ],

            headerCellTemplate: (container, options) => {
                $('<div>')
                    .appendTo(container)
                    .dxButton({
                        text: "Добавить",
                        icon: "fas fa-plus",
                        onClick: (e) => {
                            options.component.addRow();
                            $('#mainDataGrid').dxDataGrid('instance').option("focusedRowKey", undefined);
                            $('#mainDataGrid').dxDataGrid('instance').option("focusedRowIndex", undefined);
                        }
                    })
            }
        }


    ];
</script>
