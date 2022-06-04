import request from "../utils/request"
export default {
    getAllNodeForManager() {
        const req = request({
            method: 'get',
            url: '/api/manager/nodes',
        })
        return req
    },
    getNode(nodeId) {
        const req = request({
            method: 'get',
            url: '/api/manager/node/'+nodeId
        })
        return req
    },
    updateNode(nodeId, data){
        const req = request({
            method: 'post',
            url: '/api/manager/node/'+nodeId,
            data:data
        })
        return req
    },
    addNode(data){
        const req = request({
            method: 'post',
            url: '/api/manager/node/new',
            data:data
        })
        return req
    },
    deleteNode(nodeId){
        const req = request({
            method: 'delete',
            url: '/api/manager/node/'+nodeId,
        })
        return req
    },
    getAllNodeStatusForPublic()
    {
        return request({
            method: 'get',
            url: '/api/status'
        })
    }
}