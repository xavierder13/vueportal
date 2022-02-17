import Vue from 'vue';
import Vuetify from '../plugins/vuetify';
import VuetifyMask from '../plugins/vuetify-mask';
import App from './App.vue';
import Vuelidate from 'vuelidate';
import router from './router';
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import excel from 'vue-excel-export';
// import VueSocketio from 'vue-socket.io';
import VueBarcodeScanner from 'vue-barcode-scanner'
import store from './store';


// Vue.use(VueSocketio, 'http://localhost:4000');
Vue.use(excel);
Vue.use(Vuetify);
Vue.use(VuetifyMask);
Vue.use(Vuelidate);
Vue.use(VueSweetalert2);

Vue.use(VueBarcodeScanner, { sensitivity: 300, })

const app = new Vue({
    vuetify: Vuetify,
    el: '#app',
    router,
    store,
    render: h => h(App),
});