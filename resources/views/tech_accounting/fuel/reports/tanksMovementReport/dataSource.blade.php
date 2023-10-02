
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
    });

    const projectObjectsStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function () {        
            let url = "{{route('building::tech_acc::fuel::reports::'.'getProjectObjects')}}" 
            return $.getJSON(url);
        }
    })

    
</script>
