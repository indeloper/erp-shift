<script>
    let dataSourceList = new DevExpress.data.DataSource({
        store: new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "processed",
            load: function (loadOptions) {

                filterOptions = loadOptions
                
                return $.getJSON("{{route('project-object-document.index')}}",
                    {
                        data: JSON.stringify(loadOptions),
                        projectObjectsFilter: JSON.stringify(customFilter['projectObjectsFilter']),
                        projectResponsiblesFilter: JSON.stringify(customFilter['projectResponsiblesFilter']),
                        customSearchParams: JSON.stringify(window.location.search.substring(1))

                    });
            },

            insert: function (values) {

                return $.ajax({
                    url: "{{route('project-object-document.store')}}",
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
                url: getUrlWithId("{{route('project-object-document.update', ['project_object_document'=>'setId'])}}", key),
                method: "PUT",
                headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                  data: JSON.stringify(values),
                  options: null
                },
                success: function (data, textStatus, jqXHR) {
                  DevExpress.ui.notify("Данные успешно обновлены", "success", 1000)
                },
              })

            },

            remove: function (key) {
              return $.ajax({
                url: getUrlWithId("{{route('project-object-document.destroy', ['project_object_document'=>'setId'])}}", key),
                method: "DELETE",
                headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (data, textStatus, jqXHR) {
                  DevExpress.ui.notify("Данные успешно удалены", "success", 1000)
                },
              })

            },
        })
    });

    let dataSourceListMobile = new DevExpress.data.DataSource({
        group: "project_object_short_name",
        store: new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "raw",
            load: function (loadOptions) {
                return $.getJSON("{{route('projectObjectDocument.indexMobile')}}", {
                    data: JSON.stringify(loadOptions),
                    projectResponsiblesFilter: JSON.stringify(customFilter['projectResponsiblesFilter'])
                });
            }
        })
    })

    let documentTypesStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function (loadOptions) {
            return $.getJSON("{{route('projectObjectDocument.getTypes')}}");
        }
    })

    documentTypesStore.load()

    let documentStatusesStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function (loadOptions) {
            let url = "{{route('projectObjectDocument.getStatuses')}}" + '?customSearchParams=' + window.location.search.substring(1)
            return $.getJSON(url);
        }
    })

    documentStatusesStore.load()

    let documentStatusesByTypeStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function (loadOptions) {
            return $.getJSON("{{route('projectObjectDocument.getStatuses')}}" + '?documentTypeId=' + editingRowTypeId);
        }
    })

    let optionsByTypeAndStatusStore = new DevExpress.data.CustomStore({
        loadMode: "raw",
        load: function (loadOptions) {
            return $.getJSON("{{route('projectObjectDocument.getOptionsByTypeAndStatus')}}" + '?documentTypeId=' + editingRowTypeId + '&statusId=' + editingRowNewStatusId);
        }
    })

    let projectObjectsStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function (loadOptions) {
            let isArchived = new URLSearchParams(window.location.search).get("showArchive");
            let isArchivedParam = isArchived ? "?is-archived=true" : "";
            return $.getJSON("{{route('projectObjectDocument.getProjectObjects')}}" + isArchivedParam);
        }
    })

    projectObjectsStore.load();

    let responsibles_pto = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function (loadOptions) {
            return $.getJSON("{{route('projectObjectDocument.getResponsibles', ['type'=>'pto'])}}" + '&id=' + editingRowId);
        }
    })

    let responsibles_foreman = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function (loadOptions) {
            return $.getJSON("{{route('projectObjectDocument.getResponsibles', ['type'=>'foreman'])}}" + '&id=' + editingRowId);
        }
    })

    let responsibles_manager = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function (loadOptions) {
            return $.getJSON("{{route('projectObjectDocument.getResponsibles', ['type'=>'manager'])}}" + '&id=' + editingRowId);
        }
    })

    let responsible_managers_and_pto = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function (loadOptions) {
            return $.getJSON("{{route('projectObjectDocument.getResponsibles', ['type'=>'managers_and_pto'])}}" + '&id=' + editingRowId);
        }
    })

    let responsible_managers_and_foremen = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function (loadOptions) {
            return $.getJSON("{{route('projectObjectDocument.getResponsibles', ['type'=>'managers_and_foremen'])}}" + '&id=' + editingRowId);
        }
    })

    let responsibles_all = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function (loadOptions) {
            return $.getJSON("{{route('projectObjectDocument.getResponsibles', ['type'=>'all'])}}");
        }
    })

    responsibles_all.load()

    let projectObjectDocumentInfoByID = new DevExpress.data.DataSource({
        store: new DevExpress.data.CustomStore({
            loadMode: "raw",
            load: function (loadOptions) {
                return $.getJSON("{{route('projectObjectDocument.getProjectObjectDocumentInfoByID')}}" + '?id=' + editingRowId);
            }
        })
    })

    let dataForLookupsAndFilters = new DevExpress.data.DataSource({
        store: new DevExpress.data.CustomStore({
            loadMode: "raw",
            load: function (loadOptions) {
                return $.getJSON("{{route('projectObjectDocument.getDataForLookupsAndFilters')}}");
            }
        })
    })

    let documentStatusesByTypeStoreDataSource = new DevExpress.data.DataSource({
        store: documentStatusesByTypeStore,
    })
</script>

