<script>
    const entitiesDataSource = new DevExpress.data.DataSource({
        store: new DevExpress.data.CustomStore({
            key: ["timeCardId", "rowType", "id"],
            loadMode: "raw",
            load: function (loadOptions) {
                loadOptions.userData = window.location.search
                return $.getJSON("{{route($routeNameFixedPart.'resource.index')}}" + window.location.search,
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
            }
        })
    });
</script>
