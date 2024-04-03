<script>
import AddFavoriteMenuItem from '../../../user/components/AddFavoriteMenuItem.vue';

export default {
  name: 'MenuItemCollapse',
  components: { AddFavoriteMenuItem },
  props: [
      'menuItem'
  ],
  emits: [
      'reload-menu'
  ],
  data() {
    return {
      isActiveElement: false
    }
  },
  mounted() {
    this.isActive()
  },
  methods: {
    isActive() {
      const url = new URL(window.location.href)
      const pathname = url.pathname;

      this.menuItem.actives.forEach(active => {
        try {
          const regex = new RegExp(active);

          if (regex.test(pathname)) {

            this.isActiveElement = true;
          }
        } catch (e) {
        }
      })
    }
  }
};
</script>

<template>
  <li
      :class="{ 'active': isActiveElement }"
      class="nav-item"
  >
    <a class="nav-link d-block d-flex justify-content-between align-items-center"
       style="text-wrap: wrap"
       :href="menuItem.route">
      <div class="d-flex flex-row justify-content-start align-items-center">
        <div class="sidebar-mini p-0 mr-1" v-html="menuItem.icon_path"></div>
        <div class="sidebar-normal">{{ menuItem.title }}</div>
      </div>

      <div class="flex-shrink-0">
        <AddFavoriteMenuItem @reload-menu="$emit('reload-menu')" :menuItem="menuItem"/>
      </div>
    </a>
  </li>
</template>

<style scoped>

</style>