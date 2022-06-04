<template>
    <div class="mdui-row">
        <div class="mdui-col-xs-12">
            <div class="mdui-card">
                <!-- 卡片的标题和副标题 -->
                <div class="mdui-card-primary">
                    <div class="mdui-card-primary-title">节点管理</div>
                    <div class="mdui-card-primary-subtitle">Nodes</div>
                    <button class="mdui-fab mdui-fab-fixed mdui-ripple mdui-color-theme-accent" @click="addNode">
                        <i class="mdui-icon material-icons">add</i>
                    </button>
                </div>

                <!-- 卡片的内容 -->
                <div class="mdui-card-content">
                    <div class="mdui-table-fluid">
                        <table class="mdui-table mdui-table-hoverable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>IP</th>
                                    <th>Node ID</th>
                                    <th>Node Key</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(node, index) in nodes" v-bind:key="node.node_id">
                                    <td>{{ index + 1 }}</td>
                                    <td>{{ node.name }}</td>
                                    <td>{{ node.ip }}</td>
                                    <td>{{ node.node_id }}</td>
                                    <td>{{ node.key }}</td>
                                    <td>
                                        <div href="javascript:void(0)" class="mdui-btn mdui-btn-icon" @click="editNode(node)">
                                            <i class="mdui-icon material-icons">edit</i>
                                        </div>
                                        <div href="javascript:void(0)" class="mdui-btn mdui-btn-icon" @click="deleteNode(node)">
                                            <i class="mdui-icon material-icons">delete</i>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 卡片的按钮 -->
                <div class="mdui-card-actions"></div>
            </div>
        </div>
    </div>
</template>

<script>
import mdui from "mdui"
import node from "../../api/node"
export default {
    name: "Nodes",
    data() {
        return {
            nodes: [],
        }
    },
    methods: {
        deleteNode(nodeObj) {
            node.deleteNode(nodeObj.node_id)
                .then((res) => {
                    if (res.data.code == 200) {
                        mdui.snackbar({
                            message: "删除成功",
                            position: "top",
                        })
                        this.showNodes()
                    } else {
                        mdui.snackbar({
                            message: res.data.message,
                            position: "top",
                        })
                    }
                })
                .catch((error) => {
                    console.log("error:", error)
                    mdui.snackbar({
                        message: "网络异常",
                        position: "top",
                    })
                })
        },
        editNode(node) {
            this.$router.push("/manage/node/" + node.node_id)
        },
        addNode() {
            this.$router.push("/manage/node/new")
        },
        showNodes() {
            node.getAllNodeForManager().then((res) => {
                if (res.data.code == 200) {
                    this.nodes = res.data.data
                } else {
                    mdui.snackbar({
                        message: res.data.message,
                        position: "top",
                    })
                }
            })
        },
    },
    mounted() {
        this.showNodes()
    },
}
</script>
