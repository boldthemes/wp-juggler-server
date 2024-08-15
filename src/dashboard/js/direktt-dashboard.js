import { createApp } from 'vue'
import App from '../vue/App.vue'
import { createPinia } from 'pinia'
import VueQrcode from '@chenfengyuan/vue-qrcode';

import { VueQueryPlugin } from '@tanstack/vue-query'

import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import 'vuetify/styles'
import '@mdi/font/css/materialdesignicons.css'


'use strict'

// vue app setup

vueapp = createApp(App);
const pinia = createPinia()

const vuetify = createVuetify({
    components,
    directives,
})

vueapp.use(pinia)
vueapp.use(vuetify)
vueapp.component(VueQrcode.name, VueQrcode);
vueapp.use(VueQueryPlugin)


vueapp.mount("#app");


