<script>
    //external

    const fuelContractorsStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function () {
            let url = "{{route('building::tech_acc::fuel::fuelFlow::'.'getFuelContractors')}}"
            return $.getJSON(url);
        }
    })
    fuelContractorsStore.load()

    const fuelConsumersStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function () {
            let url = "{{route('building::tech_acc::fuel::fuelFlow::'.'getFuelConsumers')}}"
            return $.getJSON(url);
        }
    })
    fuelConsumersStore.load()

    const fuelFlowTypesStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function () {
            let url = "{{route('building::tech_acc::fuel::fuelFlow::'.'getFuelFlowTypes')}}"
            return $.getJSON(url);
        }
    })
    fuelFlowTypesStore.load()

    const tankFuelIncomesStore = new DevExpress.data.DataSource({
        store: new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "processed",
            load: function (loadOptions) {

                loadOptions = setFuelFlowLoadOptionsFilter(loadOptions, 'income')

                return $.getJSON("{{route('building::tech_acc::fuel::fuelFlow::'.'resource.index')}}", {
                    data: JSON.stringify(loadOptions),
                });
            },
        })
    });

    const tankFuelOutcomesStore = new DevExpress.data.DataSource({
        store: new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "processed",
            load: function (loadOptions) {

                loadOptions = setFuelFlowLoadOptionsFilter(loadOptions, 'outcome')

                return $.getJSON("{{route('building::tech_acc::fuel::fuelFlow::'.'resource.index')}}", {
                    data: JSON.stringify(loadOptions),
                });
            },
        })
    });

    const tankFuelAdjustmentsStore = new DevExpress.data.DataSource({
        store: new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "processed",
            load: function (loadOptions) {

                loadOptions = setFuelFlowLoadOptionsFilter(loadOptions, 'adjustment')

                return $.getJSON("{{route('building::tech_acc::fuel::fuelFlow::'.'resource.index')}}", {
                    data: JSON.stringify(loadOptions),
                });
            },
        })
    });

    const externalEntitiesDataSource = new DevExpress.data.DataSource({
        store: new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "processed",

            insert: function (values) {

                return $.ajax({
                    url: "{{route('building::tech_acc::fuel::fuelFlow::'.'resource.store')}}",
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        data: JSON.stringify(values),
                        options: null
                    },
                    success: function (data, textStatus, jqXHR) {
                        execAfterSuccess('Данные успешно добавлены')
                    },
                })
            },

            update: function (key, values) {
                return $.ajax({
                    url: getUrlWithId("{{route('building::tech_acc::fuel::fuelFlow::'.'resource.update', ['id'=>'setId'])}}", key),
                    method: "PUT",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        data: JSON.stringify(values),
                        options: null
                    },
                    success: function (data, textStatus, jqXHR) {
                        execAfterSuccess('Данные успешно обновлены')
                    },
                })

            },

            remove: function (key) {

                return $.ajax({
                    url: getUrlWithId("{{route('building::tech_acc::fuel::fuelFlow::'.'resource.destroy', ['id'=>'setId'])}}", key),
                    method: "DELETE",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (data, textStatus, jqXHR) {
                        execAfterSuccess('Данные успешно удалены')
                    },
                })

            },
        })
    });

    const externalEntityInfoByID = new DevExpress.data.DataSource({
        store: new DevExpress.data.CustomStore({
            loadMode: "raw",
            load: function (loadOptions) {
                // return $.getJSON("{{route('objects::getObjectInfoByID')}}" + '?id=' + editingRowId);
                return $.getJSON(getUrlWithId("{{route('building::tech_acc::fuel::fuelFlow::'.'resource.show', ['id'=>'setId'])}}", externalEditingRowId));
            }
        })
    })

    function setFuelFlowLoadOptionsFilter(loadOptions, fuelFlowType) {
        if (!loadOptions.filter) {
            loadOptions.filter = []
        }

        loadOptions.filter.push(['fuel_tank_id', '=', editingRowId])
        loadOptions.filter.push("and")
        loadOptions.filter.push(["fuel_tank_flow_type_id", "=", fuelFlowTypesStore?.__rawData?.find(el => el.slug === fuelFlowType).id])

        return loadOptions
    }

    function execAfterSuccess(message) {
        if (choosedFormTab === 'fuelIncomes') {
            tankFuelIncomesStore.reload().done(() => {
                DevExpress.ui.notify(message, "success", 1000)
            })
        }
        if (choosedFormTab === 'fuelOutcomes') {
            tankFuelOutcomesStore.reload().done(() => {
                DevExpress.ui.notify(message, "success", 1000)
            })
        }
        if (choosedFormTab === 'fuelAdjustments') {
            tankFuelAdjustmentsStore.reload().done(() => {
                DevExpress.ui.notify(message, "success", 1000)
            })
        }
        // entitiesDataSource.reload()
    }
</script>
