<template>
    <header class="appbar mdui-appbar mdui-appbar-fixed">
        <div class="mdui-toolbar mdui-color-theme">
            <a href="javascript:;" @click="toggleLeftMenus" class="mdui-btn mdui-btn-icon">
                <i class="mdui-icon material-icons">menu</i>
            </a>
            <span class="mdui-typo-title">Slime Server Status</span>
            <div class="mdui-toolbar-spacer"></div>
            <a href="javascript:;" class="mdui-btn mdui-btn-icon">
                <i class="mdui-icon material-icons">search</i>
            </a>
            <a href="javascript:;" class="mdui-btn mdui-btn-icon">
                <i class="mdui-icon material-icons">refresh</i>
            </a>
            <a href="javascript:;" class="mdui-btn mdui-btn-icon">
                <i class="mdui-icon material-icons">more_vert</i>
            </a>
        </div>
    </header>

    <div class="mdui-drawer mdui-shadow-5" id="drawer">
        <ul class="mdui-list">
            <router-link to="/" class="mdui-list-item">
                <i class="mdui-list-item-icon mdui-icon material-icons">apps</i>
                <div class="mdui-list-item-content">节点监控</div>
            </router-link>

            <template v-if="state.is_login">
                <li class="mdui-subheader">管理员</li>

                <router-link to="/manage/setting" class="mdui-list-item">
                    <i class="mdui-list-item-icon mdui-icon material-icons">settings</i>
                    <div class="mdui-list-item-content">站点设置</div>
                </router-link>

                <router-link to="/manage/nodes" class="mdui-list-item">
                    <i class="mdui-list-item-icon mdui-icon material-icons">laptop_windows</i>
                    <div class="mdui-list-item-content">节点管理</div>
                </router-link>

                <router-link to="/manage/domain" class="mdui-list-item">
                    <i class="mdui-list-item-icon mdui-icon material-icons">domain</i>
                    <div class="mdui-list-item-content">域名管理</div>
                </router-link>

                <div class="mdui-list-item" @click="logout">
                    <i class="mdui-list-item-icon mdui-icon material-icons">verified_user</i>
                    <div class="mdui-list-item-content">注销登录</div>
                </div>
            </template>
            <template v-if="!state.is_login">
                <li class="mdui-subheader">未登录</li>

                <router-link to="/login" class="mdui-list-item">
                    <i class="mdui-list-item-icon mdui-icon material-icons">verified_user</i>
                    <div class="mdui-list-item-content">用户登录</div>
                </router-link>
            </template>
        </ul>
    </div>

    <div class="mdui-container-fluid">
        <router-view />
    </div>
</template>

<style>
</style>

<script>
import mdui from "mdui"
import store from "./store/store"
import router from './router'

export default {
    data() {
        return {
            drawer: Object,
            state: store.state,
        }
    },
    methods: {
        toggleLeftMenus() {
            this.drawer.toggle()
        },
        logout(){
            store.logout()
            router.replace('/');
        }
    },
    mounted() {
        this.drawer = new mdui.Drawer("#drawer")
    },
}
</script>
