import Vue from 'vue'
import store from '@/store'
import axios from 'axios'
import Search from '@/components/Search/Search.vue'
import TableAjax from '@/components/ui/TableAjax.vue'

const TOKEN_JWT = 'token_jwt'
const TOKEN_CSRF = 'token_csrf'

axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.headers.common['Content-Type'] = 'application/json'
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
axios.defaults.headers.common['X-CSRF-Token'] = localStorage.getItem(TOKEN_CSRF)
axios.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem(TOKEN_JWT)

new Vue({
  el: '#app',
  store,
  components: {
    'search': Search,
    'tableAjax': TableAjax
  }
})
