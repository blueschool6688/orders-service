import axios from "axios";
import appService from "../../../services/appService";


export const frontendBanner = {
    namespaced:true,
    state: {
        lists: [],
        show: {},
    },
    getters: {
        lists: function (state) {
            return state.lists;
        },
        show: function (state) {
            return state.show;
        },
    },
    actions:{
        lists:function(context,payload){
            return new Promise((resolve, reject)=>{
              let url = '';
              if (payload){
                  url = url + appService.requestHandler(payload)
              }
              axios.get(url).then((res)=>{
                  context.commit("lists", res.data.data);
                  resolve(res);
              }).catch((err)=>{
                  reject(err)
              })
            })
        },
        show: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.get(`frontend/page/show/${payload}`).then((res) => {
                    context.commit("show", res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
    },
    mutations:{
        lists: function (state, payload) {
            state.lists = payload;
        },
        show: function (state, payload) {
            state.show = payload;
        },
    }
}
