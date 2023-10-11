
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

    const entityInfoByID = new DevExpress.data.DataSource({
        store: new DevExpress.data.CustomStore({
            loadMode: "raw",
            load: function (loadOptions) {
                // return $.getJSON("{{route('objects::getObjectInfoByID')}}" + '?id=' + editingRowId);
                return $.getJSON(getUrlWithId("{{route($routeNameFixedPart.'resource.show', ['id'=>'setId'])}}", editingRowId));
            }
        })
    })

    const fuelResponsiblesStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function () {        
            let url = "{{route('building::tech_acc::fuel::fuelFlow::'.'getFuelResponsibles')}}" 
            return $.getJSON(url);
        }
    })

    const fuelTanksStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function () {        
            let url = "{{route('building::tech_acc::fuel::fuelFlow::'.'getFuelTanks')}}" 
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
    
    const fuelTanksResponsiblesStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function () {        
            let url = "{{route($routeNameFixedPart.'getFuelTanksResponsibles')}}" 
            return $.getJSON(url);
        }
    })

    const companiesStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function () {        
            let url = "{{route($routeNameFixedPart.'getCompanies')}}" 
            return $.getJSON(url);
        }
    })


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
                if(!loadOptions.filter) {
                    loadOptions.filter = []
                }
                loadOptions.filter.push(['fuel_tank_id', '=', editingRowId])
                loadOptions.filter.push("and")
                loadOptions.filter.push(["fuel_tank_flow_type_id","=", fuelFlowTypesStore.__rawData.find(el=>el.slug==='income').id])

                return $.getJSON("{{route('building::tech_acc::fuel::fuelFlow::'.'resource.index')}}",
                    {
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
                if(!loadOptions.filter) {
                    loadOptions.filter = []
                }
                loadOptions.filter.push(['fuel_tank_id', '=', editingRowId])
                loadOptions.filter.push("and")
                loadOptions.filter.push(["fuel_tank_flow_type_id","=", fuelFlowTypesStore.__rawData.find(el=>el.slug==='outcome').id])

                return $.getJSON("{{route('building::tech_acc::fuel::fuelFlow::'.'resource.index')}}",
                    {
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
                if(!loadOptions.filter) {
                    loadOptions.filter = []
                }
                loadOptions.filter.push(['fuel_tank_id', '=', editingRowId])
                loadOptions.filter.push("and")
                loadOptions.filter.push(["fuel_tank_flow_type_id","=", fuelFlowTypesStore.__rawData.find(el=>el.slug==='adjustment').id])

                return $.getJSON("{{route('building::tech_acc::fuel::fuelFlow::'.'resource.index')}}",
                    {
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
                        if(choosedFormTab === 'fuelIncomes') {
                            tankFuelIncomesStore.reload().done(()=>{
                                DevExpress.ui.notify("Данные успешно добавлены", "success", 1000)
                            })      
                        }
                        if(choosedFormTab === 'fuelOutcomes') {
                            tankFuelOutcomesStore.reload().done(()=>{
                                DevExpress.ui.notify("Данные успешно добавлены", "success", 1000)
                            })      
                        }
                        if(choosedFormTab === 'fuelAdjustmens') {
                            tankFuelAdjustmentsStore.reload().done(()=>{
                                DevExpress.ui.notify("Данные успешно добавлены", "success", 1000)
                            })      
                        }
                        
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

                        tankFuelIncomesStore.reload()
                        // if(choosedFormTab === 'fuelIncomes') {
                        //     tankFuelIncomesStore.reload().done(()=>{
                        //         DevExpress.ui.notify("Данные успешно обновлены", "success", 1000)
                        //     })      
                        // }
                        // if(choosedFormTab === 'fuelOutcomes') {
                        //     tankFuelOutcomesStore.reload().done(()=>{
                        //         DevExpress.ui.notify("Данные успешно обновлены", "success", 1000)
                        //     })      
                        // }
                        // if(choosedFormTab === 'fuelAdjustmens') {
                        //     tankFuelAdjustmentsStore.reload().done(()=>{
                        //         DevExpress.ui.notify("Данные успешно обновлены", "success", 1000)
                        //     })      
                        // }
                        
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
                        if(choosedFormTab === 'fuelIncomes') {
                            tankFuelIncomesStore.reload().done(()=>{
                                DevExpress.ui.notify("Данные успешно удалены", "success", 1000)
                            })      
                        }
                        if(choosedFormTab === 'fuelOutcomes') {
                            tankFuelOutcomesStore.reload().done(()=>{
                                DevExpress.ui.notify("Данные успешно удалены", "success", 1000)
                            })      
                        }
                        if(choosedFormTab === 'fuelAdjustmens') {
                            tankFuelAdjustmentsStore.reload().done(()=>{
                                DevExpress.ui.notify("Данные успешно удалены", "success", 1000)
                            })      
                        }
                        
                    },
                })

            },
        })
    });

</script>
