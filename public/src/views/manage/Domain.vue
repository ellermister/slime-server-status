<template>
    <div class="mdui-row">
        <div class="mdui-col-xs-12">
            <div class="mdui-card">
                <!-- 卡片的标题和副标题 -->
                <div class="mdui-card-primary">
                    <div class="mdui-card-primary-title">域名管理</div>
                    <div class="mdui-card-primary-subtitle">domains</div>
                </div>

                <!-- 卡片的内容 -->
                <div class="mdui-card-content">
                    <div class="mdui-table-fluid">
                        <table class="mdui-table mdui-table-hoverable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Domain</th>
                                    <th>Show</th>
                                    <th>Expired at</th>
                                    <th>Remaining days</th>
                                    <th>Owner</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(domain, index) in domains" v-bind:key="domain.domain">
                                    <td>{{ index + 1 }}</td>
                                    <td>{{ domain.domain }}</td>
                                    <td>{{ domain.show }}</td>
                                    <td>{{ domain.whois ? domain.whois.expirationDateStr : "unkown" }}</td>
                                    <td v-if="domain.whois">
                                        <mark :class="getRemainingDayColor(domain.whois.expirationDate)">{{ getRemainingDay(domain.whois.expirationDate) }}天</mark>
                                    </td>
                                    <td v-else></td>
                                    <td>
                                        {{ domain.whois ? domain.whois.owner : "" }}
                                    </td>
                                    <td>
                                        <router-link :to="{ name: 'domain_edit', params: { name: domain.domain } }" class="mdui-btn mdui-btn-icon">
                                            <i class="mdui-icon material-icons">edit</i>
                                        </router-link>
                                        <div href="javascript:void(0)" class="mdui-btn mdui-btn-icon" @click="deleteDomain(domain)">
                                            <i class="mdui-icon material-icons">delete</i>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 卡片的按钮 -->
                <div class="mdui-card-actions mdui-fab-fixed ">
                    <router-link class="mdui-fab mdui-ripple mdui-color-theme-accent" to="/manage/domain/new">
                        <i class="mdui-icon material-icons">add</i>
                    </router-link>
                    <button class="mdui-fab mdui-ripple mdui-color-theme" @click="flushDomain">
                        <i class="mdui-icon material-icons">refresh</i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import mdui from "mdui"
import domainApi from "../../api/domain"
export default {
    name: "Domain",
    data() {
        return {
            domains: [],
        }
    },
    methods: {
        loadDomains() {
            domainApi.getDomains().then((ret) => {
                if (ret.data.code == 200) {
                    this.domains = ret.data.data
                }
            })
        },
        getRemainingDay(unixTime) {
            return parseInt((unixTime - Date.now() / 1000) / 86400)
        },
        getRemainingDayColor(unixTime) {
            const days = this.getRemainingDay(unixTime)
            if (days >= 365) {
                return "mdui-color-teal-accent"
            } else if (days >= 180) {
                return "mdui-color-light-green-accent"
            } else if (days >= 60) {
                return "mdui-color-orange-accent"
            }
            return "mdui-color-pink-accent"
        },
        deleteDomain(domain) {
            console.log("delete domain", domain)
            domainApi.deleteDomain(domain.domain)
                .then((res) => {
                    if (res.data.code == 200) {
                        mdui.snackbar({
                            message: "删除成功",
                            position: "top",
                        })
                        this.loadDomains()
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

        flushDomain(){
            domainApi.flushDomain().then(res=>{
                if (res.data.code == 200) {
                        mdui.snackbar({
                            message: "已经创建任务刷新, 请等待更新",
                            position: "top",
                        })
                    } else {
                        mdui.snackbar({
                            message: res.data.message,
                            position: "top",
                        })
                    }
            }).catch((error) => {
                    console.log("error:", error)
                    mdui.snackbar({
                        message: "网络异常",
                        position: "top",
                    })
                })
        }
    },
    mounted() {
        this.loadDomains()
    },
}
</script>