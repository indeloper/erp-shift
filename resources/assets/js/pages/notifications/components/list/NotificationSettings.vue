<script setup>

import { DxButton as DxButtonAlias } from 'devextreme-vue';
import ModalSettings from './ModalSettings.vue';

const settingsModal = _ => {
  $('#notificationSettingsModal').modal('show')
}

</script>

<template>

  <DxButtonAlias
      icon="edit"
      @click="settingsModal"
      hint="Настроить уведомления"
      text="Настроить уведомления"
  />


  <ModalSettings/>

</template>

<style scoped>

</style>