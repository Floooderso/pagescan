import './bootstrap'
import { createApp } from 'vue'
import axios from 'axios'
import CompanySearch from './components/CompanySearch.vue'
import CompanyList   from './components/CompanyList.vue'
import LeadsManager  from './components/LeadsManager.vue'

window.axios = axios
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
axios.defaults.headers.common['X-CSRF-TOKEN'] =
  document.querySelector('meta[name="csrf-token"]')?.content

const searchEl = document.getElementById('company-search-app')
if (searchEl) createApp(CompanySearch).mount(searchEl)

const listEl = document.getElementById('company-list-app')
if (listEl) createApp(CompanyList).mount(listEl)

const leadsEl = document.getElementById('leads-app')
if (leadsEl) createApp(LeadsManager, { isAdmin: leadsEl.dataset.isAdmin === '1' }).mount(leadsEl)
