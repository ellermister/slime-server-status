import axios from "axios";
import router from "../router";
import store from "../store/store";
import helper from "./helper";
// import { storeVuex } from "../store";
const request = axios.create({
  baseURL: "/",
  timeout: 5000,
});
request.interceptors.request.use(
  (config) => {
    if (config.url !== "/api/login" && config.url !== "/api/login") {
        if(store.state.token !='' && store.state.token != undefined){
            config.headers.Authorization = store.state.token
        }else{
            config.headers.Authorization = helper.getToken()
        }
        
    }
    return config;
  },
  function (error) {
    return Promise.reject(error);
  }
);

// 响应拦截器
request.interceptors.response.use(
  (response) => {
    if(response.data.code == 401){
        store.logout()
        return router.replace('/login')
    }
    // 在2xx范围内的任何状态代码都会触发此功能
    // 处理响应数据
    return response;
  },
  function (error) {
    // 任何超出2xx范围的状态代码都会触发此功能
    // 处理响应错误
    return Promise.reject(error);
  }
);
export default request;
