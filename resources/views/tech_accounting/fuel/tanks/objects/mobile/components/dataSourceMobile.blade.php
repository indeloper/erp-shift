<script>
    const externalEntitiesDataSource = new DevExpress.data.DataSource({
        store: new DevExpress.data.CustomStore({
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
                    success: function () {
                        // popupMobile.hide()
                        onInsertSuccess()
                    },
                })
            },
        })
    });

</script>
