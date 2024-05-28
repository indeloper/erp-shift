import axios from 'axios';


export const getMaterialTypesData = async () => {
    try {
        const response = await axios.get('/strmaterials/transformation/get-materials-for');
        return response.data.data;
    } catch (error) {
        console.error("Error fetching material types data:", error);
        throw error;
    }
};

const materialServiceUrls = async () => {
    try {
        const response = await axios.get('/strmaterials/transformation/get-materials-service-urls');
        return response.data.data;
    } catch (error) {
        console.error("Error fetching material types data:", error);
        throw error;
    }
}

let materialStandardsListStore = new DevExpress.data.CustomStore({
    key: "id",
    loadMode: "raw",
    load: function (loadOptions) {
        return $.getJSON(materialServiceUrls.materialsStandardsListexRoute,
            {data: JSON.stringify({dxLoadOptions: loadOptions})});
    },
});
export let materialStandardsListDataSource = new DevExpress.data.DataSource({
    key: "id",
    store: materialStandardsListStore
})
