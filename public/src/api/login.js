import request from "../utils/request"
export default {
    login(data) {
        const req = request({
            method: 'post',
            url: '/api/login',
            data: data
        })
        return req
    },
    setPassword(data) {
        const req = request({
            method: 'post',
            url: '/api/manager/set_password',
            data: data
        })
        return req
    },
    register(data) {
        const req = request({
            method: 'post',
            url: '/api/register',
            data
        })
        return req
    }
}