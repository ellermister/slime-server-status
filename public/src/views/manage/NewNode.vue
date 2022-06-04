<template>
    <div class="mdui-row">
        <div class="mdui-col-xs-12">
            <div class="mdui-card mt">
                <!-- 卡片的标题和副标题 -->
                <div class="mdui-card-primary">
                    <div class="mdui-card-primary-title">新增节点</div>
                    <div class="mdui-card-primary-subtitle">Add node</div>
                </div>

                <!-- 卡片的内容 -->
                <div class="mdui-card-content">
                    <div class="mdui-row">
                        <div class="mdui-col-xs-4 mdui-textfield mdui-textfield-not-empty">
                            <label class="mdui-textfield-label">name</label>
                            <input class="mdui-textfield-input" type="text" v-model="node.name" />
                        </div>
                        <div class="mdui-col-xs-4 mdui-textfield mdui-textfield-not-empty">
                            <label class="mdui-textfield-label">ip</label>
                            <input class="mdui-textfield-input" type="text" v-model="node.ip" />
                        </div>
                    </div>

                    <br />
                    <h3>API</h3>
                    <div class="mdui-row mdui-row">
                        <div class="mdui-col mdui-textfield">
                            <label class="mdui-textfield-label">ID</label>
                            <input class="mdui-textfield-input" type="text" v-model="node.node_id" disabled />
                        </div>
                        <div class="mdui-col mdui-textfield mdui-textfield-not-empty">
                            <label class="mdui-textfield-label">KEY</label>
                            <input class="mdui-textfield-input" type="text" v-model="node.key" />
                        </div>
                    </div>
                </div>

                <!-- 卡片的按钮 -->
                <div class="mdui-card-actions">
                    <button class="mdui-btn mdui-ripple mdui-color-theme-accent" @click="addNode" mdui-tooltip="{content:'更新节点'}">更新</button>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>
</style>
<script>
import mdui from "mdui"
import node from "../../api/node"
import helper from "../../utils/helper"
export default {
    name: "EditNode",
    data() {
        return {
            nodeId: "",
            node: {
                node_id: "",
                key: "",
                name: "",
                ip: "",
            },
        }
    },
    methods: {
        addNode() {
            node.addNode({
                node_id: this.node.node_id,
                key: this.node.key,
                name: this.node.name,
                ip: this.node.ip,
            })
                .then((res) => {
                    if (res.data.code == 200) {
                        mdui.snackbar({
                            message: "新增成功",
                            position: "top",
                        })
                    } else {
                        mdui.snackbar({
                            message: res.data.message,
                            position: "top",
                        })
                    }
                })
                .catch((error) => {
                    console.log("error:", error.message)
                    mdui.snackbar({
                        message: "请求错误",
                        position: "top",
                    })
                })
        },
    },
    mounted() {
       this.node.node_id = helper.guid()
    },
}
</script>
