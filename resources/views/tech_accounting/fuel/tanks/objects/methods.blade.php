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
    }

    function resetStores() {
        entityInfoByID.store().clearRawDataCache()
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

    async function validateTankNumberUnique(value) {
        let response = await $.getJSON("{{route($routeNameFixedPart.'validateTankNumberUnique')}}",
            {
                id: editingRowId,
                value: value
            })
        return await response.result;
    }

    async function moveFuelTank(formData, movemingFuelTankForm) {

        return $.ajax({
            url: "{{route($routeNameFixedPart.'moveFuelTank')}}",
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                data: JSON.stringify(formData),
            },
            success: function (data, textStatus, jqXHR) {
                DevExpress.ui.notify("Данные успешно обновлены", "success", 1000)
                movemingFuelTankForm.hide()
                entitiesDataSource.reload()
            },
        })
    }

    async function confirmMovingFuelTank(fuelTankId, movingConfirmationFuelTankFormPopup) {

        return $.ajax({
            url: "{{route($routeNameFixedPart.'confirmMovingFuelTank')}}",
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                fuelTankId: JSON.stringify(fuelTankId),
            },
            success: function (data, textStatus, jqXHR) {
                DevExpress.ui.notify("Данные успешно обновлены", "success", 1000)
                movingConfirmationFuelTankFormPopup.hide()
                entitiesDataSource.reload()
            },
        })
    }

    function setReadonlyFormElemsProperties(isReadonly, dataGridId) {
        let dataGrid = $("#"+dataGridId).dxDataGrid("instance")
        dataGrid.option("columns").forEach((columnItem) => {    
            dataGrid.columnOption(columnItem.dataField, "allowEditing", !isReadonly)
        });
    }

</script>
