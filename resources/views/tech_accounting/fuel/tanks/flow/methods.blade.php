@include('tech_accounting.fuel.tanks.moduleCommonMethods')

<script>

    // Общие
    function getUrlWithId(url, id) {
        return url.replace("/setId", "/" + id)
    }

    function getGridHeight() {
        let content = document.getElementsByClassName('content')[0]
        return 0.82 * content.clientHeight;
    }

    function resetVars() {
        editingRowId = 0;
        uploadingFiles = [];
        newAttachments = [];
        deletedAttachments = [];
    }

    function resetStores() {
        entityInfoByID.store().clearRawDataCache()
        entityInfoByID._isLoaded = false
    }

    function customConfirmDialog(message) {
        return DevExpress.ui.dialog.custom({
            showTitle: false,
            messageHtml: message,
            buttons: [{
                text: "Да",
                onClick: () => true
            }, {
                text: "Нет",
                onClick: () => false
            }]
        })
    }

    function createFilterRowTagBoxFilterControlForLookupColumns(e) {
        e.editorName = `dxTagBox`;
        e.editorOptions.showSelectionControls = true;
        e.editorOptions.dataSource = e.lookup.dataSource;
        e.editorOptions.displayExpr = e.lookup.displayExpr;
        e.editorOptions.valueExpr = e.lookup.valueExpr;
        e.editorOptions.applyValueMode = `useButtons`;
        e.editorOptions.value = e.value || [];
        e.editorOptions.dataFieldName = e.dataField;
        e.editorOptions.onValueChanged = () => {
            function calculateFilterExpression() {
                let filterExpression = [];
                e.element.find(`.dx-datagrid-filter-row`).find(`.dx-tagbox`).each((index, item) => {
                    let tagBoxFilterExpression = [];
                    let tagBox = $(item).dxTagBox(`instance`);
                    tagBox.option(`value`).forEach(function (value) {
                        tagBoxFilterExpression.push([tagBox.option().dataFieldName, `=`, Number(value)]);
                        tagBoxFilterExpression.push(`or`);
                    });
                    tagBoxFilterExpression.pop();
                    if (tagBoxFilterExpression.length) {
                        filterExpression.push(tagBoxFilterExpression);
                        filterExpression.push(`and`);
                    }
                })
                filterExpression.pop();
                return filterExpression;
            }

            let calculatedFilterExpression = calculateFilterExpression();


            if (calculatedFilterExpression.length) {
                if (calculatedFilterExpression.length === 1)
                    e.component.filter(calculatedFilterExpression[0]);

                if (calculatedFilterExpression.length > 1)
                    e.component.filter(calculatedFilterExpression);
            } else {
                e.component.clearFilter(`dataSource`)
            }
        }
    }


    function setLoadedEntityInfo() {
        entityInfoByID.reload().done((data) => {
            entityInfo = entityInfoByID.store().__rawData;
            //
        })
    }

    function fixDataBeforeFormRepaint() {
        //
    }

    function operationsWithFormDataAfterFormRepaint(choosedDataGridRowIndex, someData) {
        //
    }

    function setPopupItemVariablesMobile(itemData) {
        editingRowId = itemData.id
    }

    // Конец Общие

    // function getDatesDaysDiff(date1, date2) {
    //     date1 = new Date(date1);
    //     date2 = new Date(date2);
    //     return Math.floor((date2 - date1) / (1000*60*60*24))
    // }

    function getChoosedItem(id) {
        let choosedItem = {};
        let dataGridItems = $('#mainDataGrid').dxDataGrid('instance').getDataSource().items();
        dataGridItems.forEach(el => {
            let targetItem = el.items.find(item => item.id === id)
            if (targetItem) {
                choosedItem = targetItem;
            }
        })
        return choosedItem;
    }

    const getAddFlowButtonDatasource = () => {
        if(userPermissions.adjust_fuel_tank_remains) {
            return additionalResources.fuelFlowTypes
        }
        return additionalResources.fuelFlowTypes.filter(el=>el.slug != 'adjustment')
    }

</script>
