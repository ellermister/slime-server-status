import { createRouter, createWebHistory } from "vue-router"
import Home from "../views/Home.vue"
import Login from "../views/Login.vue"
import Manage from "../views/manage/Manage.vue"
import ManageSiteSetting from "../views/manage/SiteSetting.vue"
import ManageNodes from "../views/manage/Nodes.vue"
import EditNode from "../views/manage/EditNode.vue"
import NewNode from "../views/manage/NewNode.vue"
import store from "../store/store"

const routes = [
    {
        path: "/",
        name: "Home",
        meta: {
            requireAuth: false,
        },
        component: Home,
    },
    {
        path: "/login",
        name: "Login",
        meta: {
            requireAuth: false,
        },
        component: Login,
    },
    {
        path: "/manage",
        name: "manage",
        component: Manage,
        meta: {
            requireAuth: true,
        },
        children: [
            {
                path: "setting",
                name: "站点设置",
                meta: {
                    requireAuth: true,
                },
                component: ManageSiteSetting,
            },
            {
                path: "nodes",
                name: "节点设置",
                meta: {
                    requireAuth: true,
                },
                component: ManageNodes,
            },
            {
                path: "node/new",
                name: "新增节点",
                meta: {
                    requireAuth: true,
                },
                component: NewNode,
            },
            {
                path: "node/:id",
                name: "编辑节点",
                meta: {
                    requireAuth: true,
                },
                component: EditNode,
            }
        ],
    },
    {
        path: "/about",
        name: "About",
        // route level code-splitting
        // this generates a separate chunk (about.[hash].js) for this route
        // which is lazy-loaded when the route is visited.
        component: () => import(/* webpackChunkName: "about" */ "../views/About.vue"),
    },
]

const router = createRouter({
    history: createWebHistory(process.env.BASE_URL),
    routes,
})

router.beforeEach((to, from, next) => {
    const isAuthenticated = store.state.is_login
    if (to.meta.requireAuth && !isAuthenticated) next({ name: "Login" })
    else next()
})

export default router
