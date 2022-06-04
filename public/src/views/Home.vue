<template>
    <div class="mdui-container">
        <div class="mdui-table-fluid">
            <table class="mdui-table mdui-table-hoverable">
                <thead>
                    <tr>
                        <th>节点</th>
                        <th>位置</th>
                        <th>在线</th>
                        <th>网络 ↓|↑</th>
                        <th>流量 ↓|↑</th>
                        <th>CPU</th>
                        <th>RAM</th>
                        <th>HDD</th>
                        <th>More</th>
                    </tr>
                </thead>
                <tbody >
                    <tr v-for="value in servers" :key="value.id">
                        <td>{{ value.name }}</td>
                        <td>{{ "NaN" }}</td>
                        <td>{{ getUptime(value) }}</td>
                        <td>{{ getNetDeltaIn(value) + " | " + getNetDeltaOut(value) }}</td>
                        <td>{{ getNetTotalIn(value) + " | " + getNetTotalOut(value) }}</td>
                        <td>
                            <div class="mdui-progress">
                                <div class="mdui-progress-determinate" :style="getCpuWidth(value)"></div>
                                <div class="determinate mdui-text-color-white">{{ getCpuMulti(value) }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="mdui-progress" id="tooltip">
                                <div class="mdui-progress-determinate" :style="getMemWidth(value)"></div>
                                <div class="determinate mdui-text-color-white">{{ getMemUsedPercent(value) }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="mdui-progress">
                                <div class="mdui-progress-determinate"></div>
                                <div class="determinate mdui-text-color-white">NaN</div>
                            </div>
                        </td>
                        <td>
                            <i class="mdui-icon material-icons">info_outline</i>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mdui-bottom-nav mdui-bottom-nav-text-auto mdui-bottom-nav-scroll-hide mdui-color-teal mdui-theme-primary-teal mdui-theme-accent-pink">
        <p>Slime Server Status {{version}}</p>
    </div>
</template>

<script>
import mdui from "mdui"
import nodeApi from "../api/node"
import config  from "../../package.json"
export default {
    name: "Home",
    components: {
    },
    data() {
        return {
            servers: [
                
            ],
            // referer state
            timer: null,
            refererLock: false,

            // packages
            version: config.version
        }
    },
    methods: {
        getUptime(value) {
            return Math.ceil(value.stat.host.uptime / 86400) + "天"
        },
        getNetDeltaIn(value) {
            let num = value.stat.net.delta.in
            if (num < 1024) {
                return num.toFixed(2) + "B"
            } else if (num < 1024 ** 2) {
                return (num / 1024).toFixed(2) + "K"
            } else if (num < 1024 ** 3) {
                return (num / 1024 ** 2).toFixed(2) + "M"
            } else if (num < 1024 ** 4) {
                return (num / 1024 ** 2).toFixed(2) + "G"
            }
        },
        getNetDeltaOut(value) {
            let num = value.stat.net.delta.out
            if (num < 1024) {
                return num.toFixed(2) + "B"
            } else if (num < 1024 ** 2) {
                return (num / 1024).toFixed(2) + "K"
            } else if (num < 1024 ** 3) {
                return (num / 1024 ** 2).toFixed(2) + "M"
            } else if (num < 1024 ** 4) {
                return (num / 1024 ** 2).toFixed(2) + "G"
            }
        },
        getNetTotalIn(value) {
            let num = value.stat.net.total.in
            if (num < 1024) {
                return num.toFixed(2) + "B"
            } else if (num < 1024 ** 2) {
                return (num / 1024).toFixed(2) + "K"
            } else if (num < 1024 ** 3) {
                return (num / 1024 ** 2).toFixed(2) + "M"
            } else if (num < 1024 ** 4) {
                return (num / 1024 ** 3).toFixed(2) + "G"
            } else if (num < 1024 ** 5) {
                return (num / 1024 ** 4).toFixed(2) + "T"
            }
        },
        getNetTotalOut(value) {
            let num = value.stat.net.total.out
            if (num < 1024) {
                return num.toFixed(2) + "B"
            } else if (num < 1024 ** 2) {
                return (num / 1024).toFixed(2) + "K"
            } else if (num < 1024 ** 3) {
                return (num / 1024 ** 2).toFixed(2) + "M"
            } else if (num < 1024 ** 4) {
                return (num / 1024 ** 3).toFixed(2) + "G"
            } else if (num < 1024 ** 5) {
                return (num / 1024 ** 4).toFixed(2) + "T"
            }
        },
        getCpuMulti(value) {
            return (value.stat.cpu.multi * 100).toFixed(2) + "%"
        },
        getCpuWidth(value) {
            return { width: this.getCpuMulti(value) }
        },
        getMemUsedPercent(value) {
            return value.stat.mem.virtual.usedPercent.toFixed(2) + "%"
        },
        getMemWidth(value) {
            return { width: this.getMemUsedPercent(value) }
        },
        getMemUsed(value) {
            let num = value.stat.mem.virtual.used
            if (num < 1024 ** 3) {
                return (num / 1024 ** 2).toFixed(2) + "M"
            } else if (num < 1024 ** 4) {
                return (num / 1024 ** 3).toFixed(2) + "G"
            }
        },
        getMemTotal(value) {
            let num = value.stat.mem.virtual.total
            if (num < 1024 ** 3) {
                return (num / 1024 ** 2).toFixed(2) + "M"
            } else if (num < 1024 ** 4) {
                return (num / 1024 ** 3).toFixed(2) + "G"
            }
        },
        getMemTip(value) {
            this.tooltip = new mdui.Tooltip("#tooltip", {
                content: this.getMemUsed(value) + " | " + this.getMemTotal(value),
            })
        },
        getAllNodesStatus()
        {
            if(this.refererLock){
                return false;
            }
            this.refererLock = true
            nodeApi.getAllNodeStatusForPublic().then(res =>{
                if(res.data.code == 200){
                    this.servers = res.data.data
                }else{
                    console.log(res.data.message)
                }
            }).finally(()=>{
                this.refererLock = false
            })
        }
    },
    mounted(){
        this.timer = setInterval(() => {
            this.getAllNodesStatus()
        }, 1000);
        
    },
    unmounted(){
        clearInterval(this.timer)
    }
}
</script>

<style scoped>
.mdui-toolbar {
    height: 64px;
    line-height: 64px;
}

.mdui-container {
    margin-top: 20px;
}

.mdui-progress {
    height: 24px;
    line-height: 24px;
    text-align: center;
    font-weight: 500;
}

.determinate {
    position: relative;
}

thead th,
tbody td {
    text-align: center;
}
</style>
