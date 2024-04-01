<script>
import MenuItem from './MenuItem.vue';
import MenuCollapse from './MenuCollapse.vue';

export default {
  name: 'Menu',
  components: { MenuItem, MenuCollapse },

  props: [
      'menu'
  ],

  data: {
    action: null,
    menuItems: []
  },

  mounted() {
    this.action = document.getElementById('get-menu-url').value;

    this.loadMenu()
  },

  methods: {
    loadMenu() {
      axios.get(this.action)
          .then(response => {
            const responseData = response.data;

            this.menuItems = responseData.data

          })
          .catch(_ => {
            // ОБРАБОТКА ОШИБКИ
          })
    }
  }
};
</script>

<template>
<div>
  <ul class="nav first-nav">
    <div v-for="menuItem in menuItems" :key="menuItem.id">
      <MenuItem v-if="menuItem.route"  :menuItem="menuItem"/>
      <MenuCollapse v-else-if="!menuItem.route && menuItem.children"  :menuItem="menuItem"/>
    </div>
  </ul>
</div>
</template>

<style scoped>

</style>