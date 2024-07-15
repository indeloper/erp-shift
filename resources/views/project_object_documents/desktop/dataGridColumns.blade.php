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
            calculateFilterExpression: function (filterValue, selectedFilterOperation) {
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
        {
            caption: "Документ",
            dataField: "document_name",
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
        {
            visible: false,
            caption: "Дата",
            dataField: "document_date",
            dataType: "date"
        },
        {
            type: "buttons",
            width: '15%',
            buttons: [
                {
                    hint: 'Копировать',
                    icon: 'copy',
                    onClick(e) {
                        copyDocument(e.row.key)
                    },
                    visible(e) {
                        // одной стройчкой через return не работает
                        if (e.row.data.status.project_object_documents_status_type.slug === 'document_archived_or_deleted')
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
                        if (e.row.data.status.project_object_documents_status_type.slug === 'document_archived_or_deleted')
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
                        if (e.row.data.status.project_object_documents_status_type.slug === 'document_archived_or_deleted')
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
