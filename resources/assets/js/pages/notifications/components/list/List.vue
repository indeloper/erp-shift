<script setup>

import CustomStore from 'devextreme/data/custom_store';
import { DxColumn, DxDataGrid, DxPaging } from 'devextreme-vue/data-grid';
import { ref } from 'vue';
import DataSource from 'devextreme/data/data_source';

const action = document.querySelector('#load-notification-route').value;

const notifications = ref([]);
const links = ref({});
const meta = ref({});

const defaultPerPage = 20;

const store = new CustomStore({
  key: 'id',
  load: (loadOptions) => {
    const loadPage = parseInt(loadOptions.skip / loadOptions.take) + 1;
    const loadAction = action + '?page=' + loadPage;

    let sort = null;

    if (loadOptions.sort?.length) {
      sort = {
        'selector': loadOptions.sort[0].selector,
        'direction': loadOptions.sort[0].desc ? 'desc' : 'asc',
      };
    }

    return loadNotifications(loadAction, sort)
        .then(response => {
          const responseData = response.data;

          links.value = responseData.links;
          meta.value = responseData.meta;

          return {
            data: responseData.data,
            summary: responseData.meta.total,
            totalCount: meta.value.total,
          };
        });
  },
});

const dataSource = ref(new DataSource({
  remoteOperations: true,
  store: store,
}));

const loadNotifications = (action, sort = null) => {
  return axios.get(
      action,
      {
        params: {
          sort_selector: sort?.selector,
          sort_direction: sort?.direction,
        },
      },
  );
};


</script>

<template>
  <DxDataGrid
      v-if="notifications"
      :data-source="dataSource"
      :remote-operations="true"
      :word-wrap-enabled="true"
      key-expr="id"
      :show-borders="true"
  >
    <DxColumn caption="Уведомления" data-field="name"/>
    <DxColumn caption="Контрагент" data-field="contractor.short_name"/>
    <DxColumn style="max-width: 500px" caption="Адрес объекта" data-field="object.address"/>
    <DxColumn caption="Дата" data-field="created_at" data-type="data"/>

    <DxPaging
        :page-size="meta.per_page"
        :total="meta.total"
    />
  </DxDataGrid>
</template>

<style scoped>

</style>