import {createApp} from "vue";
import jQuery from 'jquery';

import('./bootstrap.js')

window.$ = jQuery;

createApp({}).mount('#app')
