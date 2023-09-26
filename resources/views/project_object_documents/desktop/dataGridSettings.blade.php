<script>
    const dataGridSettings = {
        height: getGridHeight(),


        // remoteOperations
        // изменяли настройки, были вопросы в связи с группировкой по объектам и кастомным rowFilter
        remoteOperations: {
            filtering: true,
            groupPaging: false
        },

        grouping: {
            autoExpandAll: false
        },
        groupPanel: {
            visible: false
        },

        focusedRowEnabled: false,
        hoverStateEnabled: true,
        columnAutoWidth: true,
        showBorders: true,
        showColumnLines: true,
        // allowColumnResizing: true,
        columnMinWidth: 50,
        columnResizingMode: 'nextColumn',
        syncLookupFilterValues: false,
        columnHidingEnabled: true,
        // wordWrapEnabled: true,
        showRowLines: true,


        filterRow: {
            visible: true,
            applyFilter: "auto"
        },
        headerFilter: {
            visible: false,
        },


        filterPanel: {
            visible: true,
            customizeText: (e) => {
                filterText = e.text;
            }
        },

        paging: {
            enabled: false
        },
        scrolling: {
            mode: 'virtual',
        },
        editing: {
            allowUpdating: true,
            mode: "popup",
            popup: dataGridPopup,
            allowAdding: true,
            allowDeleting: true,
            selectTextOnEditStart: false,
            form: dataGridEditForm
        },

        summary: {
            groupItems: [
                {
                    name: "documentsCountByStatusType",
                    summaryType: "custom",
                }
            ],
            calculateCustomSummary: (options) => {
                if (options.name === 'documentsCountByStatusType') {
                    if (options.summaryProcess === 'start') {
                        options.totalValue = [];
                    }

                    if (options.summaryProcess === 'calculate') {
                        let statusTypeSlug = options.value.status.project_object_documents_status_type.slug;

                        let valueItem = options.totalValue.find(item => item.name === statusTypeSlug)

                        if (valueItem) {
                            valueItem.summaryValue += 1
                        } else {
                            options.totalValue.push({
                                name: statusTypeSlug,
                                style: options.value.status.project_object_documents_status_type.style,
                                sortOrder: options.value.status.project_object_documents_status_type.sortOrder,
                                summaryValue: 1
                            })
                        }
                    }

                    if (options.summaryProcess === 'finalize') {
                        options.totalValue = options.totalValue.sort((a, b) => a.sortOrder - b.sortOrder);
                    }
                }
            },
        },

        onContentReady(e) {
            // downloadXlsButton (DropDownButton) бывают случаи, когда управление свойством disabled через option зависает
            // сделал через перерисовку элемента при каждом обновлении данных
            $('#toolbarDropDownButton').dxDropDownButton('dispose')
            if(e.component.getDataSource().items().length)
                addToolbarDropDownButton(isDownloadXlsDisabled = false)
            else
                addToolbarDropDownButton(isDownloadXlsDisabled = true)
        },


        onRowDblClick: function(e) {
            if (e.rowType === "data" && DevExpress.devices.current().deviceType === 'desktop') {
                e.component.editRow(e.rowIndex);
            }
        },
        onEditingStart(e) {
            editingRowId = e.key;
            //getFormInstance()?.itemOption('document_type_id', 'disabled', true);

            // getFormInstance()?.itemOption('dataGridEditFormMainGroup', 'visible', false);

            // // Прогружаем за один запрос комментарии и файлы, и после этого перерисовываем форму (.repaint())
            // // комментарии и файлы содержатся в projectObjectDocumentInfoByID
            // projectObjectDocumentInfoByID.reload().done((data)=>{
            //         getFormInstance()?.itemOption('dataGridEditFormMainGroup', 'visible', true);
            //         getFormInstance()?.itemOption('dataGridEditFormLoadPanel', 'visible', false);
            //         getFormInstance()?.repaint();
            // });
        },

        onEditorPreparing: (e) => {

            if (e.dataField === `document_type_id` && e.parentType === `dataRow`)
                e.editorOptions.readOnly = Boolean(editingRowId);

            if (e.parentType === `filterRow` && e.lookup)
                createFilterRowTagBoxFilterControlForLookupColumns(e)
        },

        onSaving(e) {
            if (e.changes.length === 0 && !newAttachments.length && !deletedAttachments.length && !editingRowTypeStatusOptions.length && !newCommentsArr.length)
                return;

            if (e.changes.length === 0 || !e.changes[0].data) {
                e.changes[0] = {
                    'data': {}
                };
                if (newAttachments.length || deletedAttachments.length || editingRowTypeStatusOptions.length || newCommentsArr.length) {
                    e.changes[0].key = editingRowId;
                    e.changes[0].type = 'update'
                }
            }

            e.changes[0].data.newAttachments = newAttachments
            e.changes[0].data.deletedAttachments = deletedAttachments
            e.changes[0].data.typeStatusOptions = editingRowTypeStatusOptions
            e.changes[0].data.newCommentsArr = newCommentsArr
            if (editingRowNewStatusId)
                e.changes[0].data.document_status_id = editingRowNewStatusId
        },

        onSaved() {
            resetVars();
            resetStores();
        },

        onEditCanceling(e) {

            if (!skipStoppingEditingRow && e.changes.length) {

                e.cancel = true
                skipStoppingEditingRow = 0

                customConfirmDialog("Вы уверены, что отменить изменения?").show().then((dialogResult) => {
                    if (dialogResult) {
                        resetVars();
                        resetStores();
                        skipStoppingEditingRow = 1;
                        e.component.cancelEditData();
                    }
                })
            } else {
                resetVars();
                resetStores();
            }

        },

        onCellPrepared: function(e) {
            // if (e.rowType === "data" && e.column.dataField === "status.name") {
            //     e.cellElement.css("color", e.data.status.style);
            // }
        },

        toolbar: {
            visible: false,
            items: [{
                // name: 'addRowButton',
                // showText: 'always',
            }]
        },
    }
</script>
