function init(element) {
  new Vue(
    require('./Header.vue').default
  ).$mount(element)
}

module.exports.init = init