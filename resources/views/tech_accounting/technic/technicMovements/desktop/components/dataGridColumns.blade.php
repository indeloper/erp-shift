<script>
    const dataGridColumns = [
        {
            visible: false,
            dataField: "finish_result",
        },
        {
            visible: false,
            dataField: "id",
        },     
        {
            dataField: "technic_movement_status_id",
            caption: 'Статус',
            lookup: {
                dataSource: additionalResources.technicMovementStatuses,
                valueExpr: "id",
                displayExpr: "name"
            },
        },
        {
            visible: false,
            dataField: "technic_category_id",
            caption: 'Категория техники',
            lookup: {
                dataSource: additionalResources.technicCategories,
                valueExpr: "id",
                displayExpr: "name"
            },
        },
        {
            visible: false,
            dataField: "technic_id",
            caption: 'Техника',
            lookup: {
                dataSource: additionalResources.technicsList,
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
                    store: additionalResources.technicCarriers
                },
                valueExpr: "id",
                displayExpr: "short_name"
            },
        },
        {
            dataField: "responsible_id",
            caption: 'Ответственный',
            lookup: {
                dataSource: additionalResources.technicResponsiblesAllTypes,
                valueExpr: "id",
                displayExpr: "user_full_name"
            },
        },
        {
            visible: false,
            dataField: "previous_responsible_id",
            caption: 'Предыдущий ответственный',
            lookup: {
                dataSource: additionalResources.technicResponsiblesAllTypes,
                valueExpr: "id",
                displayExpr: "user_full_name"
            },
        },

        {
            dataField: "object_id",
            caption: 'Объект назначения',
            lookup: {
                dataSource: additionalResources.projectObjects,
                valueExpr: "id",
                displayExpr: "short_name"
            },
        },

        {
            dataField: "previous_object_id",
            caption: 'Объект отправки',
            lookup: {
                dataSource: additionalResources.projectObjects,
                valueExpr: "id",
                displayExpr: "short_name"
            },
        },
        
        buttonsColumn
    ];
</script>
