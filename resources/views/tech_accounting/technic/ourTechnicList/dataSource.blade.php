
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

    const technicCategoriesStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function () {        
            let url = "{{route('building::tech_acc::technic::'.'getTechnicCategories')}}" 
            return $.getJSON(url);
        }
    })

    const technicResponsiblesStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function () {        
            let url = "{{route('building::tech_acc::technic::'.'getTechnicResponsibles')}}" 
            return $.getJSON(url);
        }
    })

    const technicBrandsStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function () {        
            let url = "{{route($routeNameFixedPart.'getTechnicBrands')}}" 
            return $.getJSON(url);
        }
    })

    const technicModelsStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function () {        
            let url = "{{route($routeNameFixedPart.'getTechnicModels')}}" 
            return $.getJSON(url);
        }
    })
    

</script>