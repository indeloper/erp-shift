<script>
import { DxButton } from 'devextreme-vue';

export default {
  name: 'List',
  components: {
    DxButton
  },
  data() {
    return {
      notifications: [],
      links: {}
    }
  },
  mounted() {
    const action = document.querySelector('#load-notification-route').value

    this.loadNotifications(action);


  },
  methods: {
    loadNotifications(action) {
      axios.get(
          action
      ).then(response => {
        const responseData = response.data;

        this.notifications = responseData.data;
        this.links = responseData.links
      });

    },
    kek() {
      alert('Hello world!')
    }
  }
};

</script>

<template>
  <div class="table-responsive" v-if="notifications.length">
    <DxButton
        text="Click me"
        @click="kek"
    />
    <table class="table table-hover mobile-table">
      <thead>
      <tr>
        <th>Уведомления</th>
        <th>Контрагент</th>
        <th style="max-width: 500px">Адрес объекта</th>
        <th>Дата</th>
        <th class="text-right" id="actions">Действия</th>
      </tr>
      </thead>
      <tbody class="notify_place">
      <tr
          v-for="notification in notifications"
          class="notify" :class="{'bg-color-snow': notification.is_seen, 'notSeen': !notification.is_seen}">


        <td data-label="Уведомления">{{ notification.name }}</td>

        <td data-label="Контрагент">
          <template v-if="notification.contractor">
            {{ notification.contractor.short_name }}
          </template>
          <template v-else>
            Не указан
          </template>
        </td>
        <td data-label="Адрес объекта">
          <template v-if="notification.object">
            {{ notification.object.address }}
          </template>
          <template v-else>
            Не указан
          </template>
        </td>
        <td data-label="Дата">
          {{ notification.created_at }}
        </td>
        <td data-label="" class="td-actions text-right actions">

          <a rel="tooltip" class="btn-danger btn-link"
             data-original-title="Удалить уведомление">
            <i class="fa fa-times"></i>
          </a>
        </td>
      </tr>
      </tbody>
    </table>
  </div>
  <div class="col-md-12" style="margin-top:20px; margin-bottom: 10px; padding: 0 10px 0 0;">
    <div class="right-edge fix-pagination">
      <div class="page-container">
        <a v-if="links.prev" @click.prevent="loadNotifications(links.prev)" class="btn btn-round btn-outline btn-sm add-btn mr-2">
          Назад
        </a>
        <a v-if="links.next" @click.prevent="loadNotifications(links.next)" class="btn btn-round btn-outline btn-sm add-btn">
          Вперед
        </a>
      </div>
    </div>
  </div>
</template>

<style scoped>

</style>