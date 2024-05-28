import {
    transformationStages,
    rowTypes,
    currentTransformationStage,
    transformationData,
    insertTransformationRow,
    getValidationUid,
    validateStages,
    updateValidationData, validateMaterialList
} from "./transformationStorage"
import {materialsStandardsAddingForm} from "./materialAddingForm/materialsStandardsAddingForm"
import {createPopupContainer} from "./materialAddingForm/popup"
import {getMaterialTypesData, materialStandardsListDataSource} from "./dataService";


(async function () {

    const materialsData = await getMaterialTypesData();

    const materialsAddingForm = await materialsStandardsAddingForm();

    let projectObjectId = document.querySelector('#projectObjectId').value;

    let currentTransformationType = "";

    let suspendSourceObjectLookupValueChanged = false;
    //<editor-fold desc="JS: DataSources">

    let availableMaterialsStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function (loadOptions) {
            return $.getJSON(materialsData.materials_actual_list_route,
                {project_object: projectObjectId});
        },
    });

    let availableMaterialsDataSource = new DevExpress.data.DataSource({
        key: "id",
        store: availableMaterialsStore
    });

    let projectObjectsListWhichParticipatesInMaterialAccountingDataSource = new DevExpress.data.DataSource({
        reshapeOnPush: true,
        store: new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "raw",
            load: function (loadOptions) {
                return $.getJSON(materialsData.material_accounting_list_route,
                    {data: JSON.stringify(loadOptions)});
            }
        })
    });

    let materialTransformationTypesDataSource = new DevExpress.data.DataSource({
        reshapeOnPush: true,
        store: new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "raw",
            load: function (loadOptions) {
                return $.getJSON(materialsData.material_transform_types_lookup_list_route,
                    {data: JSON.stringify(loadOptions)});
            }
        }),
        onChanged: function () {
            repaintGUI();
        }
    });

    let usersWithMaterialListAccessStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function (loadOptions) {
            return $.getJSON(materialsData.users_with_material_list_access_list_route,
                {data: JSON.stringify(loadOptions)});
        },
    });
    //</editor-fold>

    //<editor-fold desc="JS: Edit form configuration">
    let operationForm = $("#formContainer").dxForm({
        formData: [],
        colCount: 2,
        items: [
            {
                itemType: "group",
                colCount: 3,
                caption: "Преобразование",
                items: [
                    {
                        name: "projectObjectSelectBox",
                        colSpan: 3,
                        dataField: "project_object_id",
                        label: {text: "Объект"},
                        editorType: "dxSelectBox",
                        editorOptions: {
                            dataSource: projectObjectsListWhichParticipatesInMaterialAccountingDataSource,
                            displayExpr: "short_name",
                            valueExpr: "id",
                            searchEnabled: true,
                            value: projectObjectId,
                            onValueChanged: function (e) {
                                function updateComponentsDataSources(projectObjectIdValue) {
                                    projectObjectId = projectObjectIdValue;
                                    availableMaterialsDataSource.reload();
                                }

                                if (suspendSourceObjectLookupValueChanged) {
                                    suspendSourceObjectLookupValueChanged = false;
                                    return;
                                }

                                let oldValue = e.previousValue;
                                let currentValue = e.value;

                                if (materialsToTransform.length > 0 && e.previousValue !== null) {
                                    let confirmDialog = DevExpress.ui.dialog.confirm(
                                        'При смене объекта будут удалены введенные данные по материалам операции.<br>Продолжить?',
                                        'Смена объекта'
                                    );
                                    confirmDialog.done(function (dialogResult) {
                                        if (dialogResult) {
                                            /*updateComponentsDataSources(currentValue);
                                            currentTransformationStage = "fillingMaterialsToTransform";
                                            materialsToTransform = [];
                                            materialsAfterTransform = [];
                                            materialsRemains = [];
                                            repaintMaterialsToTransformLayer();
                                            repaintMaterialsAfterTransformLayer();
                                            repaintMaterialRemains();*/
                                        } else {
                                            suspendSourceObjectLookupValueChanged = true;
                                            e.component.option('value', oldValue);
                                        }
                                    });
                                } else {
                                    updateComponentsDataSources(currentValue);
                                }
                            }
                        },
                        validationRules: [{type: "required", message: 'Поле "Объект" обязательно для заполнения'}]
                    },
                    {
                        name: "operationDateDateBox",
                        dataField: "operation_date",
                        colSpan: 1,
                        label: {text: "Дата преобразования"},
                        editorType: "dxDateBox",
                        editorOptions: {
                            value: Date.now(),
                            max: Date.now(),
                            min: getMinDate(),
                            readOnly: false
                        },
                        validationRules: [{
                            type: "required",
                            message: 'Поле "Дата преобразования" обязательно для заполнения'
                        }]
                    },
                    {
                        name: "destinationResponsibleUserSelectBox",
                        colSpan: 2,
                        dataField: "responsible_user_id",
                        label: {text: "Ответственный"},
                        editorType: "dxSelectBox",
                        editorOptions: {
                            dataSource: {store: usersWithMaterialListAccessStore},
                            displayExpr: "full_name",
                            valueExpr: "id",
                            searchEnabled: true,
                            value: materialsData.user_id
                        },
                        validationRules: [{
                            type: "required",
                            message: 'Поле "Ответственный" обязательно для заполнения'
                        }]
                    }
                ]
            },
            {
                itemType: "group",
                caption: "Комментарий",
                items: [{
                    name: "newCommentTextArea",
                    dataField: "new_comment",
                    label: {visible: false},
                    editorType: "dxTextArea",
                    editorOptions: {height: 160},
                    validationRules: [{type: "required", message: 'Поле "Комментарий" обязательно для заполнения'}]
                }]
            },
            {
                itemType: "group",
                caption: getCaption(),
                colSpan: 2,
                items: [
                    {
                        template: function (data, itemElement) {
                            itemElement.append(`
                                <div id="selectTransformationTypeLabel"><h5>Выберите тип преобразования:</h5></div>
                                <div id="transformationTypeSelector" class="transformation-type-selector"></div>
                                <div id="transformationContent" class="transformation-content" style="display: none;">
                                    <div id="transformationGrid"></div>
                                </div>
                            `);

                            $(".transformation-type-selector").on('click', function () {
                                updateCaption('Преобразование');
                                $("#transformationTypeSelector").hide();
                                $("#selectTransformationTypeLabel").hide();
                                $("#transformationContent").show();

                                $("#transformationGrid").dxDataGrid({
                                    dataSource: transformationData,
                                    focusedRowEnabled: false,
                                    hoverStateEnabled: true,
                                    columnAutoWidth: false,
                                    showBorders: true,
                                    showColumnLines: true,
                                    paging: {enabled: false},
                                    editing: {
                                        allowUpdating: true,
                                        mode: "cell",
                                        selectTextOnEditStart: false,
                                        startEditAction: "click",
                                        useIcons: true
                                    },
                                    dataRowTemplate(container, item) {
                                        let markup = getRowMarkup(item.rowIndex, item.data);
                                        container.append(markup);
                                    },
                                    columns: [
                                        {dataField: "sortIndex", dataType: "integer", sortIndex: 0, visible: false},
                                        {type: "buttons", width: 130, allowSorting: false},
                                        {
                                            dataField: "standard_name",
                                            dataType: "string",
                                            allowEditing: false,
                                            width: "30%",
                                            caption: "Наименование",
                                            allowSorting: false
                                        },
                                        {
                                            dataField: "quantity",
                                            dataType: "number",
                                            caption: "Количество",
                                            allowSorting: false,
                                            editorOptions: {min: 0},
                                            showSpinButtons: false
                                        },
                                        {
                                            dataField: "amount",
                                            dataType: "number",
                                            caption: "Количество (шт)",
                                            allowSorting: false,
                                            editorOptions: {min: 0, format: "#"}
                                        },
                                        {
                                            dataField: "computed_weight",
                                            dataType: "number",
                                            allowEditing: false,
                                            allowSorting: false,
                                            caption: "Вес"
                                        }
                                    ]
                                });
                            });
                        }
                    }
                ]
            }
        ],
        onContentReady: () => {
            materialTransformationTypesDataSource.load();
        }
    }).dxForm("instance");

    //</editor-fold>
    function getCaption(add = null) {
        let res = 'Материалы';
        res += add ? ' 🢂 ' + add : '';
        return res;
    }

    // Функция для обновления заголовка
    function updateCaption(newCaption) {
        operationForm.option("items[2].caption", getCaption(newCaption));
    }

    function repaintGUI() {
        switch (currentTransformationStage) {
            case transformationStages.transformationTypesSelection:
                repaintTransformationTypeSelectionLayer();
                break;
        }
    }

    function repaintTransformationTypeSelectionLayer() {
        let transformTypeLayer = $('.transformation-type-selector');
        let transformTypes = materialTransformationTypesDataSource.items();

        transformTypes.forEach(element => {
            let transformationTypeLayer = $(`<div class="transformation-type-item" transformation-type-name="${element.value}" transformation-type-codename="${element.codename}"/>`).append(
                $(`<div class="transformation-type-text">${element.value}</div>`)
            )

            transformationTypeLayer.click(() => {
                let data = {
                    name: `Шаг 1: Добавьте материалы для преобразования «${element.value}»`,
                    rowType: rowTypes.rowHeader,
                    sortIndex: 1
                }
                insertTransformationRow(data, transformationStages.fillingMaterialsToTransform);

                currentTransformationType = element.codename;
            })

            transformTypeLayer.append(transformationTypeLayer)
        })
    }

    function getRowMarkup(rowIndex, data) {
        let markup;

        let row = $(`<tr>`);
        switch (data.rowType) {
            case rowTypes.rowHeader:
                markup = $(`<td colspan="5" class="transformation-header">`);
                let caption = $(`<div class="transformation-header-caption">${data.name}</div>`);
                markup.append(caption);

                let appendMaterialButton;
                let nextStageButton;

                switch (currentTransformationStage) {
                    case transformationStages.fillingMaterialsToTransform:
                        appendMaterialButton = $(`<div class="transformation-header-button">`).dxButton({
                            text: `Добавить материал`,
                            type: `normal`,
                            onClick: () => {
                                showMaterialsAddingForm();
                            }
                        });

                        nextStageButton = $(`<div class="transformation-header-button">`).dxButton({
                            text: `Далее`,
                            type: `normal`,
                            onClick: () => {
                                let data = {
                                    name: `Шаг 2: Добавьте материалы после преобразования`,
                                    rowType: rowTypes.rowHeader,
                                    sortIndex: 4
                                }
                                insertTransformationRow(data, transformationStages.fillingMaterialsAfterTransform);
                            }
                        });

                        markup.append(nextStageButton);
                        markup.append(appendMaterialButton);

                        break;
                    case transformationStages.fillingMaterialsAfterTransform:
                        appendMaterialButton = $(`<div class="transformation-header-button">`).dxButton({
                            text: `Добавить материал`,
                            type: `normal`,
                            onClick: () => {
                                showMaterialsAddingForm();
                            }
                        });

                        nextStageButton = $(`<div class="transformation-header-button">`).dxButton({
                            text: `Далее`,
                            type: `normal`,
                            onClick: () => {
                                let data = {
                                    name: `Шаг 3: Укажите остатки матералов`,
                                    rowType: rowTypes.rowHeader,
                                    sortIndex: 7
                                }
                                insertTransformationRow(data, transformationStages.fillingMaterialsRemains);
                                insertMaterialsRemains();

                                data = {
                                    name: `Шаг 4: Укажите технологические потери (из-за резки или торцовки материала)`,
                                    rowType: rowTypes.rowHeader,
                                    sortIndex: 10
                                }
                                insertTransformationRow(data, transformationStages.fillingMaterialsTechnologicalLosses);
                                insertMaterialsTechnologicalLosses()
                            }
                        });

                        markup.append(nextStageButton);
                        markup.append(appendMaterialButton);

                        break;
                }
                row.append(markup);
                break;

            case rowTypes.rowData:
                row.addClass("dx-row")
                    .addClass("dx-data-row")
                    .addClass("dx-row-lines")
                    .addClass("dx-column-lines");
                let controlRow = $(`<td class="dx-command-edit dx-command-edit-with-icons dx-cell-focus-disabled"/>`).append(getControlRowLayer(rowIndex, data));
                let standardName = $("<td/>").append(getStandardNameLayer(rowIndex, data));
                let quantity = $(`<td aria-describedby="dx-col-2" aria-selected="false" role="gridcell" aria-colindex="2" style="text-align: right;"/>`).append(getQuantityLayer(rowIndex, data));
                let amount = $(`<td aria-describedby="dx-col-3" aria-selected="false" role="gridcell" aria-colindex="3" style="text-align: right;"/>`).append(getAmountLayer(rowIndex, data));
                let computedWeight = $(`<td class="computed-weight" rowIndex="${rowIndex}"/>`).append(getComputedWeightLayer(rowIndex, data));

                row.append(controlRow);
                row.append(standardName);
                row.append(quantity);
                row.append(amount);
                row.append(computedWeight);

                break;

            case rowTypes.rowFooter:
                let footerStageErrorLayer = $(`<td colspan ="2" class="footer-validation-cell" rowIndex="${rowIndex}"/>`).append(getFooterStageErrorLayer(rowIndex, data));

                row.addClass("dx-row")
                    .addClass("dx-footer-row")
                    .addClass("dx-row-lines")
                    .addClass("dx-column-lines")
                    .attr("uid", data.id)
                    .append(footerStageErrorLayer)
                    .append($(`<td><div class="dx-datagrid-summary-item dx-datagrid-text-content quantity-total-summary">${data.quantity} ${data.measure_unit_value}</div></td>`))
                    .append($(`<td><div class="dx-datagrid-summary-item dx-datagrid-text-content amount-total-summary">${data.amount} шт</div></td>`))
                    .append($(`<td><div class="dx-datagrid-summary-item dx-datagrid-text-content weight-total-summary">${data.weight} т</div></td>`))
                break;
        }

        return row;
    }

    //TODO 1 - массив фильтрации формата DevExpress
    //TODO 2 - проект, опционально
    function showMaterialsAddingForm() {
        let dataSource = materialStandardsListDataSource;

        switch (currentTransformationStage) {
            case transformationStages.fillingMaterialsToTransform:
                dataSource = availableMaterialsDataSource
                break;
            case transformationStages.fillingMaterialsAfterTransform:
                dataSource = materialStandardsListDataSource;
                break;
        }

        dataSource.filter(getMaterialAddingFormFilter());
        let materialsList = materialsAddingForm.getEditor("materialsStandardsList");
        materialsList.option("dataSource", dataSource);
        dataSource.reload();
        materialsList.option("selectedRowKeys", []);

        let popupContainer = createPopupContainer();
        popupContainer.show();
    }

    function getStandardNameLayer(rowIndex, data) {
        let divStandardName = $(`<div class="standard-name"></div>`)

        $(`<div>${data.standard_name}</div>`)
            .appendTo(divStandardName);

        if (data.comment) {
            $(`<div class="material-comment">${data.comment}</div>`)
                .appendTo(divStandardName);

            divStandardName.addClass("standard-name-cell-with-comment");
        }

        return divStandardName;
    }

    function getQuantityLayer(rowIndex, data) {
        let isReadOnly = false;

        switch (data.rowTransformationStage) {
            case transformationStages.fillingMaterialsToTransform:
                isReadOnly = true;
                break;//data.rowType === rowTypes.rowMaterialsToTransform;
        }

        let quantity = Math.round(data.quantity * 100) / 100;

        if (isReadOnly) {
            if (quantity) {
                return $(`<div class="transformation-quantity"><span>${quantity} ${data.measure_unit_value}</span></div>`)
            } else {
                return $(`<div class="transformation-quantity measure-units-only"><span>${data.measure_unit_value}</span></div>`)
            }
        } else {
            let quantityLayer = $(
                `<div class="measure-units-only without-box-shadow dx-show-invalid-badge dx-numberbox dx-texteditor dx-editor-outlined dx-texteditor-empty dx-widget">` +
                `</div>`
            );

            quantityLayer.dxNumberBox({
                min: 0,
                value: quantity,
                format: "#0.## " + data.measure_unit_value,
                placeholder: data.measure_unit_value,
                mode: "number",
                onValueChanged: (e) => {
                    e.component.option("format", "#0.## " + data.measure_unit_value);
                    transformationData.store()
                        .update(data.id, {quantity: e.value})
                        .done(() => {
                            updateComputedWeightLayer(rowIndex, data);
                            validateMaterialList(data.validationUid);
                            validateStages(null);
                        });
                    operationForm.getEditor("transformationGrid").endUpdate();
                },
                onFocusIn: (e) => {
                    e.component.option("format", "");
                },
                onFocusOut: (e) => {
                    e.component.option("format", "#0.## " + data.measure_unit_value);
                },
            });

            return quantityLayer;
        }
    }

    function getAmountLayer(rowIndex, data) {
        let isReadOnly = false;

        let amount = data.amount;

        if (isReadOnly) {
            if (amount) {
                return $(`<div>${amount} шт</div>`)
            } else {
                return $(`<div class="measure-units-only">шт</div>`)
            }
        } else {
            let amountLayer = $(
                `<div class="measure-units-only without-box-shadow dx-show-invalid-badge dx-numberbox dx-texteditor dx-editor-outlined dx-texteditor-empty dx-widget">` +
                `</div>`
            );

            amountLayer.dxNumberBox({
                min: 0,
                value: amount,
                format: "#0 шт",
                placeholder: "шт",
                mode: "number",
                onValueChanged: (e) => {
                    e.component.option("format", "#0 шт");
                    transformationData.store()
                        .update(data.id, {amount: e.value})
                        .done(() => {
                            updateComputedWeightLayer(rowIndex, data);
                            validateMaterialList(data.validationUid);
                            validateStages(null);
                        });

                },
                onFocusIn: (e) => {
                    e.component.option("format", "");
                },
                onFocusOut: (e) => {
                    e.component.option("format", "# шт");
                },
            });

            return amountLayer;
        }
    }

    function getComputedWeightLayer(rowIndex, data) {
        let weight = data.quantity * data.amount * data.standard_weight;

        if (weight) {
            weight = Math.round(weight * 1000) / 1000
        } else {
            weight = 0;
        }

        return $(`<div>${weight} т</div>`)
    }

    function updateComputedWeightLayer(rowIndex, data) {
        $(`.computed-weight[rowIndex=${rowIndex}]`).html(getComputedWeightLayer(rowIndex, data));
    }

    function getControlRowLayer(rowIndex, data) {
        let controlRowLayer = $('<div class="command-row-buttons"/>');
        let validationUid = data.validationUid;
        let validationDiv = $(`<div class="row-validation-indicator"/>`)
            .attr("validation-uid", validationUid)

        switch (data.validationResult) {
            case "valid":
                let checkIcon = $(`<i/>`)
                    .addClass(`dx-link dx-icon fas fa-check-circle dx-link-icon`)
                    .attr(`style`, `color: #8bc34a`)
                    .appendTo(validationDiv);
                break;
            case "invalid":
                let exclamationTriangle = $(`<i/>`).addClass(`dx-link fas fa-exclamation-triangle`)
                    .attr(`style`, `color: #f15a5a`)
                    .mouseenter(function () {
                        if (!data.errorMessage) {
                            return;
                        }

                        let validationDescription = $('#validationTemplate');

                        validationDescription.dxPopover({
                            position: "top",
                            width: 300,
                            contentTemplate: data.errorMessage,
                            hideEvent: "mouseleave",
                        })
                            .dxPopover("instance")
                            .show($(this));
                    })
                    .appendTo(validationDiv);
                break;
        }

        let deleteRowDiv = $(`<div row-index="${rowIndex}"><a href="#" class="dx-link dx-icon-trash dx-link-icon" title="Удалить" row-index="${rowIndex}"></a></div>`)
            .attr("validation-uid", validationUid);

        deleteRowDiv.click((e) => {
            e.preventDefault();

            operationForm.getEditor("transformationGrid").deleteRow(rowIndex);

            validateMaterialList(data.validationUid);
            validateStages(null);
        })

        let duplicateRowDiv = $(`<div class=""><a href="#" class="dx-link dx-icon-copy dx-link-icon" title="Дублировать"></a></div>`)
            .attr("validation-uid", validationUid)
            .click((e) => {
                e.preventDefault();

                let clonedItemId = "uid-" + new DevExpress.data.Guid().toString();

                let clonedItem = $.extend({},
                    data, {
                        id: clonedItemId,
                        edit_states: ["addedByRecipient"],
                        validationUid: getValidationUid(data),
                    }
                );

                transformationData.store().insert(clonedItem).done(() => {
                    transformationData.reload();
                    validateMaterialList(data.validationUid);
                    validateStages(null);
                });
            })

        controlRowLayer.append(validationDiv);
        controlRowLayer.append(deleteRowDiv);
        controlRowLayer.append(duplicateRowDiv);

        return controlRowLayer;
    }

    function getFooterStageErrorLayer(rowIndex, data) {
        let validationUid = data.validationUid;
        let validationDiv = $(`<div class="footer-row-validation"/>`).attr("validation-uid", validationUid);
        let validationIconDiv = $(`<div class="footer-row-validation-indicator"/>`);


        let validationMessageDiv = $(`<div class="footer-row-validation-message"/>`);

        switch (data.validationResult) {
            case "valid":
                let checkIcon = $(`<i/>`)
                    .addClass(`dx-icon fas fa-check-circle`)
                    .attr(`style`, `color: #8bc34a;font-size: 16px;`)
                    .appendTo(validationIconDiv);

                validationMessageDiv.html("Проблемы не обнаружены");
                break;
            case "invalid":
                let exclamationTriangle = $(`<i/>`).addClass(`dx-icon fas fa-exclamation-triangle`)
                    .attr(`style`, `color: #f15a5a;font-size: 16px;`)
                    .appendTo(validationIconDiv);
                validationMessageDiv.html(data.errorMessage);
                break;
        }
        validationDiv.append(validationIconDiv);
        validationDiv.append(validationMessageDiv);
        return validationDiv;
    }

    function getMinDate() {
        let minDate = new Date();

        return minDate.setDate(minDate.getDate() - 3);
    }

    function getMaterialAddingFormFilter() {
        let filterArray = [];
        switch (currentTransformationStage) {
            case transformationStages.fillingMaterialsToTransform:
                switch (currentTransformationType) {
                    case "CUTTING":
                        filterArray = ["accounting_type", "=", "2"]
                        break;
                    default:
                        filterArray = null
                }
                break;
            case transformationStages.fillingMaterialsAfterTransform:
                switch (currentTransformationType) {
                    case "CUTTING":
                        let transformationDataArray = transformationData.store().createQuery()
                            .filter(['standard_id', '>', 0])
                            .toArray();

                        let brands = [];
                        let uniqueBrands = [];
                        let brandsFilterArray = []
                        transformationDataArray.forEach((item) => {
                            brands.push(item.brands)
                        });

                        uniqueBrands = Array.from(new Set(brands));

                        uniqueBrands.forEach((item) => {
                            brandsFilterArray.push([
                                'standard_brands',
                                '=',
                                item
                            ]);
                            brandsFilterArray.push('or');
                        })

                        brandsFilterArray.pop();

                        if (brandsFilterArray.length > 0) {
                            filterArray.push(brandsFilterArray);
                            filterArray.push("and");
                        }

                        filterArray.push(["standard_properties", "=", null])
                        filterArray.push("and")
                        filterArray.push(["accounting_type", "=", "2"])

                        console.log('filterArray', filterArray);
                        break;
                    default:
                        filterArray = null
                }
                break;
        }
        return filterArray;
    }

    function insertMaterialsRemains() {
        console.log(insertMaterialsRemains)
        let materialsRemains = [];
        let standardFound = false;
        let materialsToTransform = transformationData.store().createQuery()
            .filter(["rowType", "=", rowTypes.rowData],
                "and",
                ["rowTransformationStage", "=", transformationStages.fillingMaterialsToTransform])
            .toArray()

        materialsToTransform.forEach((material) => {
            materialsRemains.forEach((remainMaterial) => {
                if (remainMaterial.standard_id === material.standard_id) {
                    standardFound = true;
                }
            });

            if (!standardFound) {
                let data = {
                    id: "uid-" + new DevExpress.data.Guid().toString(),
                    standard_id: material.standard_id,
                    standard_name: material.standard_name,
                    accounting_type: material.accounting_type,
                    material_type: material.material_type,
                    measure_unit: material.measure_unit,
                    measure_unit_value: material.measure_unit_value,
                    standard_weight: material.standard_weight,
                    quantity: 0,
                    amount: 0,
                    comment: null,
                    initial_comment_id: null,
                    initial_comment: null,
                    total_quantity: material.quantity,
                    total_amount: material.amount,
                    brands: material.brands,
                    validationUid: "uid-" + new DevExpress.data.Guid().toString(),
                    validationState: "unvalidated",
                    validationResult: "none",
                    rowType: rowTypes.rowData,
                    rowTransformationStage: transformationStages.fillingMaterialsRemains,
                    sortIndex: 8
                }

                materialsRemains.push(data);
                insertTransformationRow(data, transformationStages.fillingMaterialsRemains);
                standardFound = false;
                validateMaterialList(data.validationUid);
            }
        })
    }

    function insertMaterialsTechnologicalLosses() {
        let materialsTechnologicalLosses = [];
        let standardFound = false;
        let materialsToTransfer = transformationData.store().createQuery()
            .filter(["rowType", "=", rowTypes.rowData],
                "and",
                ["rowTransformationStage", "=", transformationStages.fillingMaterialsToTransform])
            .toArray()

        materialsToTransfer.forEach((material) => {
            materialsTechnologicalLosses.forEach((technologicalLossesMaterial) => {
                if (technologicalLossesMaterial.standard_id === material.standard_id) {
                    standardFound = true;
                }
            });

            if (!standardFound) {
                let data = {
                    id: "uid-" + new DevExpress.data.Guid().toString(),
                    standard_id: material.standard_id,
                    standard_name: material.standard_name,
                    accounting_type: material.accounting_type,
                    material_type: material.material_type,
                    measure_unit: material.measure_unit,
                    measure_unit_value: material.measure_unit_value,
                    standard_weight: material.standard_weight,
                    quantity: 0,
                    amount: 0,
                    comment: null,
                    initial_comment_id: null,
                    initial_comment: null,
                    total_quantity: material.quantity,
                    total_amount: material.amount,
                    brands: material.brands,
                    validationUid: "uid-" + new DevExpress.data.Guid().toString(),
                    validationState: "unvalidated",
                    validationResult: "none",
                    rowType: rowTypes.rowData,
                    rowTransformationStage: transformationStages.fillingMaterialsTechnologicalLosses,
                    sortIndex: 11
                }

                materialsTechnologicalLosses.push(data);
                insertTransformationRow(data, transformationStages.fillingMaterialsTechnologicalLosses);
                validateMaterialList(data.validationUid);
                standardFound = false;
            }
        })
    }


})()

