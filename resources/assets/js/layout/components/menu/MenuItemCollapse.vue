<script>
export default {
  name: 'MenuItemCollapse',
  props: [
      'menuItem'
  ]
};
</script>

<template>
  <li class="nav-item" >
    <a class="nav-link" :href="menuItem.route">
      <span class="sidebar-mini" v-html="menuItem.icon_path"></span>
      <span class="sidebar-normal">{{ menuItem.title }}</span>
    </a>
  </li>
</template>

<style scoped>

</style>