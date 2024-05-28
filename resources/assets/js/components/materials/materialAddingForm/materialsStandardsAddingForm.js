import {
    transformationStages,
    rowTypes,
    currentTransformationStage,
    transformationData,
    insertTransformationRow,
    getValidationUid,
    validateStages, validateMaterialList
} from "../transformationStorage"
import {createPopupContainer} from "./popup";
import {getMaterialTypesData} from "../dataService";
import * as dataSources from "./dateSources";

export let selectedMaterialStandardsListDataSource = new DevExpress.data.DataSource({
    store: new DevExpress.data.ArrayStore({
        key: "id",
        data: []
    })
})

export let materialsStandardsAddingForm = async () => {
    let materialTypesData;

    console.log('*******************************************************************************************');
    console.log('@materialStandardsSource', dataSources.materialStandardsSource);

    try {
        materialTypesData = await getMaterialTypesData();
    } catch (error) {
        console.error("Failed to get material types data:", error);
        return;
    }

    const materialsStandardsAddingForm = $("#materialsStandardsAddingForm").dxForm({
        colCount: 2,
        items: [{
            itemType: "group",
            colCount: 3,
            caption: "Эталоны",
            items: [{
                editorType: "dxDataGrid",
                name: "materialsStandardsList",
                editorOptions: {
                    dataSource: null,
                    height: "50vh",
                    width: 500,
                    showColumnHeaders: false,
                    showRowLines: false,
                    grouping: {
                        autoExpandAll: true,
                    },
                    groupPanel: {
                        visible: false
                    },
                    selection: {
                        allowSelectAll: true,
                        deferred: false,
                        mode: "multiple",
                        selectAllMode: "allPages",
                        showCheckBoxesMode: "always"
                    },
                    paging: {
                        enabled: false
                    },
                    scrolling: {
                        mode: 'virtual'
                    },
                    searchPanel: {
                        visible: true,
                        searchVisibleColumnsOnly: true,
                        width: 240,
                        placeholder: "Поиск..."
                    },
                    columns: [
                        {
                            dataField: "standard_name",
                            dataType: "string",
                            caption: "Наименование",
                            calculateFilterExpression: function (filterValue, selectedFilterOperation, target) {
                                if (target === "search") {
                                    let columnsNames = ["standard_name", "comment"]

                                    let words = filterValue.split(" ");
                                    let filter = [];

                                    columnsNames.forEach(function (column) {
                                        filter.push([]);
                                        words.forEach(function (word) {
                                            filter[filter.length - 1].push([column, "contains", word]);
                                            filter[filter.length - 1].push("and");
                                        });

                                        filter[filter.length - 1].pop();
                                        filter.push("or");
                                    })
                                    filter.pop();
                                    return filter;
                                }
                                return this.defaultCalculateFilterExpression(filterValue, selectedFilterOperation);
                            },
                            cellTemplate: function (container, options) {
                                let quantity;
                                let amount;

                                quantity = options.data.quantity ? options.data.quantity + " " : "";
                                amount = options.data.amount ? options.data.amount + " " : "";

                                switch (options.data.accounting_type) {
                                    case 2:
                                        let standardNameText = options.data.standard_name +
                                            ' (' +
                                            quantity +
                                            options.data.measure_unit_value +
                                            '/' +
                                            amount +
                                            'шт)';

                                        let divStandardName = $(`<div class="standard-name">${standardNameText}</div>`)
                                            .appendTo(container);

                                        if (options.data.comment) {
                                            let divMaterialComment = $(`<div class="material-comment">${options.data.comment}</div>`)
                                                .appendTo(container);

                                        }

                                        container.addClass("standard-name-cell-with-comment");

                                        break;
                                    default:
                                        return $("<div>").text(options.data.standard_name +
                                            ' (' +
                                            quantity +
                                            options.data.measure_unit_value +
                                            ')')
                                }
                            }
                        },
                        {
                            dataField: "material_type",
                            dataType: "number",
                            caption: "Тип материала",
                            groupIndex: 0,
                            lookup: {
                                dataSource: materialTypesData,
                                displayExpr: "name",
                                valueExpr: "id"
                            }
                        }],
                    onSelectionChanged: function (e) {
                        selectedMaterialStandardsListDataSource.store().clear();
                        e.selectedRowsData.forEach(function (selectedRowItem) {
                            selectedMaterialStandardsListDataSource.store().insert(selectedRowItem)
                        })

                        selectedMaterialStandardsListDataSource.reload();
                    }
                }
            }]
        },
            {
                itemType: "group",
                colCount: 3,
                caption: "Выбранные материалы",
                items: [{
                    editorType: "dxList",
                    name: "selectedMaterialsList",
                    editorOptions: {
                        dataSource: selectedMaterialStandardsListDataSource,
                        allowItemDeleting: true,
                        itemDeleteMode: "static",
                        height: "50vh",
                        width: 500,
                        itemTemplate: function (data) {
                            let quantity = data.quantity ? data.quantity + " " : "";
                            let amount = data.amount ? data.amount + " " : "";
                            let container = $('<div class="standard-name-cell-with-comment"></div>')

                            switch (data.accounting_type) {
                                case 2:
                                    let standardNameText = data.standard_name +
                                        ' (' +
                                        quantity +
                                        data.measure_unit_value +
                                        '/' +
                                        amount +
                                        'шт)';

                                    let divStandardName = $(`<div class="standard-name">${standardNameText}</div>`)
                                        .appendTo(container);

                                    if (data.comment) {
                                        let divMaterialComment = $(`<div class="material-comment">${data.comment}</div>`)
                                            .appendTo(container);

                                    }

                                    return container;
                                default:
                                    return $("<div>").text(data.standard_name +
                                        ' (' +
                                        quantity +
                                        data.measure_unit_value +
                                        ')')
                            }
                        },

                        onItemDeleted: function (e) {
                            let materialsStandardsList = materialsStandardsAddingForm.getEditor("materialsStandardsList");
                            let selectedMaterialsList = materialsStandardsAddingForm.getEditor("selectedMaterialsList");
                            let selectedRowsKeys = [];
                            selectedMaterialsList.option("items").forEach(function (selectedItem) {
                                selectedRowsKeys.push(selectedItem.id);
                            });

                            materialsStandardsList.option("selectedRowKeys", selectedRowsKeys);
                        }
                    }
                }]
            },
            {
                itemType: "button",
                colSpan: 2,
                horizontalAlignment: "right",
                buttonOptions: {
                    text: "Добавить",
                    type: "default",
                    stylingMode: "text",
                    useSubmitBehavior: false,

                    onClick: function () {
                        let selectedMaterialsData = materialsStandardsAddingForm.getEditor("selectedMaterialsList").option("items");

                        selectedMaterialsData.forEach(function (material) {
                            switch (currentTransformationStage) {
                                case transformationStages.fillingMaterialsToTransform:
                                case transformationStages.fillingMaterialsAfterTransform:
                                    let rowType;
                                    let sortIndex;
                                    switch (currentTransformationStage) {
                                        case transformationStages.fillingMaterialsToTransform:
                                            rowType = rowTypes.rowData;
                                            sortIndex = 2;
                                            break;
                                        case transformationStages.fillingMaterialsAfterTransform:
                                            rowType = rowTypes.rowData;
                                            sortIndex = 5;
                                            break;
                                    }
                                    let validationUid = getValidationUid(material);

                                    let data = {
                                        id: "uid-" + new DevExpress.data.Guid().toString(),
                                        material_id: material.id,
                                        standard_id: material.standard_id,
                                        standard_name: material.standard_name,
                                        accounting_type: material.accounting_type,
                                        material_type: material.material_type,
                                        measure_unit: material.measure_unit,
                                        measure_unit_value: material.measure_unit_value,
                                        standard_weight: material.weight,
                                        quantity: material.quantity,
                                        amount: material.amount,
                                        comment: material.comment,
                                        initial_comment_id: material.comment_id,
                                        initial_comment: material.comment,
                                        total_quantity: material.quantity,
                                        total_amount: material.amount,
                                        brands: material.standard_brands,
                                        rowType: rowType,
                                        rowTransformationStage: currentTransformationStage,
                                        sortIndex: sortIndex,
                                        validationUid: validationUid,
                                        validationState: "unvalidated",
                                        validationResult: "none",
                                        errorMessage: ""
                                    };

                                    insertTransformationRow(data, currentTransformationStage);

                                    validateMaterialList(data.validationUid);
                                    validateStages(null);
                                    break;
                                /*case "fillingMaterialsRemains":
                                    break;*/
                            }
                        })

                        //updateRowFooter();

                        /*switch(currentTransformationStage) {
                            case "fillingMaterialsToTransform":
                                repaintMaterialsToTransformLayer();
                                break;
                            case "fillingMaterialsAfterTransform":
                                repaintMaterialsAfterTransformLayer();
                                break;
                            case "fillingMaterialsRemains":
                                break;
                        }*/
                        let popupContainer = createPopupContainer();
                        popupContainer.hide();
                    }
                }
            }
        ]
    }).dxForm("instance")

    return materialsStandardsAddingForm;

}
