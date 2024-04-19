<script setup>

import { ref } from 'vue';
import { DxColumn, DxDataGrid } from 'devextreme-vue/data-grid';
import { DxCheckBox } from 'devextreme-vue/check-box';
import DataSource from 'devextreme/data/data_source';
import CustomStore from 'devextreme/data/custom_store';

const store = new CustomStore({
  key: 'id',
  load: (loadOptions) => {
    return loadNotificationItems()
        .then(response => {
          return {
            data: response.data.data,
            summary: response.data.data.length,
          };
        });
  },
});

const dataSource = ref(new DataSource({
  remoteOperations: true,
  store: store,
}));


const loadNotificationItems = _ => {
  const action = document.querySelector('#notifications-items').value;

  return axios.get(action)
}

</script>

<template>
  <div class="modal" id="notificationSettingsModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Настройка уведомлений</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <DxDataGrid
              :data-source="dataSource"
              :word-wrap-enabled="true"
              key-expr="id"
              :show-borders="true"
          >
            <DxColumn caption="Уведомление" data-field="description"/>


            <DxColumn
                caption="Mail"
                cell-template="mail-template"
            />

            <template #mail-template="{ data: templateOptions }">
              <DxCheckBox :value="templateOptions.data.mail" />
            </template>

            <DxColumn
                caption="Telegram"
                cell-template="telegram-template"
            />

            <template #telegram-template="{ data: templateOptions }">
              <DxCheckBox :value="templateOptions.data.telegram" />
            </template>

            <DxColumn
                caption="System"
                cell-template="system-template"
            />

            <template #system-template="{ data: templateOptions }">
              <DxCheckBox :value="templateOptions.data.system" />
            </template>


          </DxDataGrid>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            Закрыть
          </button>
          <button type="button" class="btn btn-primary">
            Сохранить
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>

</style>