<template>
    <div class="mdui-container" style="margin-top: 25px">
        <div class="mdui-row">
            <div class="mdui-col-xs-5">
                <div class="mdui-card">
                    <!-- 卡片的标题和副标题 -->
                    <div class="mdui-card-primary">
                        <div class="mdui-card-primary-title">登录</div>
                        <div class="mdui-card-primary-subtitle">Login</div>
                    </div>

                    <!-- 卡片的内容 -->
                    <div class="mdui-card-content">
                        <div class="mdui-textfield mdui-textfield-floating-label">
                            <i class="mdui-icon material-icons">lock</i>
                            <label class="mdui-textfield-label">Password</label>
                            <input class="mdui-textfield-input" type="text" pattern="^.*(?=.{8,})(?=.*[a-z]).*$" required v-model="password" />
                            <div class="mdui-textfield-error">密码至少 8 位，且包含小写字母</div>
                            <div class="mdui-textfield-helper">请输入至少 8 位，且包含小写字母的密码</div>
                        </div>
                    </div>

                    <!-- 卡片的按钮 -->
                    <div class="mdui-card-actions">
                        <button class="mdui-btn mdui-ripple mdui-color-theme-accent" @click="loginRequest">登录</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import loginApi from "../api/login"
import mdui from "mdui"
import store from "../store/store"
export default {
    name: "Login",
    data() {
        return {
            password: "",
        }
    },
    methods: {
        loginRequest() {
            loginApi
                .login({
                    password: this.password,
                })
                .then((res) => {
                    if (res.data.code == 200) {
                        store.login(res.data.data.token)
                        this.$router.replace('/manage/setting')
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
                        position: "top",
                        buttonColor: "#90CAF9",
                    })
                })
        },
    },
    mounted() {},
}
</script>
