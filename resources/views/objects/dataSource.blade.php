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
        load: function () {
            return $.getJSON("{{route('objects::getMaterialAccountingTypes')}}");
        }
    })

    const objectInfoByID = new DevExpress.data.DataSource({
        store: new DevExpress.data.CustomStore({
            loadMode: "raw",
            load: function (loadOptions) {
                return $.getJSON("{{route('objects::getObjectInfoByID')}}" + '?id=' + editingRowId);
            }
        })
    })


    let bitrixProjectsArray = [];

    const getBitrixProjects = async () => {
        const url = "{{config('auth.BITRIX_PROJECTS_LIST_URL')}}";

        const bitrixRequest = async (requestUrl) => {
            let response = await fetch(requestUrl);
            return await response.json()
        }

        const firstRequestResult = await bitrixRequest(url);
        bitrixProjectsArray = bitrixProjectsArray.concat(firstRequestResult.result);

        const batchNumber = Math.ceil(await firstRequestResult.total / 50);
        for (let i = 1; i < batchNumber; i++) {
            let requestResult = await bitrixRequest(url + '&start=' + i * 50);
            bitrixProjectsArray = bitrixProjectsArray.concat(requestResult.result);
        }
    }

    const getBitrixProjectsAndPrepareData = async () => {
        await getBitrixProjects()
        bitrixProjectsArray.map((item) => {
            item.ID = Number(item.ID)
            return item;
        });
    }

    getBitrixProjectsAndPrepareData()

</script>
