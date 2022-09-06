import request from "../utils/request"
export default {
    getDomains() {
        const req = request({
            method: "get",
            url: "/api/manager/domain",
        })
        return req
    },
    getDomain(name) {
        const req = request({
            method: "get",
            url: "/api/manager/domain/" + name,
        })
        return req
    },
    updateDomain(name, data) {
        const req = request({
            method: "post",
            url: "/api/manager/domain/" + name,
            data: {
                domain: data.domain,
                show: data.show,
            },
        })
        return req
    },
    deleteDomain(name) {
        const req = request({
            method: "delete",
            url: "/api/manager/domain/" + name,
        })
        return req
    },
    flushDomain() {
        const req = request({
            method: "post",
            url: "/api/manager/domain/flush",
        })
        return req
    },
}
