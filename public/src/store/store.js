import { reactive } from 'vue'
import helper from '../utils/helper'
const store = {
    debug: true,

    state: reactive({
        is_login: false,
        token: ""
    }),

    setStatus(token) {
        this.state.is_login = true
        this.state.token = token
    },

    login(token) {
        this.setStatus(token)
        helper.setToken(this.state.token)
    },

    logout() {
        this.state.is_login = false
        this.state.token = ""
        helper.clearToken()
    },
}

if(helper.getToken()){
    store.setStatus(helper.getToken())
}


export default store