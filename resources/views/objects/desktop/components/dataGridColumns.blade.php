<script>
    const dataGridColumns = [

        {
            caption: "Идентификатор",
            dataField: "id",
            visible: false
        },
        {
            caption: "Кадастровый номер",
            dataField: "cadastral_number",
            visible: false
        },
        {
            caption: "Тип материального учета",
            dataField: "material_accounting_type",
            visible: false,
            lookup: {
                dataSource: materialAccountingTypesDataSource,
                valueExpr: "id",
                displayExpr: "name"
            }
        },

        {
            caption: "Ответственные ПТО",
            dataField: "responsibles_pto",
            visible: false,
            editorType: 'dxTagBox',
            editorOptions: {
                valueExpr: 'id',
                displayExpr: 'user_full_name',
                disabled: true,
                elementAttr: {
                    id: 'responsiblesPTOfield'
                },
                dataSource: [],
            },
        },

        {
            caption: "Ответственные РП",
            dataField: "responsibles_managers",
            visible: false,
            editorType: 'dxTagBox',
            editorOptions: {
                valueExpr: 'id',
                displayExpr: 'user_full_name',
                disabled: true,
                elementAttr: {
                    id: 'responsiblesManagersfield'
                },
                dataSource: [],
            },
        },

        {
            caption: "Ответственные прорабы",
            dataField: "responsibles_foremen",
            visible: false,
            editorType: 'dxTagBox',
            editorOptions: {
                valueExpr: 'id',
                displayExpr: 'user_full_name',
                disabled: true,
                elementAttr: {
                    id: 'responsiblesForemenfield'
                },
                dataSource: [],
            },
        },


        {
            caption: "Bitrix ID",
            dataField: "bitrixId",
            width: 75,
        },
        {
            caption: "ДО",
            dataField: "is_participates_in_documents_flow",
            width: 75,
            dataType: "boolean",
            allowFiltering: false,
            editorOptions: {
                enableThreeStateBehavior: false
            }
        },
        {
            caption: "ПР.Р",
            dataField: "is_participates_in_material_accounting",
            width: 75,
            dataType: "boolean",
            allowFiltering: false,
            editorOptions: {
                enableThreeStateBehavior: false,
            }
        },
        {
            caption: "Наименование",
            dataField: "name",
            // width: '150'
        },
        {
            caption: "Адрес",
            dataField: "address",
            // width: '150'
        },

        {
            caption: "Сокращенное наименование",
            dataField: "short_name",
            // width: '150'
        },
        // {
        //     caption: "Контрагенты",
        //     dataField: "",
        //     // width: '150'
        // },

        {
            type: "buttons",
            buttons: [
                'edit',
                {

                }
            ],

            headerCellTemplate: (container, options) => {
                $('<div>')
                    .appendTo(container)
                    .dxButton({
                        text: "Добавить",
                        icon: "fas fa-plus",
                        onClick: (e) => {
                            options.component.addRow();
                            $('#dataGridContainer').dxDataGrid('instance').option("focusedRowKey", undefined);
                            $('#dataGridContainer').dxDataGrid('instance').option("focusedRowIndex", undefined);
                        }
                    })
            }
        }


    ];
</script>