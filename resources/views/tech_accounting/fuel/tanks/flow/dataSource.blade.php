<script>
    const entitiesDataSource = new DevExpress.data.DataSource({
        store: new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "processed",
            load: function (loadOptions) {

                currentLoadOptions = loadOptions

                return $.getJSON("{{route($routeNameFixedPart.'resource.index')}}",
                    {
                        data: JSON.stringify(loadOptions),
                    });
            },
            insert: function (values) {

                return $.ajax({
                    url: "{{route($routeNameFixedPart.'resource.store')}}",
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        data: JSON.stringify(values),
                        options: null
                    },
                    success: function (data, textStatus, jqXHR) {
                        entitiesDataSource.reload()
                        DevExpress.ui.notify("Данные успешно добавлены", "success", 1000)
                    },
                })
            },

            update: function (key, values) {
                return $.ajax({
                    url: getUrlWithId("{{route($routeNameFixedPart.'resource.update', ['id'=>'setId'])}}", key),
                    method: "PUT",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        data: JSON.stringify(values),
                        options: null
                    },
                    success: function (data, textStatus, jqXHR) {
                        entitiesDataSource.reload()
                        DevExpress.ui.notify("Данные успешно обновлены", "success", 1000)
                    },
                })

            },

            remove: function (key) {

                return $.ajax({
                    url: getUrlWithId("{{route($routeNameFixedPart.'resource.destroy', ['id'=>'setId'])}}", key),
                    method: "DELETE",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (data, textStatus, jqXHR) {
                        entitiesDataSource.reload()
                        DevExpress.ui.notify("Данные успешно удалены", "success", 1000)
                    },
                })

            },

        })
    });

    let entityInfoByID = new DevExpress.data.DataSource({
        store: new DevExpress.data.CustomStore({
            loadMode: "raw",
            load: function (loadOptions) {
                return $.getJSON(getUrlWithId("{{route($routeNameFixedPart.'resource.show', ['id'=>'setId'])}}", editingRowId));
            }
        })
    })


    const fuelResponsiblesStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function () {
            let url = "{{route($routeNameFixedPart.'getFuelResponsibles')}}"
            return $.getJSON(url);
        }
    })

    const fuelTanksStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function () {
            let url = "{{route($routeNameFixedPart.'getFuelTanks')}}"
            return $.getJSON(url);
        }
    })

    const fuelContractorsStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function () {
            let url = "{{route($routeNameFixedPart.'getFuelContractors')}}"
            return $.getJSON(url);
        }
    })

    const fuelConsumers = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function () {
            let url = "{{route($routeNameFixedPart.'getFuelConsumers')}}"
            return $.getJSON(url);
        }
    })

    fuelConsumers.load()

    const fuelFlowTypesStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function () {
            let url = "{{route($routeNameFixedPart.'getFuelFlowTypes')}}"
            return $.getJSON(url);
        }
    })
    
    fuelFlowTypesStore.load()

    const projectObjectsStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function () {
            let url = "{{route('building::tech_acc::fuel::tanks::'.'getProjectObjects')}}"
            return $.getJSON(url);
        }
    })

    const companiesStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function () {
            let url = "{{route('building::tech_acc::fuel::tanks::'.'getCompanies')}}"
            return $.getJSON(url);
        }
    })

</script>
