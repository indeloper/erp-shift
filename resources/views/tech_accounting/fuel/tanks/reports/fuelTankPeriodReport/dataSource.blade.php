<script>
    // const entitiesDataSource = new DevExpress.data.DataSource({
    //     store: new DevExpress.data.CustomStore({
    //         key: "id",
    //         loadMode: "processed",
    //         load: function (loadOptions) {

    //             return $.getJSON("{{route($routeNameFixedPart.'resource.index')}}",
    //                 {
    //                     data: JSON.stringify(loadOptions),
    //                 });
    //         },
    //     })
    // });


    const fuelTanksStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function () {
            let url = "{{route('building::tech_acc::fuel::fuelFlow::'.'getFuelTanks')}}"
            return $.getJSON(url);
        }
    });
    fuelTanksStore.load()

    const fuelTanksResponsiblesStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function () {
            let url = "{{route('building::tech_acc::fuel::tanks::'.'getFuelTanksResponsibles')}}"
            return $.getJSON(url);
        }
    });
    fuelTanksResponsiblesStore.load()

    const projectObjectsStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function () {
            let url = "{{route('building::tech_acc::fuel::tanks::'.'getProjectObjects')}}"
            return $.getJSON(url);
        }
    });
    projectObjectsStore.load()

</script>