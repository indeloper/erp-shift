<script>

export default {
  name: 'AddFavoriteMenuItem',
  props: [
      'menuItem'
  ],
  emits: [
      'reload-menu'
  ],
  methods: {
    toggleFavoriteMenuItem() {
      axios.post(
          this.menuItem.toggle_favorite_route
      )
          .then(response => {
            const responseData = response.data;

            this.$emit('reload-menu')
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
  <i @click.prevent.stop="toggleFavoriteMenuItem" v-if="menuItem.is_favorite" class="fa fa-star mr-0 favorite-icon" style="font-size: 16px" aria-hidden="true"></i>
  <i @click.prevent.stop="toggleFavoriteMenuItem" v-else-if="!menuItem.is_favorite" class="fa fa-star-o mr-0 favorite-icon" style="font-size: 16px" aria-hidden="true"></i>
</div>
</template>

<style scoped>
.favorite-icon:hover {
  opacity: .5;
}
</style>