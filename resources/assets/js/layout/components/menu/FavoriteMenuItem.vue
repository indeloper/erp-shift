<script>
import AddFavoriteMenuItem from '../../../user/components/AddFavoriteMenuItem.vue';

export default {
  name: 'FavoriteMenuItem',
  components: { AddFavoriteMenuItem },
  props: [
      'menuItem'
  ],
  emits: [
      'reload-menu'
  ]
};
</script>

<template>
  <li
      class="nav-item"
  >
    <a class="nav-link d-block d-flex justify-content-between align-items-center" style="text-wrap: wrap" :href="menuItem.route">
      <div class="d-flex flex-row justify-content-start align-items-center">
        <div class="p-0 mr-1" v-html="menuItem.icon_path"></div>
        <p>{{ menuItem.title }}</p>
      </div>
      <div class="flex-shrink-0">
        <AddFavoriteMenuItem @reload-menu="$emit('reload-menu')" :menuItem="menuItem"/>
      </div>
    </a>
  </li>
</template>

<style scoped>

</style>