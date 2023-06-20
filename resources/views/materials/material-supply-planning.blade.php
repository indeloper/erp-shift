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
            $("div.content").children(".container-fluid.pd-0-360").removeClass();
        });

        $(function () {
            let dataSourceLoadOptions = {};
            let availableMaterialsFilterOptions = {};
            let currentSelectedProjectObject = {};


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
                },
                /*update(key, values) {
                    return sendRequest(`${URL}/UpdateOrder`, 'PUT', {
                        key,
                        values: JSON.stringify(values),
                    });
                },
                remove(key) {
                    return sendRequest(`${URL}/DeleteOrder`, 'DELETE', {
                        key,
                    });
                },*/
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
                                            currentSelectedProjectObject = e.itemData.planningObjectData;
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
                   type: "summaryTab"
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
                }
            }

            function getSupplyPlanningEditForm() {
                return {
                    colCount: 3,
                    items: [
                        {
                            dataField: "brand_type_id",
                            label: {
                                text: "Тип шпунта"
                            },
                            editorType: "dxSelectBox",
                            editorOptions: {
                                dataSource: brandTypesDataSource,
                                displayExpr: 'name',
                                valueExpr: 'id'
                            },
                            validationRules: [{ type: "required" }]
                        },
                        {
                            dataField: "common_quantity",
                            dataType: "number",
                            label: {
                                text: "Длина (м.п)"
                            },
                            validationRules: [{ type: "required" }]
                            //editorType: "dxNumberBox",
                            /*editorOptions: {
                                items: [],
                                openOnFieldClick: true,
                                onKeyDown: function(e) {
                                    console.log(e);
                                    let key = e.event.key;

                                    if (/^[0-9.,\b\t]$/.test(key)) {
                                        let value = e.component.option("value");
                                        if ((key === "." || key === ",") && (value.includes(",") || value.includes(".")) || (key === "." && value === "")) {
                                            e.event.preventDefault();
                                        }
                                        return;
                                    }
                                    e.event.preventDefault();
                                }
                            }*/
                        },
                        {
                            dataField: "supply_planned_weight",
                            //editorType: "dxNumberBox",
                            label: {
                                text: "Планируемый объем поставки (т)"
                            },
                            validationRules: [{ type: "required" }]
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
                                        loadMode: "raw",
                                        load: (loadOptions) => {
                                            loadOptions.userData = availableMaterialsFilterOptions;
                                            return $.getJSON(`{{route('materials.supply-planning.available-material-list')}}`,
                                                {
                                                    loadOptions: JSON.stringify(loadOptions),
                                                });
                                        }
                                    }),
                                    key: "id"
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
                                        customizeText: (e) => {
                                            return new Intl.NumberFormat('ru-RU').format(e.value);
                                        },
                                        editorOptions: {
                                            min: 0,
                                        }
                                    },
                                    {
                                        dataField: "reserved_weight",
                                        caption: "Бронируемый вес (т)",
                                        customizeText: (e) => {
                                            return new Intl.NumberFormat('ru-RU').format(e.value);
                                        }
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
                    ],
                    onFieldDataChanged: (e) => {
                        let formData = e.component.option("formData");

                        if (formData.brand_type_id && formData.common_quantity) {
                            availableMaterialsFilterOptions = {
                                "brand_type_id":  formData.brand_type_id,
                                "quantity":  formData.common_quantity
                            }

                            e.component.getEditor("materialInputGrid").refresh();
                        }
                    }
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

            function getObjectContentTemplate(data) {
                let planningObjectDataSource = new DevExpress.data.DataSource({
                    loadMode: "raw",
                    /*map: function (dataItem) {
                        return {
                            brand_with_quantity: dataItem.brand_type_name + " " + new Intl.NumberFormat('ru-RU').format(dataItem.quantity) + " м.п"
                        }
                    },*/
                    store: new DevExpress.data.CustomStore({
                        key: "id",
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
                        /*update: function (key, values) {
                            return $.ajax({
                                url: "{{route('labor-safety.orders-and-requests.update')}}",
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
                                error: function (jqXHR, textStatus, errorThrown) {
                            DevExpress.ui.notify("При сохранении данных произошла ошибка", "error", 5000)
                            });
                        }*/
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
                        visible: false
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
                        allowDeleting: false,
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

                                        console.log("groupCellTemplate options", options);

                                        options.component.editRow(4);

                                        let editForm = $(".dx-datagrid-edit-popup-form").dxForm("instance");
                                        editForm.option("formData", {
                                            brand_type_id: options.data.items[0].brand_type_id,
                                            common_quantity: options.data.items[0].quantity
                                        });
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
                        },
                        {
                            dataField: "weight",
                            caption: "Забронированный вес",
                            customizeText: (e) => {
                                return new Intl.NumberFormat('ru-RU').format(e.value) + " т";
                            },
                        },
                        {
                            type: 'buttons',
                            width: 150,
                            //visible: !isRowReadOnly(requestStatusId),
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
                                //if (!isRowReadOnly(requestStatusId)) {
                                    $('<div>')
                                        .appendTo(container)
                                        .dxButton({
                                            text: "Добавить",
                                            icon: "fas fa-plus",
                                            onClick: (e) => {
                                                options.component.addRow();
                                            }
                                        })
                                //}
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
                    onSaving: (e) => {
                        let editForm = $(".dx-datagrid-edit-popup-form").dxForm("instance");
                        console.log("editForm", editForm);
                        e.changes[0].data.brand_type_id = editForm.getEditor("brand_type_id").option("value");
                        e.changes[0].data.quantity = editForm.getEditor("common_quantity").option("value");
                        e.changes[0].data.planned_weight = editForm.getEditor("supply_planned_weight").option("value");
                        e.changes[0].data.materialsData = editForm.getEditor("materialInputGrid").option("editing.changes");
                        e.changes[0].data.supply_object_id = currentSelectedProjectObject.id
                    }
                })
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
