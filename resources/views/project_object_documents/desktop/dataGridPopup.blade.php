<script>
    const dataGridPopup =
        {
            showTitle: true,
            title: "Информация о документе",
            hideOnOutsideClick: false,
            showCloseButton:true,
            maxWidth: '75%',
            // height: '85vh',
            animation: {
                show: {
                    type: 'pop',
                    duration: 300,
                    from: {
                        scale: 0.55
                    }
                },
                hide: {
                    type: 'pop',
                    duration: 300,
                    to: {
                        opacity: 0,
                        scale: 0.55
                    },
                    from: {
                        opacity: 1,
                        scale: 1
                    }
                }
            },
            onShowing(e){
                if(isArchivedOrDeletedDocuments())
                e.component.option('toolbarItems', [])

                getFormInstance()?.itemOption('dataGridEditFormMainGroup', 'visible', false);

                // Прогружаем за один запрос комментарии и файлы, и после этого перерисовываем форму (.repaint())
                // комментарии и файлы содержатся в projectObjectDocumentInfoByID
                projectObjectDocumentInfoByID.reload().done((data)=>{
                        getFormInstance()?.itemOption('dataGridEditFormMainGroup', 'visible', true);
                        getFormInstance()?.itemOption('dataGridEditFormLoadPanel', 'visible', false);
                        getFormInstance()?.repaint();

                        $('.dx-popup-content').css({'height': '', 'paddingBottom': 0 })
                        $('.dx-popup-normal').css({'height': '' })

                });
            },
            onShown(){
                // setTimeout(() => {
                    if(!projectObjectDocumentInfoByID.isLoaded())
                    getFormInstance()?.itemOption('dataGridEditFormLoadPanel', 'visible', true);
                // }, 300)
            },
            onHiding(){
                getFormInstance()?.itemOption('dataGridEditFormLoadPanel', 'visible', false);
            }

            // onShowing(e){
            //     createCustomToolbarItems()

            //     $('#deleteDocumentButton').dxButton({
            //         icon: "trash",
            //         hint: "Удалить",
            //         onClick: function(e) {
            //             deleteDocument(deletingRowId = editingRowId)
            //         }
            //     })

            //     $('#copyDocumentButton').dxButton({
            //         icon: "copy",
            //         hint: "Копировать",
            //         onClick: function(e) {
            //             copyDocument(copyRowId = editingRowId)
            //         }
            //     })
            // },
            // onShown(){
            //     if(editingRowId){
            //         if(document.querySelector('#deleteDocumentButton'))
            //         deleteDocumentButton.hidden = false
            //         if(document.querySelector('#copyDocumentButton'))
            //         copyDocumentButton.hidden = false
            //     } else {
            //         document.querySelector('[aria-label="trash"]').remove()
            //         document.querySelector('[aria-label="copy"]').remove()
            //     }
            // }
        }


    // Дополнительный Popup для статусов и опций

    function showOptionsPopup() {

        let coreDataGridInstance = getCoreDataGridInstance();

        setOptionPopupVariables(coreDataGridInstance)

        const statusOptionsFormPopup =  $('#statusOptionsForm').dxPopup({
            title: 'Статус и опции',
            width: 300,
            height: 300,
            visible: true,
            hideOnOutsideClick: true,
            showCloseButton: true,

            toolbarItems: [
                {
                    widget: 'dxButton',
                    toolbar: 'bottom',
                    location: 'after',
                    useSubmitBehavior: true,
                    options: {
                        text: 'OK',
                    },
                    onClick() {
                        if(editingRowNewStatusId) {
                            coreDataGridInstance.cellValue(coreDataGridInstance.getRowIndexByKey(editingRowId), "document_status_id", editingRowNewStatusId)
                            let allStatuses = documentStatusesStore.__rawData;
                            let currentStatus = allStatuses.filter(el => el.id === editingRowNewStatusId)[0];
                            handleNewCommentAdded('Новый статус: ' + currentStatus.name.toLowerCase())
                            // coreDataGridInstance.dxValidator('instance').reset()
                        }
                        editingRowTypeStatusOptions = editingRowTypeStatusOptions_tmp
                        statusOptionsFormPopup.hide()
                    }
                }
            ],

            onShown() {
                optionsByTypeAndStatusStore.clearRawDataCache();
                getDocumentOptionsByTypeAndStatus();
                renderStatusSelector();
            },

            contentTemplate: function(contentElement){
                return contentElement.append('<div id="documentStatusSelector"></div><div id="optionsList"></div>')
                // statusOptionsFormContentTemplate(contentElement)
            } ,

        }).dxPopup('instance')
    }

    function setOptionPopupVariables(coreDataGridInstance) {
        if(editingRowId) {
            editingRowTypeId = coreDataGridInstance.cellValue(coreDataGridInstance.getRowIndexByKey(editingRowId), "document_type_id")
            editingRowStatusId = coreDataGridInstance.cellValue(coreDataGridInstance.getRowIndexByKey(editingRowId), "document_status_id")
            editingRowStartOptions = JSON.parse(coreDataGridInstance.cellValue(coreDataGridInstance.getRowIndexByKey(editingRowId), "options"))
        }
    }

    function renderStatusSelector() {
        documentStatusesByTypeStore.clearRawDataCache();
        $("#documentStatusSelector").dxSelectBox({
                    // inputAttr: { 'id': 'documentStatusSelector' },
                    // dataSource: documentStatusesByTypeStore,
                    dataSource: documentStatusesByTypeStoreDataSource,
                    valueExpr: "id",
                    displayExpr: "name",
                    value: editingRowStatusId,

                    itemTemplate(data) {
                        return $(`
                            <div style="display:flex; align-items:center">
                                <div class="round-color-marker" style="background-color: ${data?.project_object_documents_status_type?.style}; margin-right:15px; margin-left:5px" />
                                <div class='status-name'>${data?.name}</div>
                            </div>
                        `);
                    },

                    fieldTemplate(data, container) {
                        const result = $(`
                            <div style="display:flex; align-items:center">
                                <div class="round-color-marker" style="background-color: ${data?.project_object_documents_status_type?.style}; margin-right:0; margin-left:10px" />
                                <div class='status-name'></div>
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

                        getDocumentOptionsByTypeAndStatus()

                        documentStatusesByTypeStore.load().done((statuses)=>{
                            let choosedStatus = statuses.filter(el=>el.id===editingRowNewStatusId)
                            documentStatusMainFormSelector.value = choosedStatus[0].name
                        })
                    }
                })

    }

    // function statusOptionsFormContentTemplate(contentElement){

    //     documentStatusesByTypeStore.clearRawDataCache();

    //     return contentElement.append(
    //             $("<div />").dxSelectBox({
    //                 // inputAttr: { 'id': 'documentStatusSelector' },
    //                 // dataSource: documentStatusesByTypeStore,
    //                 dataSource: documentStatusesByTypeStoreDataSource,
    //                 valueExpr: "id",
    //                 displayExpr: "name",
    //                 value: editingRowStatusId,

    //                 itemTemplate(data) {
    //                     return $(`
    //                         <div style="display:flex; align-items:center">
    //                             <div class="round-color-marker" style="background-color: ${data?.project_object_documents_status_type?.style}; margin-right:15px; margin-left:5px" />
    //                             <div class='status-name'>${data?.name}</div>
    //                         </div>
    //                     `);
    //                 },

    //                 fieldTemplate(data, container) {
    //                     const result = $(`
    //                         <div style="display:flex; align-items:center">
    //                             <div class="round-color-marker" style="background-color: ${data?.project_object_documents_status_type?.style}; margin-right:0; margin-left:10px" />
    //                             <div class='status-name'></div>
    //                         </div>
    //                     `);
    //                     result
    //                         .find('.status-name')
    //                         .dxTextBox({
    //                             value: data?.name,
    //                             readOnly: true,
    //                         });

    //                     container.append(result);
    //                 },

    //                 onValueChanged: function (e) {
    //                     resetStatusOptionsVars()
    //                     optionsByTypeAndStatusStore.clearRawDataCache();
    //                     editingRowNewStatusId = e.value;
    //                     getDocumentOptionsByTypeAndStatus()

    //                     documentStatusesByTypeStore.load().done((statuses)=>{
    //                         let choosedStatus = statuses.filter(el=>el.id==editingRowNewStatusId)
    //                         documentStatusMainFormSelector.value = choosedStatus[0].name
    //                     })
    //                 }
    //             }),

    //             $("<div />").attr("id", "optionsList")
    //     )
    // }

    function renderOptionsLoadIndicator() {

        try {
            $('#optionsList').dxList('dispose')
        } catch (err) {
            //
        }

        let optionsList = $('#optionsList')
        const optionsLoadIndicatorWrapper = $('<div>')
            .attr('id', 'optionsLoadIndicatorWrapper')
            .css({
                'display': 'flex',
                'justify-content': 'center',
                'margin': '10px 0'
            })
            .appendTo(optionsList)

        $('<div>')
            .dxLoadIndicator({
                height: 30,
                width: 30,
            }).appendTo(optionsLoadIndicatorWrapper)
    }


    function getDocumentOptionsByTypeAndStatus() {
        let dxPopupContentElems = document.querySelectorAll('.dx-popup-content')
        renderOptionsLoadIndicator()

        optionsByTypeAndStatusStore.load().done((options)=>{
            // if(optionsListLoadPanel)
            // optionsListLoadPanel.hide();

            if($('#optionsLoadIndicatorWrapper'))
            $('#optionsLoadIndicatorWrapper').remove()


            options = JSON.parse(options);

            getOptionsDxList(options);
        })

    }

    function getOptionsDxList(options) {
        $('#optionsList').dxList({
            dataSource: options,
            hoverStateEnabled: false,
            itemTemplate(data) {
                const result = $('<div />').addClass("status-option");

                if(data.type === 'checkbox') {
                    $('<div />').dxCheckBox({
                        enableThreeStateBehavior: false,
                        value: getStartOptionValue(data.id),
                        text: data.label,
                        hint: data.label,
                        onValueChanged(e){
                            editingRowTypeStatusOptions_tmp.push(
                                {
                                    id: data.id,
                                    type: data.type,
                                    value: e.value,
                                    comment: data.label
                                }
                            );
                        }
                    }).appendTo(result)
                }

                if(data.type === 'select') {
                    $('<div />').dxSelectBox({
                        dataSource: getOptionsSelectSource(data.source),
                        value: getStartOptionValue(data.id),
                        valueExpr: "id",
                        displayExpr: 'user_full_name',
                        label: data.label,
                        labelMode: "floating",
                        onValueChanged(e){
                            editingRowTypeStatusOptions_tmp.push(
                                {
                                    id: data.id,
                                    type: data.type,
                                    value: e.value,
                                    comment: data.label,
                                    source: data.source
                                }
                            );
                        }
                    }).appendTo(result)
                }

                if(data.type === 'text') {
                    $('<div />').dxTextBox({
                        label: data.label,
                        labelMode: "floating",
                        value: getStartOptionValue(data.id),
                        onValueChanged(e){
                            editingRowTypeStatusOptions_tmp.push(
                                {
                                    id: data.id,
                                    type: data.type,
                                    value: e.value,
                                    comment: data.label,
                                }
                            );
                        }
                    }).appendTo(result)
                }

                return result;
            }
        })
    }

    function getOptionsSelectSource(selectSourceName) {
        if(selectSourceName === 'responsible_managers_and_pto')
        return responsible_managers_and_pto
        if(selectSourceName === 'responsible_managers_and_foremen')
        return responsible_managers_and_foremen
    }

    function getStartOptionValue(optionId) {

        if(typeof(editingRowStartOptions) === null || typeof(editingRowStartOptions) === 'undefined' || editingRowStartOptions === null)
        return false

        if(typeof editingRowStartOptions[optionId] === 'undefined')
        return false

        return editingRowStartOptions[optionId].value;
    }

    function resetStatusOptionsVars() {
        editingRowNewStatusId = 0;
        editingRowTypeStatusOptions = [];
        editingRowTypeStatusOptions_tmp = [];
    }

</script>
