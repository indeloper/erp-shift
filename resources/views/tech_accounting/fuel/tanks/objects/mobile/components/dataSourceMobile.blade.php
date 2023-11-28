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

</script>