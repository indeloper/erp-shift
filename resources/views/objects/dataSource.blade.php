<script>
    const objectsDataSource = new DevExpress.data.DataSource({
        store: new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "processed",
            load: function (loadOptions) {

                return $.getJSON("{{route('objects::index')}}",
                    {
                        data: JSON.stringify(loadOptions),
                    });
            },
            insert: function (values) {

                return $.ajax({
                    url: "{{route('objects::store')}}",
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
                    url: getUrlWithId("{{route('objects::update', ['id'=>'setId'])}}", key),
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
        })
    });

    const materialAccountingTypesDataSource = new DevExpress.data.CustomStore({
        loadMode: "raw",
        key: "id",
        load: function() {
            return $.getJSON("{{route('objects::getMaterialAccountingTypes')}}");
        }
    })

    const objectInfoByID = new DevExpress.data.DataSource({
        store: new DevExpress.data.CustomStore({
            loadMode: "raw",
            load: function (loadOptions) {
                // let rowId = dataGridInstance.option().focusedRowKey;
                return $.getJSON("{{route('objects::getObjectInfoByID')}}" + '?id=' + editingRowId);
            }
        })
    })

    const bitrixProjectsDataSource = new DevExpress.data.DataSource({
        store: new DevExpress.data.CustomStore({
            loadMode: "raw",
            load: function (loadOptions) {
                return $.getJSON("{{config('auth.BITRIX_PROJECTS_LIST_URL')}}");
            }
        })
    })

    bitrixProjectsDataSource.load().done(()=>{
        bitrixProjectsArray = bitrixProjectsDataSource.store().__rawData.result
        .map((item) => {
            item.ID = Number(item.ID)
            return item;
        })
    // Возвращает элемент для new_array
    })

</script>
