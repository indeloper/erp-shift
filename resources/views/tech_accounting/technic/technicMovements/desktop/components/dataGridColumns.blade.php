<script>
    const dataGridColumns = [
        {
            visible: false,
            dataField: "finish_result",
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
            dataField: "previous_responsible_id",
            caption: 'Предыдущий ответственный',
            lookup: {
                dataSource: additionalResources.technicResponsiblesAllTypes,
                valueExpr: "id",
                displayExpr: "user_full_name"
            },
        },

        {
            caption: 'Идентификатор',
            dataField: "id",
            width: 65,
            cellTemplate: (container, options) => {
                const status = additionalResources.technicMovementStatuses.find(el=>el.id===options.row.data.technic_movement_status_id).slug
                let markerColorClass 
                switch(status) {
                    case 'created':
                        markerColorClass = 'background-color-blue'
                        break
                    case 'cancelled':
                        markerColorClass = 'background-color-red'
                        break
                    case 'completed':
                        markerColorClass = 'background-color-green'
                        break
                    default:
                        markerColorClass = 'background-color-yellow'
                }
              
                $(`
                    <div style="display:flex; align-items: center">
                        <div class="round-color-marker ${markerColorClass}"></div>
                        <div>${options.row.data.id}</div>
                    </div>
                `).appendTo(container)
            },
        },     
        
        {
            dataField: "technic_movement_status_id",
            caption: 'Статус',
            sortIndex: 1, 
            sortOrder: "asc", 
            lookup: {
                dataSource: additionalResources.technicMovementStatuses,
                valueExpr: "id",
                displayExpr: "name"
            },
            cellTemplate: (container, options) => {
                const status = additionalResources.technicMovementStatuses.find(el=>el.id===options.row.data.technic_movement_status_id).slug
                // let textColorClass 
                // switch(status) {
                //     case 'created':
                //         textColorClass = 'text-color-blue'
                //         break
                //     case 'cancelled':
                //         textColorClass = 'text-color-red'
                //         break
                //     case 'completed':
                //         textColorClass = 'text-color-green'
                //         break
                //     default:
                //     textColorClass = 'text-color-yellow'
                // }
                $('<span>')
                    // .addClass(textColorClass)
                    .text(options.displayValue)
                    .appendTo(container)
            },
            width: 160
        },

        
        {
            dataField: "movement_start_datetime",
            caption: 'Дата транспортировки',
            dataType: "datetime",
            width: 140
        },
       
        {
            dataField: "order_start_date",
            caption: 'Период эксплуатации',
            dataType: "date",
            sortIndex: 2, 
            sortOrder: "asc", 
            cellTemplate: (container, options) => {
                $(`
                    <div style="">
                        <span>${options.row.data.order_end_date ? '': 'с '}</span>
                        <span>${new Date(options.row.data.order_start_date).toLocaleDateString()}</span>
                        <span>${options.row.data.order_end_date ? ' - ': ''}</span>
                        <span>${options.row.data.order_end_date ? new Date(options.row.data.order_end_date).toLocaleDateString() : ''}</span>
                    </div>
                `).appendTo(container)
            },
            width: 170
        },

        {
            dataField: "technic_id",
            caption: 'Техника',
            lookup: {
                dataSource: additionalResources.technicsList,
                valueExpr: "id",
                displayExpr: "name"
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
            caption: 'Объект отправления',
            lookup: {
                dataSource: additionalResources.projectObjects,
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
            cellTemplate: (container, options) => {
                const responsible = additionalResources.technicResponsiblesAllTypes.find(el=>el.id===options.row.data.responsible_id)?.user_full_name
                $(container).text(responsible ? responsible : 'Не назначен')
            },
            width: 110
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
        
        buttonsColumn
    ];
</script>
