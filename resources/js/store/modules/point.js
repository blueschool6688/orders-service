import axios from "axios";
import clientAxios from "../../config/clientAxios";
import appService from "../../services/appService";

export const point = {
    namespaced: true,
    state:{
        lists:[],
        page:{},
        pagination:[],
        settings:[],
        details:[]
    },
    getters:{
        lists: function (state) {
            return state.lists;
        },
        pagination: function (state) {
            return state.pagination;
        },
        page: function (state) {
            return state.page;
        },
        settings:function (state){
            return state.settings;
        },
        details: function (state) {
            return state.details
        }
    },
    actions:{
        lists: function (context,payload){
            return new Promise((resolve, reject)=>{
                let url = "topup/list"
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }
                clientAxios.get(url).then((res) => {
                    context.commit("lists", res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            })
        },
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
        changeToPoint: function (context,payload){
          return new Promise((resolve, reject)=>{
              let url = 'point/change-to-point'

              clientAxios.post(url,payload).then(res => {
                  resolve(res)
              })
              .catch(err => {
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
        lists: function (state, payload) {
            state.lists = payload;
        },
        pagination: function (state, payload) {
            state.pagination = payload;
        },
        page: function (state, payload) {
            if (typeof payload !== "undefined" && payload !== null) {
                state.page = {
                    from: payload.from,
                    to: payload.to,
                    total: payload.total,
                };
            }
        },
        settings:function(state, payload){
            state.settings = payload;
        },
        details: function (state, payload){
            state.details = payload;
        }
    }
}
