<script>
    const dataGridColumns = [
        {
            visible: false,
            dataField: "newAttachments",
        },
        {
            visible: false,
            dataField: "id",
        },     
        {
            dataField: "technic_movement_status_id",
            caption: 'Статус',
            lookup: {
                dataSource: technicMovementStatusesStore,
                valueExpr: "id",
                displayExpr: "name"
            },
        },
        {
            visible: false,
            dataField: "technic_category_id",
            caption: 'Категория техники',
            lookup: {
                dataSource: technicCategoriesStore,
                valueExpr: "id",
                displayExpr: "name"
            },
        },
        {
            visible: false,
            dataField: "technic_id",
            caption: 'Техника',
            lookup: {
                dataSource: technicsListStore,
                valueExpr: "id",
                displayExpr: "name"
            },
        },
        {
            dataField: "order_start_date",
            caption: 'Начало',
            dataType: "date",
        },
        {
            dataField: "order_end_date",
            caption: 'Окончание',
            dataType: "date",
        },
        {
            visible: false,
            dataField: "order_comment",
            caption: 'Комментарий',
        },
        {
            visible: false,
            dataField: "movement_start_datetime",
            caption: 'Начало перевозки',
        },
        {
            dataField: "contractor_id",
            caption: 'Перевозчик',
            lookup: {
                dataSource: {
                    store: technicCarriersStore
                },
                valueExpr: "id",
                displayExpr: "short_name"
            },
        },
        {
            dataField: "responsible_id",
            caption: 'Ответственный',
            lookup: {
                dataSource: technicResponsiblesAllTypesStore,
                valueExpr: "id",
                displayExpr: "user_full_name"
            },
        },
        {
            visible: false,
            dataField: "previous_responsible_id",
            caption: 'Предыдущий ответственный',
            lookup: {
                dataSource: technicResponsiblesAllTypesStore,
                valueExpr: "id",
                displayExpr: "user_full_name"
            },
        },

        {
            dataField: "object_id",
            caption: 'Объект назначения',
            lookup: {
                dataSource: projectObjectsStore,
                valueExpr: "id",
                displayExpr: "short_name"
            },
        },

        {
            dataField: "previous_object_id",
            caption: 'Объект отправки',
            lookup: {
                dataSource: projectObjectsStore,
                valueExpr: "id",
                displayExpr: "short_name"
            },
        },
        
        buttonsColumn
    ];
</script>
