<script>
export default {
  name: 'Header',
  data() {
    return {
      title: null,
      url: null,
      user_url: null,
      logout_url: null,
      messages_index_url: null,
      user: null,
      notifications_index_url: null,
      messages_count: 0,
      notifications_count: 0,
    }
  },
  mounted() {
    this.initData()
    this.loadUser()
  },
  methods: {
    loadUser() {
      axios.get(
          this.user_url
      ).then(response => {
        const responseData = response.data

        this.user = responseData.data
      })
    },
    initData() {
      this.messages_count = parseInt(document.querySelector('#messages_count')
          .value)

      this.notifications_count = parseInt(document.querySelector('#notifications_count')
          .value)

      this.title = document.querySelector('#title')
          .value

      this.url = document.querySelector('#url')
          .value

      this.messages_index_url = document.querySelector('#messages_index_url')
          .value

      this.notifications_index_url = document.querySelector('#notifications_index_url')
          .value

      this.logout_url = document.querySelector('#logout_url')
          .value

      this.user_url = document.querySelector('#user_url')
          .value
    }
  }
};
</script>

<template>
  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
      <div class="navbar-wrapper">
        <div class="navbar-minimize">
          <button id="minimizeSidebar"
                  class="btn btn-warning btn-fill btn-round btn-icon d-none d-lg-block">
            <i class="fa fa-ellipsis-v visible-on-sidebar-regular"></i>
            <i class="fa fa-navicon visible-on-sidebar-mini"></i>
          </button>
        </div>
        <a class="navbar-brand" :href="url">{{ title }}</a>
      </div>
      <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse"
              aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-bar burger-lines"></span>
        <span class="navbar-toggler-bar burger-lines"></span>
        <span class="navbar-toggler-bar burger-lines"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-end">
        <ul class="nav navbar-nav mr-auto">
        </ul>
        <ul class="navbar-nav" v-if="user">
          <li class="dropdown nav-item">
            <a id="messages" target="_blank" :href="messages_index_url" class="nav-link">
              <i class="nc-icon nc-chat-round"></i>
              <span class="d-lg-none">Диалоги</span>
              <span class="notification" v-if="messages_count">{{ messages_count }}</span>
            </a>
          </li>
          <li class="dropdown nav-item">
            <a id="main" :href="notifications_index_url" class="nav-link">
              <i class="nc-icon nc-bell-55"></i>
              <span class="d-lg-none">Оповещения</span>
              <span class="notification" v-if="notifications_count">{{ notifications_count }}</span>
            </a>
          </li>
          <li class="dropdown nav-item">
            <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
              {{ user.last_name }} {{ user.first_name }} {{ user.patronymic }}
            </a>
            <ul class="dropdown-menu dropdown-menu-right">
              <a :href="user.user_card_route" class="dropdown-item">
                <i class="nc-icon nc-single-02"></i> Профиль
              </a>
              <a :href="logout_url" class="dropdown-item text-exit">
                <i class="nc-icon nc-button-power"></i> Выйти
              </a>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>

<style scoped>

</style>