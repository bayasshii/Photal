/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
window.Vue = require('vue');

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

Vue.component('example-component', require('./components/ExampleComponent.vue').default);
Vue.component('showalbum-component', require('./components/ShowAlbumComponent.vue').default);
Vue.component('createalbum-component', require('./components/CreateAlbumComponent.vue').default);
Vue.component('showhome-component', require('./components/ShowHomeComponent.vue').default);
Vue.component('albummenu-component', require('./components/AlbumMenuComponent.vue').default);
Vue.component('timeline-component', require('./components/TimelineComponent.vue').default);
Vue.component('editalbum-component', require('./components/EditAlbumComponent.vue').default);



/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

import VueRouter from 'vue-router'
Vue.use(VueRouter);

const timeline = new Vue({
    el: '#timeline'
});