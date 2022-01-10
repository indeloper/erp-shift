const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */
 mix.copy('resources/assets/css/additionally.css', 'public/css');
 mix.copy('resources/assets/css/bootstrap.min.css', 'public/css');
 mix.copy('resources/assets/css/common.css', 'public/css');
 mix.copy('resources/assets/css/demo.css', 'public/css');
 mix.copy('resources/assets/css/documentation.css', 'public/css');
 mix.copy('resources/assets/css/im.css', 'public/css');
 mix.copy('resources/assets/css/light-bootstrap-dashboard.css', 'public/css');
 mix.copy('resources/assets/css/pe-icon-7-stroke.css', 'public/css');
 mix.copy('resources/assets/css/font-awesome.min.css', 'public/css');
 mix.copy('resources/assets/css/v4-shims.min.css', 'public/css');
 mix.copy('resources/assets/css/google_fonts.css', 'public/css');
 mix.copy('resources/assets/css/offer.css', 'public/css');
 mix.copy('resources/assets/css/mat_acc_report.css', 'public/css');
 mix.copy('resources/assets/css/object_tooltips.css', 'public/css');
 mix.copy('resources/assets/css/mobile-sidebar.css', 'public/css');
 mix.copy('resources/assets/css/ttn.css', 'public/css');
 mix.copy('resources/assets/css/messages.css', 'public/css');
 mix.copy('resources/assets/css/index.css', 'public/css');
 mix.copy('resources/assets/css/tech.css', 'public/css');
 mix.copy('resources/assets/css/balloon.css', 'public/css');
 mix.copy('resources/assets/css/jquery-ui.min.css', 'public/css');
 mix.copy('resources/assets/css/video-js.css', 'public/css');

mix.sass('resources/assets/css/custom/projects.scss', 'public/css');

 mix.copy('resources/assets/js/core/bootstrap.min.js', 'public/js/core');
 mix.copy('resources/assets/js/core/jquery.3.2.1.min.js', 'public/js/core');
 mix.copy('resources/assets/js/core/popper.min.js', 'public/js/core');

 mix.copy('resources/assets/js/demo.js', 'public/js');
 mix.copy('resources/assets/js/form-validation.js', 'public/js');
 mix.copy('resources/assets/js/plugins/vee-validate.js', 'public/js/plugins');
 mix.copy('resources/assets/js/jquery.table.js', 'public/js');
 mix.copy('resources/assets/js/light-bootstrap-dashboard.js', 'public/js');
 mix.copy('resources/assets/js/modal-window.js', 'public/js');
 mix.copy('resources/assets/js/select2.js', 'public/js');

 mix.copy('resources/assets/js/html2canvas/base64.js', 'public/js');
 mix.copy('resources/assets/js/html2canvas/canvas2image.js', 'public/js');
 mix.copy('resources/assets/js/html2canvas/html2canvas.js', 'public/js');
 mix.copy('resources/assets/js/html2canvas/jquery.plugin.html2canvas.js', 'public/js');


 mix.copy('resources/assets/js/plugins/bootstrap-table-mobile.js', 'public/js/plugins');
 mix.copy('resources/assets/js/plugins/bootstrap-datetimepicker.js', 'public/js/plugins');
 mix.copy('resources/assets/js/plugins/bootstrap-notify.js', 'public/js/plugins');
 mix.copy('resources/assets/js/plugins/bootstrap-selectpicker.js', 'public/js/plugins');
 mix.copy('resources/assets/js/plugins/bootstrap-switch.js', 'public/js/plugins');
 mix.copy('resources/assets/js/plugins/bootstrap-table.js', 'public/js/plugins');
 mix.copy('resources/assets/js/plugins/bootstrap-tagsinput.js', 'public/js/plugins');
 mix.copy('resources/assets/js/plugins/chartist.min.js', 'public/js/plugins');
 mix.copy('resources/assets/js/plugins/fullcalendar.min.js', 'public/js/plugins');
 mix.copy('resources/assets/js/plugins/jquery-jvectormap.js', 'public/js/plugins');
 mix.copy('resources/assets/js/plugins/jquery.bootstrap-wizard.js', 'public/js/plugins');
 mix.copy('resources/assets/js/plugins/jquery.dataTables.min.js', 'public/js/plugins');
 mix.copy('resources/assets/js/plugins/jquery.validate.min.js', 'public/js/plugins');
 mix.copy('resources/assets/js/plugins/moment.min.js', 'public/js/plugins');
 mix.copy('resources/assets/js/plugins/nouislider.js', 'public/js/plugins');
 mix.copy('resources/assets/js/plugins/perfect-scrollbar.jquery.min.js', 'public/js/plugins');
 mix.copy('resources/assets/js/plugins/sweetalert2.all.min.js', 'public/js/plugins');
 mix.copy('resources/assets/js/fixMultiSubmit.js', 'public/js');
 mix.copy('resources/assets/js/plugins/lodash.js', 'public/js/plugins/lodash.js');
 mix.copy('resources/assets/js/plugins/cadesplugin_api.js', 'public/js/plugins/cadesplugin_api.js');
 mix.copy('resources/assets/js/plugins/download.js', 'public/js/plugins/download.js');
 mix.copy('resources/assets/js/92abca6220.js', 'public/js/92abca6220.js');
 mix.copy('resources/assets/js/axios.min.js', 'public/js/axios.min.js');
 mix.copy('resources/assets/js/elementui.js', 'public/js/elementui.js');
 mix.copy('resources/assets/js/ru-RU.js', 'public/js/ru-RU.js');
 mix.copy('resources/assets/js/vue.js', 'public/js/vue.js');
 mix.copy('resources/assets/js/vue-router.js', 'public/js/vue-router.js');
mix.copy('resources/assets/js/video.js', 'public/js/video.js');
mix.copy('resources/assets/js/validation-rules.js', 'public/js');
mix.copy('resources/assets/js/common.js', 'public/js/common.js');
mix.copy('resources/assets/js/velocity.min.js', 'public/js/velocity.min.js');
mix.copy('resources/assets/js/plugins/jquery-nicescroll.min.js', 'public/js/plugins/jquery-nicescroll.min.js');
mix.copy('resources/assets/js/plugins/jquery.steps.min.js', 'public/js/plugins/jquery.steps.min.js');
mix.copy('resources/assets/js/plugins/jquery-ui.min.js', 'public/js/plugins/jquery-ui.min.js');



mix.copy('resources/assets/js/plugins/bootstable.js', 'public/js/plugins/bootstable.js');

 mix.copy('node_modules/jquery-mask-plugin/dist/jquery.mask.min.js', 'public/js/plugins/jquery.mask.min.js');
 mix.copy('node_modules/autonumeric/dist/autoNumeric.min.js', 'public/js/plugins/autonumeric.min.js');

 //vue
 mix.copy('resources/assets/js/components/mat_acc/materials.js', 'public/js/components/mat_acc');
mix.copy('resources/assets/js/validation-rules.js', 'public/js/validation-rules.js');

mix.copy('resources/assets/webfonts/*', 'public/webfonts');

mix.copy('resources/assets/fonts/googleFonts/*', 'public/fonts');

 mix.copy('resources/assets/fonts/nucleo-icons.eot', 'public/fonts');
 mix.copy('resources/assets/fonts/nucleo-icons.svg', 'public/fonts');
 mix.copy('resources/assets/fonts/nucleo-icons.ttf', 'public/fonts');
 mix.copy('resources/assets/fonts/nucleo-icons.woff', 'public/fonts');
 mix.copy('resources/assets/fonts/nucleo-icons.woff2', 'public/fonts');

 mix.copy('resources/assets/fonts/Pe-icon-7-stroke.eot', 'public/fonts');
 mix.copy('resources/assets/fonts/Pe-icon-7-stroke.svg', 'public/fonts');
 mix.copy('resources/assets/fonts/Pe-icon-7-stroke.ttf', 'public/fonts');
 mix.copy('resources/assets/fonts/Pe-icon-7-stroke.woff', 'public/fonts');

 mix.copy('resources/assets/fonts/element-icons.ttf', 'public/fonts');
 mix.copy('resources/assets/fonts/element-icons.woff', 'public/fonts');

mix.copy('resources/assets/img/favicon.ico', 'public/img');
 mix.copy('resources/assets/img/sidebar-5.jpg', 'public/img');
 mix.copy('resources/assets/img/full-screen-image-2.jpg', 'public/img');
 mix.copy('resources/assets/img/faces/face-0.jpg', 'public/img/person');
 mix.copy('resources/assets/img/apple-icon.png', 'public/img');

 mix.copy('resources/assets/img/logo-mini.png', 'public/img');
 mix.copy('resources/assets/img/logo-normal.png', 'public/img');
 mix.copy('resources/assets/img/logosvg.png', 'public/img');
 mix.copy('resources/assets/img/kp_head.png', 'public/img');
 mix.copy('resources/assets/img/kp_head_rotate.png', 'public/img');
 mix.copy('resources/assets/img/kp_head_2.png', 'public/img');
 mix.copy('resources/assets/img/kp_head_rotate_2.png', 'public/img');
 mix.copy('resources/assets/img/rectangle.png', 'public/img');
 mix.copy('resources/assets/img/faces/user-male-black-shape.png', 'public/img');
 mix.copy('resources/assets/img/sort/', 'public/img/sort');
 mix.copy('resources/assets/img/small.png', 'public/img');
 mix.copy('resources/assets/img/support_icon.png', 'public/img');
 mix.copy('resources/assets/img/check-message.png', 'public/img');
 mix.copy('resources/assets/img/send-btn.png', 'public/img');
 mix.copy('resources/assets/img/crane.png', 'public/img');
 mix.copy('resources/assets/img/crane.svg', 'public/img');
 mix.copy('resources/assets/img/wrench.svg', 'public/img');
 mix.copy('resources/assets/img/delivery-truck.svg', 'public/img');

 mix.js('resources/assets/js/plugins/pusher_and_vue.js', 'public/js/plugins');

 // emoji
 mix.copy('resources/assets/js/plugins/emojionearea.min.js', 'public/js/plugins/');
 mix.copy('resources/assets/css/emojionearea.min.css', 'public/css/');
 mix.copy('resources/assets/js/plugins/emojione.js', 'public/js/plugins/');
 mix.copy('resources/assets/css/emojione.css', 'public/css/');

 //Devextreme
 mix.copy('node_modules/devextreme/dist/css/dx.common.css', 'public/css/devextreme/dx.common.css');
 mix.copy('node_modules/devextreme/dist/css/dx.material.blue.light.compact.css', 'public/css/devextreme/dx.material.blue.light.compact.css');
 mix.copyDirectory('node_modules/devextreme/dist/css/fonts', 'public/css/devextreme/fonts');
 mix.copyDirectory('node_modules/devextreme/dist/css/icons', 'public/css/devextreme/icons');
 mix.copy('node_modules/devextreme/dist/js/dx.all.js', 'public/js/devextreme/dx.all.js');
 mix.copy('node_modules/devextreme/dist/js/localization/dx.messages.ru.js', 'public/js/devextreme/dx.messages.ru.js');
  //Q3W
 mix.copy('resources/assets/css/custom/main.css', 'public/css/main.css');

 mix.version();
