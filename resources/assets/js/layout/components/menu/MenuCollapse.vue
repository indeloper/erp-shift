<script>
import MenuItemCollapse from './MenuItemCollapse.vue';

export default {
  name: 'MenuItem',
  components: { MenuItemCollapse },
  props: [
      'menuItem'
  ],
  emits: [
    'reload-menu'
  ],
  data() {
    return {
      isOpen: false
    }
  },
  mounted() {
    this.isOpenCollapse()
  },
  methods: {
    showCollapse() {
      if (this.isOpen) {
        this.isOpen = false;
        $('#collapse' + this.menuItem.id).collapse('hide')
      } else {
        this.isOpen = true;
        $('#collapse' + this.menuItem.id).collapse('show')
      }
    },
    isOpenCollapse() {
      const url = new URL(window.location.href)
      const pathname = url.pathname;

      this.menuItem.actives.forEach(active => {
        try {
          const regex = new RegExp(active);

          if (regex.test(pathname)) {
            $('#collapse' + this.menuItem.id).collapse('show')

            this.isOpen = true;
          }
        } catch (e) {
        }
      })
    }
  }
};
</script>

<template>
  <li class="nav-item">

    <a @click.prevent.stop="showCollapse" class="nav-link " data-toggle="collapse" :href="`#collapse${menuItem.id}`">
      <span v-html="menuItem.icon_path"></span>
      <p>{{ menuItem.title }}
        <b class="caret"></b>
      </p>
    </a>


    <div
        class="collapse"
        :id="`collapse${menuItem.id}`">
      <ul class="nav">
        <MenuItemCollapse
            v-for="childrenItem in menuItem.children"
            :menuItem="childrenItem"
            :key="childrenItem.id"
            @reload-menu="$emit('reload-menu')"
        />
        <hr>
      </ul>
    </div>
  </li>
</template>

<style scoped>

</style>