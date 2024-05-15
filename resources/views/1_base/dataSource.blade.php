
<script>
    const entitiesDataSource = new DevExpress.data.DataSource({
        store: new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "processed",
            reshapeOnPush: true,
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

               @php
                   // TODO: // FIX URL !!!

    //                   return $.ajax({
    //                       url: getUrlWithId("{{route($routeNameFixedPart.'resource.update', ['id'=>'setId'])}}", key),
    //                       method: "PUT",
    //                       headers: {
    //                           'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //                       },
    //                       data: {
    //                           data: JSON.stringify(values),
    //                           options: null
    //                       },
    //                       success: function (data, textStatus, jqXHR) {
    //                           DevExpress.ui.notify("Данные успешно обновлены", "success", 1000)
    //                           // $('#entitiesListMobile').dxList('instance')?.reload()
    //                       },
    //                   })
               @endphp

            },
            remove: function (key) {

                @php
                    // TODO: // FIX URL !!!

    //                    return $.ajax({
    //                        url: getUrlWithId("{{route($routeNameFixedPart.'resource.destroy', ['id'=>'setId'])}}", key),
    //                        method: "DELETE",
    //                        headers: {
    //                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //                        },
    //                        success: function (data, textStatus, jqXHR) {
    //                            DevExpress.ui.notify("Данные успешно удалены", "success", 1000)
    //                            entitiesDataSource.reload()
    //                            $('#entitiesListMobile').dxList('instance')?.reload()
    //                        },
    //                    })
                @endphp

            },
            byKey: function (key) {
                let d = new $.Deferred();
                @php
                    // TODO: // FIX URL !!!

    //                    $.get(getUrlWithId("{{route($routeNameFixedPart.'resource.show', ['id'=>'setId'])}}", key))
    //                        .done(function (dataItem) {
    //                            d.resolve(dataItem);
    //                        });
                @endphp
                return d.promise();
            }
        })
    });

    let entityInfoByID = new DevExpress.data.DataSource({
        store: new DevExpress.data.CustomStore({
            loadMode: "raw",
            load: function (loadOptions) {
              @php
                  // TODO: // FIX URL !!!

    //                  return $.getJSON(getUrlWithId("{{route($routeNameFixedPart.'resource.show', ['id'=>'setId'])}}", editingRowId));

              @endphp
            }
        })
    })

</script>
