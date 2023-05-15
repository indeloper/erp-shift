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
    <div id="addNewProjectObjectPopupContainer">
@endsection

@section('js_footer')
    <script>
        let dataSourceLoadOptions = {};

        let objectsList = [];

        $(function () {
            $("div.content").children(".container-fluid.pd-0-360").removeClass();
        });

        $(function () {
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

            function createSupplyPlanningForm() {
                let supplyPlanningForm = $("#formContainer").dxForm({
                    items: [
                        {
                            itemType: "group",
                            caption: "Планирование поставок материалов",
                            cssClass: "material-supply-planning-grid",
                            items: [
                                {
                                    itemType: "tabbed",
                                    tabs: getPlanningObjectsTabArray(),
                                    tabPanelOptions: {
                                        deferRendering: false,
                                        // onTitleClick: (e) => {
                                        //     //createObjectContent(objectId);
                                        // },
                                    }
                                }
                            ]
                        }
                    ]
                }).dxForm('instance');

                @if(Auth::user()->can('material_supply_planning_editing'))
                createGridGroupHeaderButtons();
                @endcan

                return supplyPlanningForm;
            }

            function createGridGroupHeaderButtons() {
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

            function calculateNeededWeight(rowData) {
                let weight = rowData.amount * rowData.quantity * rowData.standard_weight;

                if (isNaN(weight)) {
                    weight = 0;
                }

                if (rowData.remains_weight <= weight) {
                    return Math.round((weight - rowData.remains_weight) * 1000) / 1000;
                }
            }

            function getPlanningObjectsTabArray() {
               let summaryTab = {
                   title: "Сводка",
                   icon: "fas fa-table",
                   type: "summaryTab"
               }

               console.log("[summaryTab, ...objectsList]", [summaryTab, ...objectsList])
               return [summaryTab, ...objectsList];
            }

            const newObjectValidationGroupName = "newObjectValidationForm";

            const createPopupTemplate = (formData) => () => {
                const labelTemplate = (iconName) => (data) => $(`<div><i class="dx-icon dx-icon-${iconName}"></i>${data.text}</div>`);
                return $("<div>").dxForm({
                    formData: formData,
                    validationGroup: newObjectValidationGroupName,
                    showColonAfterLabel: true,
                    items: [
                        {
                            dataField: "object_name",
                            validationRules: [{type: "required", message: `Поле "Наименование объекта" обязательно для заполнения`}],
                            label: {
                                template: labelTemplate("far far-kaaba"),
                                text: "Наименование объекта"
                            },
                        },
                    ],
                });
            };

            const projectObjectNamePopup = $("#addNewProjectObjectPopupContainer").dxPopup({
                hideOnOutsideClick: true,
                showCloseButton: true,
                height: "auto",
            }).dxPopup("instance");

            const confirmItem = {
                widget: "dxButton",
                location: "after",
                toolbar: "bottom",
                options: {
                    text: "ОК",
                    type: "normal",
                },
            };

            const cancelItem = {
                widget: "dxButton",
                location: "after",
                toolbar: "bottom",
                options: {
                    text: "Отмена",
                    onClick: () => {
                        projectObjectNamePopup.hide();
                    },
                },
            };

            const showProjectObjectNamePopup = (isNewRecord, data) => {
                const contentTemplate = createPopupTemplate(data);
                projectObjectNamePopup.option({
                    title: isNewRecord ? "Добавить планируемый объект" : "Редактировать планируемый объект",
                    contentTemplate,
                    toolbarItems: [
                        {
                            ...confirmItem,
                            onClick: () => {
                                let result = DevExpress.validationEngine.validateGroup(newObjectValidationGroupName);

                                if (!result.isValid)
                                    return;

                                objectsList.push(
                                    {
                                        title: data.object_name,
                                        type: "objectTab",
                                        template: (data, index, container) => {
                                            console.log("data", data);
                                            console.log("index", index);
                                            console.log("container", container);
                                            return getObjectContentTemplate(data);
                                        }
                                    }
                                );

                                projectObjectNamePopup.hide();

                                createSupplyPlanningForm();
                            },
                        },
                        cancelItem,
                    ],
                    visible: true,
                });
            };

            function getObjectContentTemplate(data) {
                let objectDataSource = new DevExpress.data.DataSource({
                    store: new DevExpress.data.CustomStore({
                        key: "id",
                        load: function (loadOptions) {
                            return $.getJSON("{{route('labor-safety.orders-and-requests.list')}}",
                                {
                                    loadOptions: JSON.stringify(loadOptions),
                                });
                        },
                        insert: function (values) {
                            return $.ajax({
                                url: "{{route('labor-safety.orders-and-requests.store')}}",
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
                            });
                        }
                    })
                });

                return $(`<div>`).dxDataGrid({
                    dataSource: objectDataSource,
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
                        //popup: getRequestEditingPopup(),
                        //form: getRequestEditForm(),
                    },
                    remoteOperations: true,
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
                            dataField: "planned_tongue",
                            caption: "Шпунт",
                            groupIndex: 0
                        },
                        {
                            dataField: "project_object",
                            caption: "Объект"
                        },
                        {
                            dataField: "planned_length",
                            caption: "Планируемая длина (м.п)"
                        },
                        {
                            dataField: "planned_weight",
                            caption: "Планируемый вес (тн)"
                        },
                        {
                            type: 'buttons',
                            width: 150,
                            //visible: !isRowReadOnly(requestStatusId),
                            buttons: [
                                {
                                    name: 'edit',
                                    visible: (e) => {
                                        //
                                    }
                                },
                                {
                                    name: 'delete',
                                    visible: (e) => {
                                        //
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
                                                //options.component.option("editing.popup", getWorkersEditingPopup());
                                                //options.component.option("editing.form", getWorkersListEditForm());
                                                options.component.addRow();
                                            }
                                        })
                                //}
                            }
                        }
                    ]
                })
            }

            createSupplyPlanningForm();

        });
    </script>
@endsection
