<script>
    $(()=>{
        const dataGridInstance = $('#dataGridContainer').dxDataGrid({
            ...dataGridSettings,
            columns: dataGridColumns,    
        }).dxDataGrid('instance')

        
        $('#responsiblesFilterSelect').dxTagBox({
            dataSource: responsibles_all,
            valueExpr: 'id',
            displayExpr: 'user_full_name',
            maxDisplayedTags: 1,
            searchEnabled: true,
            showSelectionControls: true,
            wrapItemText: true,
            showDropDownButton: true,
            onInitialized(e) {
                getPermissions().then((permissions)=>{
                    if(permissions.project_object_documents_default_filtering_by_responsible_user){
                        e.component.option('value', ["{{Auth::user()->id}}"])
                    }
                })
            },
            onSelectionChanged(e) {
                oldFilterVal = customFilter['projectResponsiblesFilter']
                customFilter['projectResponsiblesFilter'] = [];
                for (let i = 0; i < this._selectedItems.length; i++){
                    customFilter['projectResponsiblesFilter'].push(this._selectedItems[i].id)
                }

                dataSourceList.reload();
                // window.history.pushState("", "", window.location.origin + window.location.pathname + "?projectResponsiblesFilter=" + JSON.stringify(customFilter['projectResponsiblesFilter']));
            },
            placeholder: 'Выбрать...',
        })

        $('#objectsFilterSelect').dxTagBox({
            dataSource: projectObjectsStore,
            valueExpr: 'id',
            displayExpr: 'short_name',
            maxDisplayedTags: 0,
            searchEnabled: true,
            showSelectionControls: true,
            wrapItemText: true,
            showDropDownButton: true,
            onSelectionChanged(e) {
                oldFilterVal = customFilter['projectResponsiblesFilter']
                customFilter['projectObjectsFilter'] = [];
                for (let i = 0; i < this._selectedItems.length; i++){
                    projectObjectsFilter.push(this._selectedItems[i].id)
                    customFilter['projectObjectsFilter'].push(this._selectedItems[i].id)
                }

                dataSourceList.reload();
                // window.history.pushState("", "", window.location.origin + window.location.pathname + "?projectObjectsFilter=" + JSON.stringify(customFilter['projectObjectsFilter']));
            },
            placeholder: 'Выбрать...',
        })

        $('#groupingAutoExpandAllTrue').dxButton({
            icon: 'expand',
            dropDownOptions: {width: 120},
            onClick() {
                dataGridInstance.option('grouping.autoExpandAll', true)
            }
        })

        $('#groupingAutoExpandAllFalse').dxButton({
            icon: 'collapse',
            dropDownOptions: {width: 120},
            onClick() {
                dataGridInstance.option('grouping.autoExpandAll', false)
            }
        })

        $('#dropDownButton').dxDropDownButton({
            icon: 'overflow',
            dropDownOptions: {width: 120},
            items: [
                'Скачать XLS',
                {
                    text: 'Открыть архив',
                    disabled: true
                }
            ],
            onItemClick(e) {
                if(e.itemData === 'Скачать XLS')
                    downloadXls();

                if(e.itemData === 'Открыть архив')
                    showArchive();
            }
        })

        // $('#downloadButton').dxButton({
        //     text: "Скачать",
        //     icon: "fa fa-download",
        //     onClick: (e) => {
        //         let result = downloadXls();
        //     }
        // })


        // $('#addRowButton').dxButton({
        //     text: "Добавить",
        //     icon: "fas fa-plus",
        //     onClick: (e) => {
        //         projectObjectCommentsDataSource.reload();
        //         projectObjectAttachmentsDataSource.reload();
        //         dataGridInstance.addRow();
        //     }
        // })


    })
</script>
