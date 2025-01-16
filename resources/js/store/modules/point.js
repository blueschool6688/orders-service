import axios from "axios";
import clientAxios from "../../config/clientAxios";

export const point = {
    namespaced: true,
    state:{
        settings:[]
    },
    getters:{
        settings:function (state){
            return state.settings;
        }
    },
    actions:{
        settings: function(context, payload) {
            return new Promise((resolve, reject)=>{
                let url = "admin/setting/point-exchange";
                axios.get(url).then((res)=>{
                    context.commit('settings',res.data)
                    resolve(res)
                })
                .catch((err)=>{
                    reject(err)
                })
            })
        },
        save:function (context,payload){
            return new Promise((resolve, reject)=>{
                let url = "admin/setting/point-exchange";
                axios.post(url,payload).then((res)=>{
                    context.commit('settings',res.data)
                    resolve(res)
                })
                .catch((err)=>{
                    reject(err)
                })
            })
        },
        exchange:function(context,payload){
            return new Promise((resolve, reject)=>{
                let url = "topup/exchange";
                clientAxios.post(url,payload).then((res)=>{
                    resolve(res)
                })
                .catch((err)=>{
                    reject(err)
                })
            })
        },
        createRequest:function(content,payload){
            return new Promise((resolve, reject)=>{
                clientAxios.post("/topup/create-request",payload)
                    .then(res => {
                        resolve(res)
                    })
                    .catch(err =>{
                        reject(err)
                    })
            })
        }
    },
    mutations:{
        settings:function(state, payload){
            state.settings = payload;
        }
    }
}
