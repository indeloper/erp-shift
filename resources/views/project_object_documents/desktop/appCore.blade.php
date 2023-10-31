<script>
    $(()=>{
        // const dataGridInstance = $('#dataGridContainer').dxDataGrid({
        //     dataSource: dataSourceList,
        //     ...dataGridSettings,
        //     columns: dataGridColumns,
        // }).dxDataGrid('instance')

        $("#dataGridAnchor").dxForm({
            items: [
                {
                    itemType: "group",
                    caption: "Документооборот: Площадка ⇆ Офис",
                    cssClass: "datagrid-container",
                    items: [{
                        name: "dataGridContainer",
                        editorType: "dxDataGrid",
                        editorOptions: {
                            dataSource: dataSourceList,
                            ...dataGridSettings,
                            columns: dataGridColumns,
                            elementAttr: {
                                id: "dataGridContainer"
                            }
                        }
                    }]
                }
            ]
        })

        const groupCaption = $('.datagrid-container').find('.dx-form-group-with-caption');
            $('<div>').addClass('dx-form-group-caption-buttons').prependTo(groupCaption);
            groupCaption.find('span').addClass('dx-form-group-caption-span-with-buttons');
        const groupCaptionButtonsDiv = groupCaption.find('.dx-form-group-caption-buttons');
        groupCaptionButtonsDiv.css('display', 'flex')
        groupCaptionButtonsDiv
            .append('<div id="responsiblesFilterSelect" class="headerToolbarItem dxTagBoxItem">')
            .append('<div id="objectsFilterSelect" class="headerToolbarItem dxTagBoxItem">')
            .append('<div id="groupingAutoExpandAllTrue" class="headerToolbarItem">')
            .append('<div id="groupingAutoExpandAllFalse" class="headerToolbarItem">')
            .append('<div id="toolbarDropDownButton" class="headerToolbarItem">')

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
                        e.component.option('value', [+"{{Auth::user()->id}}"])
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
            },
            placeholder: 'Выбрать...',
        })

        $('#groupingAutoExpandAllTrue').dxButton({
            icon: 'expand',
            dropDownOptions: {width: 120},
            onClick() {
                $('#dataGridContainer').dxDataGrid('instance').option('grouping.autoExpandAll', true)
            }
        })

        $('#groupingAutoExpandAllFalse').dxButton({
            icon: 'collapse',
            dropDownOptions: {width: 120},
            onClick() {
                $('#dataGridContainer').dxDataGrid('instance').option('grouping.autoExpandAll', false)
            }
        })

        // бывают случаи, когда управление свойством disabled через option у downloadXlsButton зависает
        // сделал через перерисовку элемента при каждом обновлении данных

        addToolbarDropDownButton()

    })
</script>
