<script>
    const renderStatusSelectBox = (statusAndOptionsWrapper) => {
        const statusSelectWrapper = $("<div>").attr("id", "statusSelect").appendTo(statusAndOptionsWrapper)
        const statusLoadIndicatorWrapper = $('<div>')
            .attr('id', 'statusLoadIndicatorWrapper')
            .css({
                'display': 'flex',
                'justify-content': 'center',
                'margin-bottom': '10px'
            })
            .appendTo(statusSelectWrapper)

        $('<div>')
            .dxLoadIndicator({
                height: 40,
                width: 40,
            }).appendTo(statusLoadIndicatorWrapper)

        documentStatusesByTypeStoreDataSource.reload().done(() => {
            statusLoadIndicatorWrapper.remove()

            // не работает во внешнем файле
            function isStatusSelectDisabled() {
                if (!editingRowId)
                    return true;

                let currentStatus = documentStatusesStore.__rawData.filter(el => el.id === editingRowStatusId)[0];
                return currentStatus?.project_object_documents_status_type.slug === 'work_with_document_is_finished' && !permissions.can_setup_final_project_object_document_status
            }

            $('<div id="documentMobileStatusId" style="margin-top:10px">').dxSelectBox({
                dataSource: documentStatusesByTypeStoreDataSource,
                valueExpr: "id",
                displayExpr: "name",
                value: editingRowStatusId,
                disabled: isStatusSelectDisabled(),
                itemTemplate(data) {
                    return $(`
                            <div style="display:flex; align-items:center">
                                <div class="round-color-marker" style="background-color: ${data?.project_object_documents_status_type?.style}; margin-right:15px; margin-left:5px" />
                                <div class="status-name">${data?.name}</div>
                            </div>
                        `);
                },

                fieldTemplate(data, container) {
                    const result = $(`
                            <div style="display:flex; align-items:center">
                                <div class="round-color-marker" style="background-color: ${data?.project_object_documents_status_type?.style}; margin-right:0; margin-left:10px" />
                                <div class="status-name"></div>
                            </div>
                        `);
                    result
                        .find('.status-name')
                        .dxTextBox({
                            value: data?.name,
                            readOnly: true,
                        });

                    container.append(result);
                },

                onValueChanged: function (e) {
                    resetStatusOptionsVars()
                    optionsByTypeAndStatusStore.clearRawDataCache();
                    editingRowNewStatusId = e.value;

                    renderStatusOptions();

                    let allStatuses = documentStatusesStore.__rawData;
                    let currentStatus = allStatuses.filter(el => el.id === editingRowNewStatusId)[0];
                    handleNewCommentAdded('Новый статус: ' + currentStatus.name.toLowerCase())
                }
            }).dxValidator({
                validationGroup: "documentValidationGroup",
                validationRules: [{
                    type: "required",
                    message: 'Укажите значение',
                }]
            }).appendTo(statusSelectWrapper)
        })
    }
</script>
