<template>
    <div class="mdui-container" style="margin-top: 25px">
        <div class="mdui-row">
            <div class="mdui-col-xs-12">
                <div class="mdui-card">
                    <!-- 卡片的标题和副标题 -->
                    <div class="mdui-card-primary">
                        <div class="mdui-card-primary-title">站点设置</div>
                        <div class="mdui-card-primary-subtitle">Site Setting</div>
                    </div>

                    <!-- 卡片的内容 -->
                    <div class="mdui-card-content">
                   
                    <!-- 禁用状态 -->
                    <div class="mdui-textfield">
                        <i class="mdui-icon material-icons">lock</i>
                        <input class="mdui-textfield-input" type="text" placeholder="New password" v-model="password" />
                    </div>
                    </div>

                    <!-- 卡片的按钮 -->
                    <div class="mdui-card-actions">
                        <button class="mdui-btn mdui-ripple mdui-color-theme-accent" @click="setPassword">设置密码</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import loginApi from "../../api/login"
import mdui from "mdui"
export default {
    name: "SiteSetting",
    data() {
        return {
            password: "",
        }
    },
    methods: {
        setPassword() {
            if(!this.password){
                return
            }
            loginApi
                .setPassword({
                    password: this.password,
                })
                .then((res) => {
                    if (res.data.code == 200) {
                        localStorage.setItem('token', res.data.data.token)
                        mdui.snackbar({
                            message: "密码更新完毕",
                            position: "top",
                        })
                        console.log('token updated!',res.data.data.token)
                    } else {
                        mdui.snackbar({
                            message: res.data.message,
                            position: "top",
                        })
                    }
                    console.log(res)
                })
                .catch((error) => {
                    console.log("error:", error)
                    mdui.snackbar({
                        message: "网络异常",
                        position: "top"
                    })
                })
        },
    },
    mounted() {},
}
</script>
