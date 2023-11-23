<script>
    const entitiesDataSource = new DevExpress.data.DataSource({
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


    const fuelTanksStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function () {
            let url = "{{route('building::tech_acc::fuel::fuelFlow::'.'getFuelTanks')}}"
            return $.getJSON(url);
        }
    })
    fuelTanksStore.load()

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

    const fuelFlowTypesStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function () {
            let url = "{{route('building::tech_acc::fuel::fuelFlow::'.'getFuelFlowTypes')}}"
            return $.getJSON(url);
        }
    })
    fuelFlowTypesStore.load()

</script>
