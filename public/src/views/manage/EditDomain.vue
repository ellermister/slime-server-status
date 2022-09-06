<template>
    <div class="mdui-row">
        <div class="mdui-col-xs-12">
            <div class="mdui-card mt">
                <!-- 卡片的标题和副标题 -->
                <div class="mdui-card-primary">
                    <div class="mdui-card-primary-title">编辑域名</div>
                    <div class="mdui-card-primary-subtitle">Edit domain</div>
                </div>

                <!-- 卡片的内容 -->
                <div class="mdui-card-content">
                    <div class="mdui-row">
                        <div class="mdui-col-xs-4 mdui-textfield mdui-textfield-not-empty">
                            <label class="mdui-textfield-label">Name</label>
                            <input class="mdui-textfield-input" type="text" v-model="domainInformation.domain" />
                        </div>
                        <div class="mdui-col-xs-4 mdui-textfield mdui-textfield-not-empty">
                            <label class="mdui-textfield-label">Show</label>
                            <label class="mdui-switch">
                                <input type="checkbox" :checked="domainInformation.show" v-model="domainInformation.show" />
                                <i class="mdui-switch-icon"></i>
                            </label>
                        </div>
                    </div>

                    <br />
                    <h3>Whois</h3>
                    <div class="mdui-row mdui-row" v-if="domainInformation.whois">
                        <div class="mdui-col mdui-textfield">
                            <label class="mdui-textfield-label">Owner</label>
                            <input class="mdui-textfield-input" type="text" v-model="domainInformation.whois.owner" disabled />
                        </div>
                        <div class="mdui-col mdui-textfield mdui-textfield-not-empty">
                            <label class="mdui-textfield-label">Registrar</label>
                            <input class="mdui-textfield-input" type="text" v-model="domainInformation.whois.registrar" disabled />
                        </div>
                        <div class="mdui-col mdui-textfield mdui-textfield-not-empty">
                            <label class="mdui-textfield-label">CreationDateStr</label>
                            <input class="mdui-textfield-input" type="text" v-model="domainInformation.whois.creationDateStr" disabled />
                        </div>
                        <div class="mdui-col mdui-textfield mdui-textfield-not-empty">
                            <label class="mdui-textfield-label">ExpirationDateStr</label>
                            <input class="mdui-textfield-input" type="text" v-model="domainInformation.whois.expirationDateStr" disabled />
                        </div>
                        <div class="mdui-col mdui-textfield mdui-textfield-not-empty">
                            <label class="mdui-textfield-label">Name Servers</label>
                            <input class="mdui-textfield-input" type="text" v-model="domainInformation.whois.nameServers" disabled />
                        </div>
                    </div>
                </div>

                <!-- 卡片的按钮 -->
                <div class="mdui-card-actions">
                    <button class="mdui-btn mdui-ripple mdui-color-theme-accent" @click="updateDomain" mdui-tooltip="{content:'更新域名'}">更新</button>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>
</style>
<script>
import mdui from "mdui"
import domainApi from "../../api/domain"
export default {
    name: "EditDomain",
    data() {
        return {
            name: "",
            domainInformation: {
                domain: "",
                show: true,
                whois:{}
            },

            currentDomainAction: null,
        }
    },
    methods: {
        updateDomain() {
            console.log("updateDomain", this.domainInformation)
            let originDomain = null;
            if(this.currentDomainAction == 'update'){
                originDomain = this.name;
            }else{
                originDomain = this.domainInformation.domain;
            }
            domainApi
                .updateDomain(originDomain, {
                    domain: this.domainInformation.domain,
                    show: this.domainInformation.show,
                })
                .then((res) => {
                    if (res.data.code == 200) {
                        mdui.snackbar({
                            message: "更新成功",
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

        showDomain() {
            if (this.currentDomainAction == "update") {
                domainApi
                    .getDomain(this.name)
                    .then((res) => {
                        if (res.data.code == 200) {
                            this.domainInformation = res.data.data
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
            }
        },
    },
    mounted() {
        this.currentDomainAction = this.$route.params.name ? "update" : "new"
        this.name = this.$route.params.name
        this.showDomain()
    },
}
</script>
