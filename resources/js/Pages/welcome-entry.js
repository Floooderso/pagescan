import { createApp } from 'vue'
import Welcome from '../Pages/Welcome.vue'

const el = document.getElementById('welcome-app')
if (el) {
  createApp(Welcome, {
    loginUrl:       el.dataset.loginUrl,
    registerUrl:    el.dataset.registerUrl,
    impressumUrl:   el.dataset.impressumUrl,
    datenschutzUrl: el.dataset.datenschutzUrl,
    year:           Number(el.dataset.year),
  }).mount(el)
}
