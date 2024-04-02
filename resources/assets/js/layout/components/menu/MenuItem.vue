<script>
export default {
  name: 'MenuItem',
  props: [
      'menuItem'
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
      class="nav-item"
      :class="{ 'active': isActiveElement }"
  >
    <a class="nav-link" :href="menuItem.route">
      <span v-html="menuItem.icon_path"></span>
      <p>{{ menuItem.title }}</p>
    </a>
  </li>
</template>

<style scoped>

</style>