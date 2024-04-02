<script>
import MenuItem from './MenuItem.vue';
import MenuCollapse from './MenuCollapse.vue';
import FavoriteMenuItem from './FavoriteMenuItem.vue';

export default {
  name: 'Menu',
  components: { MenuItem, MenuCollapse, FavoriteMenuItem },

  props: [
      'menu'
  ],

  data() {
    return {
      action: null,
      favoritesUrl: null,
      menuItems: [],
      favorites: [],
    }
  },

  mounted() {
    this.action = document.getElementById('get-menu-url').value;
    this.favoritesUrl = document.getElementById('get-menu-favorites-url').value;

    this.loadMenu()
  },

  methods: {
    loadMenu() {
      axios.get(this.action)
          .then(response => {
            const responseData = response.data;

            this.menuItems = responseData.data

          })

      axios.get(this.favoritesUrl)
          .then(response => {
            const responseData = response.data;

            this.favorites = responseData.data
          })
    }
  }
};
</script>

<template>
<div>
  <ul class="nav first-nav">
    <div v-for="favorite in favorites" :key="favorite.id">
      <FavoriteMenuItem
          v-if="favorite.route"
          @reload-menu="loadMenu"
          :menuItem="favorite"
      />
    </div>

    <hr v-if="favorites.length">

    <div v-for="menuItem in menuItems" :key="menuItem.id">

      <MenuItem
          v-if="menuItem.route"
          :menuItem="menuItem"
      />

      <MenuCollapse
          v-else-if="!menuItem.route && menuItem.children"
          @reload-menu="loadMenu"
          :menuItem="menuItem"
      />

    </div>
  </ul>
</div>
</template>

<style scoped>

</style>