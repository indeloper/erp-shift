<script>
    const dataGridColumns = [
        {
            caption: "Кадастровый номер",
            dataField: "cadastral_number",
            visible: false,
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
                searchEnabled: true
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
                searchEnabled: true
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
                searchEnabled: true
            },
        },
        {
            caption: "Идентификатор",
            dataField: "id",
            width: 75,
        },
        {
            caption: "Bitrix ID",
            dataField: "bitrix_id",
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
        },
        {
            caption: "Адрес",
            dataField: "address",
        },

        {
            caption: "Сокращенное наименование",
            dataField: "short_name",
        },
        {
            type: "buttons",
            buttons: [
                'edit',
                {}
            ],
            headerCellTemplate: (container, options) => {
                $('<div>')
                    .appendTo(container)
                    .dxButton({
                        text: "Добавить",
                        icon: "fas fa-plus",
                        onClick: (e) => {
                            options.component.addRow().done(() => {
                                options.component.cellValue(0, "material_accounting_type", 1);
                                options.component.cellValue(0, "is_participates_in_documents_flow", 0);
                                options.component.cellValue(0, "is_participates_in_material_accounting", 0);
                                options.component.option("focusedRowKey", undefined);
                                options.component.option("focusedRowIndex", undefined);
                            });
                        },
                        onInitialized(e) {
                            e.component.option('visible', permissions.objects_create)
                        }
                    })
            }
        }
    ];
</script>
