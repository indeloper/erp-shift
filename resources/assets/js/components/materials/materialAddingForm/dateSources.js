import DevExpress from "devextreme";

export let accountingTypesSource = new DevExpress.data.CustomStore({
    key: "id",
    loadMode: "raw",
    load: function (loadOptions) {
        return $.getJSON('/strmaterials/material-accounting-type/list',
            {data: JSON.stringify({dxLoadOptions: loadOptions})});
    },
});


export let materialStandardsSource = new DevExpress.data.CustomStore({
    key: "id",
    loadMode: "raw",
    load: function (loadOptions) {
        return $.getJSON('/strmaterials/material-standard/list',
            {data: JSON.stringify({dxLoadOptions: loadOptions})});
    },
});

export let materialTypesSource = new DevExpress.data.CustomStore({
    key: "id",
    loadMode: "raw",
    load: function (loadOptions) {
        return $.getJSON('/strmaterials/material-type/list',
            {data: JSON.stringify({dxLoadOptions: loadOptions})});
    },
});

export let measureUnitsSource = new DevExpress.data.CustomStore({
    key: "id",
    loadMode: "raw",
    load: function (loadOptions) {
        return $.getJSON('/material/measure-units/list',
            {data: JSON.stringify({dxLoadOptions: loadOptions})});
    },
});
