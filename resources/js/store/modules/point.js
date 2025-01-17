import axios from "axios";
import clientAxios from "../../config/clientAxios";

export const point = {
    namespaced: true,
    state:{
        settings:[],
        details:[]
    },
    getters:{
        settings:function (state){
            return state.settings;
        },
        details: function (state) {
            return state.details
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
        createRequest:function(context,payload){
            return new Promise((resolve, reject)=>{
                clientAxios.post("/topup/create-request",payload)
                    .then(res => {
                        resolve(res)
                    })
                    .catch(err =>{
                        reject(err)
                    })
            })
        },
        details: function(context,payload){
            return new Promise((resolve, reject)=>{
                clientAxios.get(`/topup/details/${payload.id}`).
                    then(res =>{
                        context.commit('details',res.data.data)
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
        },
        details: function (state, payload){
            state.details = payload;
        }
    }
}
