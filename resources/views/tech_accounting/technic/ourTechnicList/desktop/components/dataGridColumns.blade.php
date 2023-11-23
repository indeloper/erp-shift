<script>
    const dataGridColumns = [
        
        {
            dataField: "exploitation_start",
            caption: "Начало эксплуатации",
            dataType: "date",
            visible: false
        },

        {
            caption: "Марка (old)",
            dataField: "brand",
            visible: false,
            editorOptions: {
                readOnly:true
            }
        },
        {
            caption: "Модель(old)",
            dataField: "model",
            visible: false,
            editorOptions: {
                readOnly:true
            }
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
