import('./bootstrap.js')
import {createApp} from 'vue/dist/vue.esm-bundler';
import ProjectsIndex from "./pages/projects/ProjectsIndex.vue";
import 'devextreme/dist/css/dx.light.css';

const app = createApp({});
app.component('projects-index', ProjectsIndex);
app.mount('#app-test');


