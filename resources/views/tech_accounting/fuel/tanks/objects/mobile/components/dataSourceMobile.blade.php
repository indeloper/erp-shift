<script>
    const fuelTanksStore = 
        new DevExpress.data.DataSource({
            store: new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "processed",
                load: function (loadOptions) {

                    return $.getJSON("{{route($routeNameFixedPart.'resource.index')}}",
                        {
                            data: JSON.stringify(loadOptions),
                        });
                },
            })
        });

    const fuelTanksResponsiblesStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function () {
            let url = "{{route($routeNameFixedPart.'getFuelTanksResponsibles')}}"
            return $.getJSON(url);
        }
    })

    fuelTanksResponsiblesStore.load()

    const projectObjectsStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function () {
            let url = "{{route($routeNameFixedPart.'getProjectObjects')}}"
            return $.getJSON(url);
        }
    })

    const fuelFlowTypesStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function () {
            let url = "{{route('building::tech_acc::fuel::fuelFlow::'.'getFuelFlowTypes')}}"
            return $.getJSON(url);
        }
    })
    fuelFlowTypesStore.load()

    const fuelContractorsStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function () {
            let url = "{{route('building::tech_acc::fuel::fuelFlow::'.'getFuelContractors')}}"
            return $.getJSON(url);
        }
    })
    fuelContractorsStore.load()

    const fuelConsumersStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function () {
            let url = "{{route('building::tech_acc::fuel::fuelFlow::'.'getFuelConsumers')}}"
            return $.getJSON(url);
        }
    })
    fuelConsumersStore.load()

    const externalEntitiesDataSource = new DevExpress.data.DataSource({
        store: new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "processed",
            insert: function (values) {
                return $.ajax({
                    url: "{{route('building::tech_acc::fuel::fuelFlow::'.'resource.store')}}",
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        data: JSON.stringify(values),
                        options: null
                    },
                    success: function () {
                        popupMobile.hide()
                    },
                })
            },
        })
    });


</script>