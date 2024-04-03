function init(element) {
  new Vue(
    require('./Footer.vue').default
  ).$mount(element)
}

module.exports.init = init