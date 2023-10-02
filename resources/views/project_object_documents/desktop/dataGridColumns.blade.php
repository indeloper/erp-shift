<script>
    const dataGridColumns = [
        {
            visible: false,
            caption: "фейковое поле для активации сабмита формы",
            allowFiltering: false,
            dataField: "fake_submit_dataField",
        },
        {
            visible: false,
            caption: "поле формы новый комментарий",
            allowFiltering: false,
            dataField: "new_comment",
        },
        {
            visible: false,
            caption: "options с начальными параметрами",
            allowFiltering: false,
            dataField: "options",
        },
        {
            caption: "Идентификатор",
            dataField: "id",
            tableName: "project_object_documents",
            width: 75,
            cellTemplate: (container, options) => {
                $(`
                    <div style="display:flex; align-items: center">
                        <div class="round-color-marker" style="
                            background-color: ${options.row.data.status.project_object_documents_status_type.style};
                        "></div>
                        <div>${options.value}</div>
                    </div>
                `).appendTo(container)
            },
            calculateFilterExpression: function(filterValue, selectedFilterOperation) {
                let fullDataFieldName = `${this.tableName}.${this.dataField}`;
                if (selectedFilterOperation === "between" && $.isArray(filterValue)) {
                    return [
                        [fullDataFieldName, ">", filterValue[0]],
                        "and", [fullDataFieldName, "<", filterValue[1]]
                    ]
                }
                return [fullDataFieldName, selectedFilterOperation, filterValue];
            }
        },
        {
            dataField: "project_object_id",
            caption: "Объект",
            lookup: {
                dataSource: projectObjectsStore,
                valueExpr: "id",
                displayExpr: (e) => {
                    return e.short_name ? e.short_name : e.object_name
                }
            },
            groupIndex: 0,
            groupCellTemplate: (container, options) => {
                let groupRow = $(`<div style="display:flex; justify-content: space-between;">
                                            <div>${options.column.caption}: ${options.displayValue}</div>
                                            <div class="summary-circles-container" style="display: flex; margin-right: 20px"></div>
                                          </div>`)
                    .appendTo(container);

                let summaryItems = options.summaryItems.find(item => item.name === "documentsCountByStatusType").value;

                if (summaryItems) {
                    summaryItems.forEach((summaryItem) => {
                        groupRow.find(".summary-circles-container").append(
                            `<div style="background-color: ${summaryItem.style}; margin:2px; border: 1px solid #cfcfcf; font-size: 11px; height:20px; width: 20px; border-radius: 50%; display:flex; justify-content: center; align-items: center;">
                                <div style="color:white; font-weight: bolder; padding-top: 2px;">${summaryItem.summaryValue}</div>
                            </div>`
                        )
                    })
                }
            }
        },
        {
            visible: true,
            dataField: "document_type_id",
            caption: "Тип",
            lookup: {
                dataSource: documentTypesStore,
                valueExpr: "id",
                displayExpr: "name"
            },
            calculateSortValue(rowData) {
                return rowData.sortOrder
            },
            sortOrder: 'asc',
            width: '25%'
        },

        // {
        //     caption: "Тип",
        //     dataField: "type.name",
        //     width: '25%',
        //     // allowSorting: false,
        //     validationRules: [{
        //         type: 'required',
        //         message: 'Укажите значение',
        //     }],
        //     headerFilter: {
        //         allowSearch: true,
        //         // Работает устаревшая версия - allowSearch
        //         // search: {
        //         //     enabled: true,
        //         // },
        //         dataSource: [
        //             {text: 'РД', value: 1},
        //             {text: 'Акт с площадки', value: 2},
        //             {text: 'Журнал', value: 3},
        //             {text: 'ППР', value: 4},
        //             {text: 'ИД', value: 5},
        //             {text: 'Выполнение', value: 6},
        //             {text: 'Прочее', value: 7},
        //         ]
        //     },
        // },

        {
            caption: "Документ",
            dataField: "document_name",
            // allowFiltering: false,
            // allowSorting: false,
            // hidingPriority: 1,
            width: '25%'
        },

        {
            visible: true,
            dataField: "document_status_id",
            caption: "Статус",
            cellTemplate: (container, options) => {
                $('<span>')
                    .text(options.displayValue)
                    .css('color', options.row.data.status.project_object_documents_status_type.style)
                    .appendTo(container)
            },
            lookup: {
                dataSource: documentStatusesStore,
                valueExpr: "id",
                displayExpr: "name"
            },
            width: '25%'

        },

        // {
        //     caption: "Статус",
        //     dataField: "status.name",
        //     cellTemplate: (container, options) => {
        //         $('<span>')
        //             .text(options.value)
        //             .css('color', options.row.data.status.style)
        //             .appendTo(container)
        //     },
        //     validationRules: [{
        //         type: 'required',
        //         message: 'Укажите значение',
        //     }],
        //     headerFilter: {
        //         allowSearch: true,
        //         // Работает устаревшая версия - allowSearch
        //         // search: {
        //         //     enabled: true,
        //         // },
        //         dataSource: [
        //             {text: 'Не оформлен', value: 1},
        //             {text: 'Не получен', value: 2},
        //             {text: 'В работе', value: 3},
        //             {text: 'На площадке', value: 4},
        //             {text: 'Ведется, на площадке', value: 5},
        //             {text: 'Подписан, на площадке', value: 6},
        //             {text: 'Оформлен и готов к передаче', value: 7},
        //             {text: 'Передан заказчику', value: 8},
        //             {text: 'Передан в офис', value: 9},
        //             {text: 'Получен офисом', value: 10},
        //         ],
        //     },
        //     // filterValues: [1,2,3,4,5] ,
        //     width: '25%'
        // },
        {
            visible: false,
            caption: "Дата",
            dataField: "document_date",
            dataType: "date"
        },

        {
            type: "buttons",
            // hidingPriority: 10,
            width: '15%',
            buttons: [
                // 'edit',
                // 'delete',

                {
                    hint: 'Копировать',
                    icon: 'copy',
                    onClick(e) {
                        copyDocument(e.row.key)
                    },
                    visible(e) {
                        // одной стройчкой через return не работает
                        if(e.row.data.status.project_object_documents_status_type.slug === 'document_archived_or_deleted')
                        return false
                        return true;
                    }
                },
                {
                    hint: 'Редактировать',
                    icon: 'edit',
                    onClick(e) {
                        e.component.editRow(e.row.rowIndex)
                    },
                    visible(e) {
                        // одной стройчкой через return не работает
                        if(e.row.data.status.project_object_documents_status_type.slug === 'document_archived_or_deleted')
                        return false
                        return true;
                    }
                },
                {
                    hint: 'Удалить',
                    icon: 'trash',
                    onClick(e) {
                        deleteDocument(e.row.key)
                    },
                    visible(e) {
                        if(e.row.data.status.project_object_documents_status_type.slug === 'document_archived_or_deleted')
                        return false
                        return !e.row.data.deleted_at;
                    }
                },
                {
                    hint: 'Восстановить',
                    icon: 'undo',
                    onClick(e) {
                        restoreDocument(e.row.key)
                    },
                    visible(e) {
                        return e.row.data.deleted_at;
                    }
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
                        }
                    })
            }
        }

    ];
</script>
