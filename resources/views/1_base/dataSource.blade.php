
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
                        DevExpress.ui.notify("Данные успешно добавлены", "success", 1000)
                        entitiesDataSource.reload()
                        $('#entitiesListMobile').dxList('instance')?.reload() 
                    },
                })
            },

            update: function (key, values, isMobile=false) {   

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
                        entitiesDataSource.reload()
                        $('#entitiesListMobile').dxList('instance')?.reload()                        
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
                        entitiesDataSource.reload()
                        $('#entitiesListMobile').dxList('instance')?.reload()
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
    
</script>
