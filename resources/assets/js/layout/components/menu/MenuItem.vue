<script>
export default {
  name: 'MenuItem',
  props: [
      'menuItem'
  ]
};
</script>

<template>
  <li class="nav-item">
    <a class="nav-link" :href="menuItem.route">
      <span v-html="menuItem.icon_path"></span>
      <p>{{ menuItem.title }}</p>
    </a>
  </li>
</template>

<style scoped>

</style>