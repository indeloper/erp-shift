@extends('layouts.app')

@section('title', 'Планирование поставок материалов')

@section('url', route('materials.supply-planning.index'))

@section('css_top')
    <style>
        .dx-form-group {
            background-color: #fff;
            border: 1px solid #cfcfcf;
            border-radius: 1px;
            box-shadow: 0 1px 4px 0 rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .dx-layout-manager .dx-field-item:not(.dx-first-col) {
            padding-left: 0px !important;
        }

        .dx-form-group.dx-group-no-border {
            border: 0;
            border-radius: 0;
        }

        .dx-form-group-caption-buttons {
            display: flex;
            flex-direction: row-reverse;
            align-items: flex-start;
        }

        .dx-placeholder {
            line-height: 6px;
        }

        .supply-planning-details {
            border: 1px solid #e0e0e0;
        }

        .dx-datagrid-borders > .dx-datagrid-header-panel {
            border-bottom: 0;
            border-top: 1px solid #e0e0e0;
            border-left: 1px solid #e0e0e0;
            border-right: 1px solid #e0e0e0;
        }

        .dx-datagrid-header-panel .dx-button {
            margin-top: 6px;
            margin-right: 8px;
        }

        .supply-planning-materials {
            margin-bottom: 16px;
        }

        .weight-transfer-icon {
            position: absolute;
            font-size: 18px;
            color: #3384d5;
            cursor: pointer;
            z-index: 1;
        }

        .weight-transfer-icon:hover {
            color: #0a5bab;
        }
    </style>
@endsection

@section('content')
    <div id="formContainer"></div>
    <div id="gridContainer"></div>
    <div id="addNewPlanningObjectPopupContainer">
        @endsection

        @section('js_footer')
            <script>

                $(function () {
                    let dataSourceLoadOptions = {};
                    let availableMaterialsFilterOptions = {};
                    let currentSelectedProjectObject = {};
                    let supplyPlanningEditingForm;

                    let supplyObjectsStore = new DevExpress.data.CustomStore({
                        key: 'id',
                        load: (loadOptions) => {
                            dataSourceLoadOptions = loadOptions;
                            return $.getJSON("{{route('materials.supply-planning.planning-objects.list')}}",
                                {
                                    loadOptions: JSON.stringify(loadOptions),
                                });
                        },
                        insert: function (values) {
                            return $.ajax({
                                url: "{{route('materials.supply-planning.planning-objects.store')}}",
                                method: "POST",
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                data: {
                                    data: JSON.stringify(values),
                                    options: null
                                },
                                success: function (data, textStatus, jqXHR) {
                                    loadSupplyObjects();
                                    DevExpress.ui.notify("Данные успешно добавлены", "success", 1000);
                                },
                                error: function (jqXHR, textStatus, errorThrown) {
                                    DevExpress.ui.notify("При сохранении данных произошла ошибка", "error", 5000)
                                }
                            })
                        }
                    });

                    let supplyObjectsDataSource = new DevExpress.data.DataSource({
                        store: supplyObjectsStore,
                        loadMode: "raw"
                    });

                    //<editor-fold desc="JS: DataSources">
                    let projectObjectsStore = new DevExpress.data.CustomStore({
                        key: "id",
                        loadMode: "raw",
                        load: function (loadOptions) {
                            return $.getJSON("{{route('project-objects.list')}}",
                                {data: JSON.stringify(loadOptions)});
                        },
                    })

                    let projectObjectsDataSource = new DevExpress.data.DataSource({
                        store: projectObjectsStore
                    });

                    let brandsStore = new DevExpress.data.CustomStore({
                        key: "id",
                        loadMode: "raw",
                        load: function (loadOptions) {
                            return $.getJSON("{{route('materials.brands.list')}}",
                                {data: JSON.stringify(loadOptions)});
                        }
                    })

                    let brandsDataSource = new DevExpress.data.DataSource({
                        store: brandsStore
                    })

                    let brandTypesStore = new DevExpress.data.CustomStore({
                        key: "id",
                        loadMode: "raw",
                        load: function (loadOptions) {
                            return $.getJSON("{{route('materials.brand-types.list')}}",
                                {data: JSON.stringify(loadOptions)});
                        }
                    })

                    let brandTypesDataSource = new DevExpress.data.DataSource({
                        store: brandTypesStore
                    })

                    let contractorsStore = new DevExpress.data.CustomStore({
                        key: "id",
                        loadMode: "raw",
                        load: function (loadOptions) {
                            return $.getJSON("{{route('contractors.list')}}",
                                {data: JSON.stringify({dxLoadOptions: loadOptions})});
                        },
                    })

                    let contractorsDataSource = new DevExpress.data.DataSource({
                        store: contractorsStore
                    })

                    let materialsSupplyPlanningSource = new DevExpress.data.DataSource({
                        store: new DevExpress.data.CustomStore({
                            key: "id",
                            load: function (loadOptions) {
                                dataSourceLoadOptions = loadOptions;
                                return $.getJSON("{{route('materials.supply-planning.list')}}",
                                    {
                                        loadOptions: JSON.stringify(loadOptions),
                                    });
                            },
                            insert: function (values) {
                                return $.ajax({
                                    url: "{{route('materials.supply-planning.store')}}",
                                    method: "POST",
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    },
                                    data: {
                                        data: JSON.stringify(values),
                                        options: null
                                    },
                                    success: function (data, textStatus, jqXHR) {
                                        DevExpress.ui.notify("Данные успешно добавлены", "success", 1000)
                                    },
                                })
                            },
                            update: function (key, values) {
                                return $.ajax({
                                    url: "{{route('materials.supply-planning.update')}}",
                                    method: "PUT",
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    },
                                    data: {
                                        key: key,
                                        modifiedData: JSON.stringify(values)
                                    },
                                    success: function (data, textStatus, jqXHR) {
                                        DevExpress.ui.notify("Данные успешно изменены", "success", 1000)
                                    }
                                });
                            },
                            remove: function (key) {
                                return $.ajax({
                                    url: "{{route('materials.supply-planning.delete')}}",
                                    method: "DELETE",
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    },
                                    data: {
                                        key: key
                                    },
                                    success: function (data, textStatus, jqXHR) {
                                        DevExpress.ui.notify("Данные успешно удалены", "success", 1000)
                                    },

                                    error: function (jqXHR, textStatus, errorThrown) {
                                        DevExpress.ui.notify("При удалении данных произошла ошибка", "error", 5000)
                                    }
                                })
                            }
                        })
                    });

                    function getMaterialWeight(data, weightColumnName = 'weight') {
                        let amount = data.amount;
                        let weight = amount * data.quantity * data[weightColumnName];

                        if (isNaN(weight)) {
                            weight = 0;
                        } else {
                            weight = Math.round(weight * 1000) / 1000;
                        }

                        data.computed_weight = weight;
                        return weight;
                    }

                    function createSupplyPlanningForm(supplyObjects) {
                        let supplyPlanningForm = $("#formContainer").dxForm({
                            items: [
                                {
                                    itemType: "group",
                                    caption: "Планирование поставок материалов",
                                    cssClass: "material-supply-planning-grid",
                                    items: [
                                        {
                                            itemType: "tabbed",
                                            tabs: getPlanningObjectsTabArray(supplyObjects),
                                            tabPanelOptions: {
                                                deferRendering: true,
                                                onTitleClick: (e) => {
                                                    console.log("onTitleClick", e);
                                                    currentSelectedProjectObject = e.itemData.planningObjectData;
                                                    if (e.itemData.type === "summaryTab") {
                                                        //planningSummaryStore
                                                    }
                                                }
                                            }
                                        }
                                    ]
                                }
                            ]
                        }).dxForm('instance');

                        @if(Auth::user()->can('material_supply_planning_editing'))
                        createGroupHeaderButtons();
                        @endcan

                            return supplyPlanningForm;
                    }

                    function createGroupHeaderButtons() {
                        let groupCaption = $('.material-supply-planning-grid').find('.dx-form-group-with-caption');
                        $('<div>').addClass('dx-form-group-caption-buttons').prependTo(groupCaption);
                        groupCaption.find('span').addClass('dx-form-group-caption-span-with-buttons');
                        let groupCaptionButtonsDiv = groupCaption.find('.dx-form-group-caption-buttons');

                        $('<div>')
                            .dxButton({
                                text: "Добавить объект",
                                icon: "fas fa-plus",
                                onClick: (e) => {
                                    showProjectObjectNamePopup(true, {});
                                }
                            })
                            .addClass('dx-form-group-caption-button')
                            .prependTo(groupCaptionButtonsDiv)
                    }

                    function getPlanningObjectsTabArray(supplyObjects) {
                        let summaryTab = {
                            title: "Сводка",
                            icon: "fas fa-table",
                            type: "summaryTab",
                            template: (data, index, container) => {
                                return getSummaryContentTemplate();
                            }
                        }

                        let objectsList = [];

                        supplyObjects.forEach((element) => {
                            objectsList.push({
                                title: element.name,
                                type: "objectTab",
                                planningObjectData: element,
                                template: (data, index, container) => {
                                    return getObjectContentTemplate(data);
                                }
                            })
                        })

                        return [summaryTab, ...objectsList];
                    }

                    const newObjectValidationGroupName = "newObjectValidationForm";

                    const createPopupTemplate = (formData) => () => {
                        return $("<div>").dxForm({
                            formData: formData,
                            validationGroup: newObjectValidationGroupName,
                            showColonAfterLabel: false,
                            items: [
                                {
                                    dataField: "object_name",
                                    validationRules: [{type: "required", message: `Поле "Наименование объекта" обязательно для заполнения`}],
                                    label: {
                                        text: "Наименование объекта"
                                    },
                                },
                            ],
                        });
                    };

                    const planningObjectNamePopup = $("#addNewPlanningObjectPopupContainer").dxPopup({
                        hideOnOutsideClick: true,
                        showCloseButton: true,
                        height: "auto",
                        width: 400,
                    }).dxPopup("instance");

                    function getSupplyPlanningPopup () {
                        return {
                            hideOnOutsideClick: true,
                            showCloseButton: true,
                            height: "auto",
                            width: 800,
                            onShowing: (e) => {
                                //console.log("onShowing", e);
                                supplyPlanningEditingForm = $('body,html').find('#supplyPlanningEditingForm' + currentSelectedProjectObject.id).dxForm('instance');

                                loadAvailableMaterials();
                            },
                            onHiding: () => {
                                availableMaterialsFilterOptions = {};
                            }
                        }
                    }

                    function getSupplyPlanningEditForm() {
                        return {
                            elementAttr: {
                                id: () => {return "supplyPlanningEditingForm" + currentSelectedProjectObject.id}
                            },
                            colCount: 3,
                            items: [
                                {
                                    dataField: "brand_type_id",
                                    label: {
                                        text: "Тип шпунта"
                                    },
                                },
                                {
                                    dataField: "quantity",
                                    label: {
                                        text: "Длина (м.п)"
                                    }
                                },
                                {
                                    dataField: "planned_project_weight",
                                    label: {
                                        text: "Планируемый объем поставки (т)"
                                    }
                                },
                                {
                                    name: "materialInputGrid",
                                    colSpan: 3,
                                    label: {
                                        visible: false
                                    },
                                    editorType: "dxDataGrid",
                                    editorOptions: {
                                        height: 400,
                                        dataSource: new DevExpress.data.DataSource({
                                            store: new DevExpress.data.CustomStore({
                                                key: ["reserved_id", "project_object", "standard_id"],
                                                loadMode: "raw",
                                                load: (loadOptions) => {
                                                    loadOptions.userData = availableMaterialsFilterOptions;
                                                    return $.getJSON(`{{route('materials.supply-planning.available-material-list')}}`,
                                                        {
                                                            loadOptions: JSON.stringify(loadOptions),
                                                        });
                                                }
                                            })
                                        }),
                                        focusedRowEnabled: false,
                                        hoverStateEnabled: true,
                                        columnAutoWidth: false,
                                        showBorders: true,
                                        showColumnLines: true,
                                        filterRow: {
                                            visible: true,
                                            applyFilter: "auto"
                                        },
                                        toolbar: {
                                            visible: false
                                        },
                                        grouping: {
                                            autoExpandAll: true,
                                        },
                                        groupPanel: {
                                            visible: false
                                        },
                                        editing: {
                                            allowUpdating: true,
                                            allowAdding: false,
                                            allowDeleting: false,
                                            mode: "batch"
                                        },
                                        remoteOperations: false,
                                        scrolling: {
                                            mode: "virtual",
                                            rowRenderingMode: "virtual",
                                            useNative: false,
                                            scrollByContent: true,
                                            scrollByThumb: true,
                                            showScrollbar: "onHover"
                                        },
                                        /*selection: {
                                            mode: "multiple",
                                            allowSelectAll: false
                                        },*/
                                        paging: {
                                            enabled: true,
                                            pageSize: 50
                                        },
                                        columns: [
                                            {
                                                dataField: "short_name",
                                                caption: "Объект",
                                                groupIndex: 0,
                                                allowEditing: false
                                            },

                                            {
                                                dataField: "name",
                                                caption: "Наименование",
                                                allowEditing: false

                                            },
                                            {
                                                dataField: "summary_amount",
                                                caption: "Количество (шт)",
                                                allowEditing: false
                                            },
                                            {
                                                dataField: "summary_weight",
                                                dataType: "number",
                                                caption: "Доступный вес (т)",
                                                allowEditing: false,
                                                calculateDisplayValue: (rowData) => {
                                                    return rowData.summary_weight - rowData.reserved_weight_on_other_objects
                                                },
                                                customizeText: (e) => {
                                                    return new Intl.NumberFormat('ru-RU').format(e.value);
                                                },
                                                editorOptions: {
                                                    min: 0,
                                                },
                                                cellTemplate: (container, options) => {
                                                    $(`<i class="fa fa-arrow-circle-right weight-transfer-icon" aria-hidden="true"></i>`)
                                                        /*.offset({
                                                            left: container.parent().offset().left + 16
                                                        })*/
                                                        .on("click",(e) => {
                                                            options.component.cellValue(options.row.rowIndex, "reserved_weight", options.data.summary_weight - options.data.reserved_weight_on_other_objects);
                                                        })
                                                        .appendTo(container);

                                                    $(`<div>${options.text}</div>`)
                                                        .appendTo(container);
                                                }
                                            },
                                            {
                                                dataField: "reserved_weight",

                                                caption: "Бронируемый вес (т)",
                                                customizeText: (e) => {
                                                    return new Intl.NumberFormat('ru-RU').format(e.value);
                                                },
                                                alignment: "right",
                                                editorOptions: {
                                                    min: 0,
                                                },
                                                validationRules: [
                                                    {
                                                        type: "custom",
                                                        validationCallback: (e) => {
                                                            console.log(e);
                                                            return e.data.summary_weight >= e.value;
                                                        },
                                                        message: "Бронируемый вес не должен превышать доступный!"
                                                    }
                                                ]
                                            },
                                        ],
                                        summary: {
                                            recalculateWhileEditing: true,
                                            totalItems: [
                                                {
                                                    column: 'summary_weight',
                                                    summaryType: 'sum',
                                                    customizeText: (e) => {
                                                        return `Доступно: ${new Intl.NumberFormat('ru-RU').format(e.value)} т`;
                                                    }
                                                },
                                                {
                                                    column: 'reserved_weight',
                                                    summaryType: 'sum',
                                                    customizeText: (e) => {
                                                        return `Забронировано: ${new Intl.NumberFormat('ru-RU').format(e.value)} т`;
                                                    }
                                                }
                                            ]
                                        }
                                    }
                                }
                            ]
                        }
                    }

                    const confirmItem = {
                        widget: "dxButton",
                        location: "after",
                        toolbar: "bottom",
                        options: {
                            text: "ОК",
                            type: "normal",
                            stylingMode: "outlined"
                        },
                    };

                    const cancelItem = {
                        widget: "dxButton",
                        location: "after",
                        toolbar: "bottom",
                        options: {
                            text: "Отмена",
                            type: "normal",
                            stylingMode: "outlined"
                        },
                    };

                    const showProjectObjectNamePopup = (isNewRecord, data) => {
                        const contentTemplate = createPopupTemplate(data);
                        planningObjectNamePopup.option({
                            title: isNewRecord ? "Добавить планируемый объект" : "Редактировать планируемый объект",
                            contentTemplate,
                            toolbarItems: [
                                {
                                    ...confirmItem,
                                    onClick: () => {
                                        let result = DevExpress.validationEngine.validateGroup(newObjectValidationGroupName);

                                        if (!result.isValid)
                                            return;

                                        supplyObjectsStore.insert({name: data.object_name}).done(() => {
                                            planningObjectNamePopup.hide();
                                            loadSupplyObjects();
                                        });
                                    },
                                },
                                {
                                    ...cancelItem,
                                    onClick: () => {
                                        planningObjectNamePopup.hide();
                                    },
                                }
                            ],
                            visible: true,
                        });
                    };

                    function getSummaryContentTemplate() {
                        let planningSummaryStore = new DevExpress.data.CustomStore({
                            loadMode: "raw",
                            load: function (loadOptions) {
                                let url = "{{route('materials.supply-planning.get-summary')}}";

                                return $.getJSON(url,
                                    {
                                        loadOptions: JSON.stringify(loadOptions),
                                    });
                            },
                        })

                        return $(`<div>`).dxPivotGrid({
                            dataSource: {
                                store: planningSummaryStore,
                                fields: [
                                    {
                                        area: "row",
                                        dataField: "standard_name",
                                        caption: "Эталон",
                                        dataType: "string",
                                        width: 150
                                    },
                                    {
                                        area: "row",
                                        dataField: "quantity",
                                        caption: "Длина",
                                        dataType: "number",
                                        width: 80,
                                        customizeText: (cellData) => {
                                            return `${new Intl.NumberFormat('ru-RU').format(cellData.value)} м.п`;
                                        }
                                    },
                                    {
                                        area: "row",
                                        dataField: "supply_object_name",
                                        caption: "Планируемый объект",
                                        dataType: "string",
                                        width: 250
                                    },
                                    {
                                        area: "column",
                                        dataField: "project_object_name",
                                        caption: "Объект поставки",
                                        dataType: "string"
                                    },
                                    {
                                        area: "data",
                                        dataField: "weight",
                                        dataType: 'number',
                                        summaryType: 'sum',
                                        caption: "Вес",
                                        customizeText: (cellData) => {
                                            if (cellData.valueText !== '') {
                                                return `${new Intl.NumberFormat('ru-RU').format(cellData.value)} т`;
                                            } else {
                                                return ''
                                            }
                                        }
                                    }
                                ]
                            },
                            allowSortingBySummary: true,
                            showBorders: true,
                        })
                    }

                    function getObjectContentTemplate(data) {
                        let planningObjectDataSource = new DevExpress.data.DataSource({
                            loadMode: "raw",
                            /*map: function (dataItem) {
                                return {
                                    brand_with_quantity: dataItem.brand_type_name + " " + new Intl.NumberFormat('ru-RU').format(dataItem.quantity) + " м.п"
                                }
                            },*/
                            store: new DevExpress.data.CustomStore({
                                key: ["id", "supply_material_id"],
                                load: function (loadOptions) {
                                    let url = "{{route('materials.supply-planning.get-materials-for-supply-planning', ['planningObjectId' => 'planningObjectIdValue'])}}";
                                    url = url.replace('planningObjectIdValue', data.planningObjectData.id);
                                    return $.getJSON(url,
                                        {
                                            loadOptions: JSON.stringify(loadOptions),
                                        });
                                },
                                insert: function (values) {
                                    return $.ajax({
                                        url: "{{route('materials.supply-planning.store')}}",
                                        method: "POST",
                                        headers: {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                        },
                                        data: {
                                            data: JSON.stringify(values),
                                            options: null
                                        },
                                        success: function (data, textStatus, jqXHR) {
                                            DevExpress.ui.notify("Данные успешно добавлены", "success", 1000)
                                        },
                                        error: function (jqXHR, textStatus, errorThrown) {
                                            DevExpress.ui.notify("При сохранении данных произошла ошибка", "error", 5000)
                                        }
                                    })
                                },
                                update: function (key, values) {
                                    return $.ajax({
                                        url: "{{route('materials.supply-planning.update')}}",
                                        method: "PUT",
                                        headers: {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                        },
                                        data: {
                                            key: key,
                                            modifiedData: JSON.stringify(values)
                                        },
                                        success: function (data, textStatus, jqXHR) {
                                            DevExpress.ui.notify("Данные успешно изменены", "success", 1000)
                                        },
                                        error: function (jqXHR, textStatus, errorThrown) {
                                            DevExpress.ui.notify("При сохранении данных произошла ошибка", "error", 5000)
                                        }
                                    });
                                },
                                remove: (key) => {
                                    return $.ajax({
                                        url: "{{route('materials.supply-planning.delete')}}",
                                        method: "DELETE",
                                        headers: {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                        },
                                        data: {
                                            key: key
                                        },
                                        success: function (data, textStatus, jqXHR) {
                                            DevExpress.ui.notify("Данные успешно удалены", "success", 1000)
                                        },
                                        error: function (jqXHR, textStatus, errorThrown) {
                                            DevExpress.ui.notify("При удвлении данных произошла ошибка", "error", 5000)
                                        }
                                    })
                                }
                            })
                        });

                        return $(`<div>`).dxDataGrid({
                            dataSource: planningObjectDataSource,
                            focusedRowEnabled: false,
                            hoverStateEnabled: true,
                            columnAutoWidth: false,
                            showBorders: true,
                            showColumnLines: true,
                            filterRow: {
                                visible: true,
                                applyFilter: "auto"
                            },
                            toolbar: {
                                visible: false,
                                items: []
                            },
                            grouping: {
                                autoExpandAll: true,
                            },
                            groupPanel: {
                                visible: false
                            },
                            editing: {
                                mode: 'popup',
                                allowUpdating: true,
                                allowAdding: false,
                                allowDeleting: true,
                                selectTextOnEditStart: true,
                                popup: getSupplyPlanningPopup(),
                                form: getSupplyPlanningEditForm(),
                            },
                            remoteOperations: false,
                            scrolling: {
                                mode: "virtual",
                                rowRenderingMode: "virtual",
                                useNative: false,
                                scrollByContent: true,
                                scrollByThumb: true,
                                showScrollbar: "onHover"
                            },
                            paging: {
                                enabled: true,
                                pageSize: 50
                            },
                            columns: [
                                {
                                    dataField: "brand_with_quantity",
                                    caption: "Тип марки материала",
                                    groupIndex: 0,
                                    groupCellTemplate: function(container, options) {
                                        $('<div>').text(`${options.column.caption}: ${options.value}`).appendTo(container);
                                        container.attr("colspan", 4);

                                        let commandGroupCell = $('<td class="dx-command-edit dx-command-edit-with-icons dx-cell-focus-disabled" role="gridcell" aria-colindex="6" style="text-align: center;" tabindex="0">')
                                            .appendTo(container.parent());

                                        $('<a href="#" class="dx-link dx-link-edit dx-icon-edit dx-link-icon" title="Редактировать" aria-label="Редактировать">')
                                            .on("click", (e) => {
                                                e.preventDefault();
                                                options.component.beginCustomLoading('');
                                                options.component.editRow(options.row.rowIndex + 1);
                                                options.component.endCustomLoading();
                                            })
                                            .appendTo(commandGroupCell);

                                        $('<a href="#" class="dx-link dx-link-trash dx-icon-trash dx-link-icon" title="Удалить" aria-label="Удалить">')
                                            .on("click", (e) => {
                                                e.preventDefault();
                                                options.component.beginCustomLoading('');
                                                options.component.deleteRow(options.row.rowIndex + 1);
                                                options.component.endCustomLoading();
                                            })
                                            .appendTo(commandGroupCell);
                                    }
                                },
                                {
                                    dataField: "short_name",
                                    caption: "Объект"
                                },
                                {
                                    dataField: "standard_name",
                                    caption: "Эталон"
                                },
                                {
                                    dataField: "quantity",
                                    caption: "Планируемая длина",
                                    customizeText: (e) => {
                                        return new Intl.NumberFormat('ru-RU').format(e.value) + " м.п";
                                    },
                                    validationRules: [{ type: "required" }]
                                },
                                {
                                    dataField: "weight",
                                    caption: "Забронированный вес",
                                    customizeText: (e) => {
                                        return new Intl.NumberFormat('ru-RU').format(e.value) + " т";
                                    },
                                },
                                {
                                    dataField: "brand_type_id",
                                    caption: "Тип шпунта",
                                    editorType: "dxSelectBox",
                                    visible: false,
                                    editorOptions: {
                                        dataSource: brandTypesDataSource,
                                        displayExpr: 'name',
                                        valueExpr: 'id'
                                    },
                                    validationRules: [{ type: "required" }],
                                },
                                {
                                    dataField: "planned_project_weight",
                                    caption: "Планируемый объем поставки",
                                    dataType: "number",
                                    visible: false,
                                    validationRules: [{ type: "required" }]
                                },
                                {
                                    type: 'buttons',
                                    width: 150,
                                    buttons: [
                                        {
                                            name: 'edit',
                                            visible: (e) => {
                                                return false;
                                            }
                                        },
                                        {
                                            name: 'delete',
                                            visible: (e) => {
                                                return false;
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
                            ],
                            summary: {
                                groupItems: [
                                    {
                                        showInColumn: 'short_name',
                                        column: 'planned_project_weight',
                                        summaryType: 'max',
                                        showInGroupFooter: true,
                                        customizeText: (e) => {
                                            return `Потребность: ${new Intl.NumberFormat('ru-RU').format(e.value)} т`;
                                        }
                                    },
                                    {
                                        column: 'weight',
                                        summaryType: 'sum',
                                        showInGroupFooter: true,
                                        customizeText: (e) => {
                                            return `Забронировано: ${new Intl.NumberFormat('ru-RU').format(e.value)} т`;
                                        }
                                    },
                                    {
                                        column: 'quantity',
                                        summaryType: 'sum',
                                        showInGroupFooter: true,
                                        customizeText: (e) => {
                                            return ``;
                                        }
                                    }
                                ],
                                /*totalItems: [
                                    {
                                        showInColumn: 'short_name',
                                        column: 'planned_project_weight',
                                        summaryType: 'max',
                                        customizeText: (e) => {
                                            return `Требуемое количество: ${new Intl.NumberFormat('ru-RU').format(e.value)} т`;
                                        }
                                    },
                                    {
                                        column: 'weight',
                                        summaryType: 'max',
                                        customizeText: (e) => {
                                            return `Забронировано: ${new Intl.NumberFormat('ru-RU').format(e.value)} т`;
                                        }
                                    }
                                ]*/
                            },
                            onEditorPreparing: (args) => {
                                if ((args.dataField === "brand_type_id" || args.dataField === "quantity") && args.parentType === "dataRow") {

                                    args.editorOptions.readOnly = !args.row.isNewRow;

                                    const defaultValueChangeHandler = args.editorOptions.onValueChanged;

                                    args.editorOptions.onValueChanged = (e) => {
                                        defaultValueChangeHandler(e);

                                        console.log(args);

                                        availableMaterialsFilterOptions.planning_object_id = currentSelectedProjectObject.id;

                                        if (args.dataField === "brand_type_id") {
                                            availableMaterialsFilterOptions.brand_type_id = e.value
                                        }

                                        if (args.dataField === "quantity") {
                                            availableMaterialsFilterOptions.quantity = e.value
                                        }

                                        if (availableMaterialsFilterOptions.quantity && availableMaterialsFilterOptions.brand_type_id) {
                                            loadAvailableMaterials();
                                        }
                                    }
                                }
                            },
                            onEditingStart: (e) => {
                                console.log("onEditingStart");
                                availableMaterialsFilterOptions = {
                                    "brand_type_id":  e.data.brand_type_id,
                                    "quantity":  e.data.quantity,
                                    "planning_object_id": currentSelectedProjectObject.id
                                };

                            },
                            onSaving: (e) => {
                                if (e.changes.length === 0) {
                                    e.changes.push({
                                        data: {},
                                        key: e.component.option("editing.editRowKey"),
                                        type: "update"
                                    });
                                }

                                if (e.changes[0].type !== "remove") {
                                    e.changes[0].data.materialsData = supplyPlanningEditingForm.getEditor("materialInputGrid").option("editing.changes");
                                    e.changes[0].data.supply_object_id = currentSelectedProjectObject.id
                                }
                            }
                        })
                    }

                    function loadAvailableMaterials() {
                        supplyPlanningEditingForm.getEditor("materialInputGrid").refresh();
                    }

                    function loadSupplyObjects() {
                        supplyObjectsDataSource.load().done((result) => {
                            createSupplyPlanningForm(result);
                        })
                    }

                    loadSupplyObjects();
                });
            </script>
@endsection
