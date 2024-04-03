function init(element) {
  new Vue(
    require('./Menu.vue').default
  ).$mount(element)
}

module.exports.init = init