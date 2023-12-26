
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

    const resources = JSON.parse("{{$resources}}".replace(/&quot;/g,'"'));

    const technicCategoriesStore = resources.technicCategories;
    const technicMovementStatusesStore = resources.technicMovementStatuses;
    const technicsListStore = resources.technicsList;
    const technicResponsiblesByTypesStore = resources.technicResponsiblesByTypes;
    const technicResponsiblesAllTypesStore = resources.technicResponsiblesAllTypes;
    // const technicCarriersStore = resources.technicCarriers;
    // const projectObjectsStore = resources.projectObjects;

    const technicCarriersStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function () {        
            let url = "{{route($routeNameFixedPart.'getTechnicCarriers')}}" 
            return $.getJSON(url);
        }
    })

    const projectObjectsStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function () {        
            let url = "{{route($routeNameFixedPart.'getProjectObjects')}}" 
            return $.getJSON(url);
        }
    })
   
</script>
