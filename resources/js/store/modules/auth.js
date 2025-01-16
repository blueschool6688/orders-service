import axios from 'axios'
import clientAxios from "../../config/clientAxios";
import SsoAxios from "../../config/SsoAxios";
import ssoAxios from "../../config/SsoAxios";
export const auth = {
    state: {
        //admin
        authStatus: false,
        authToken: null,
        authBranchId: '',
        authInfo: {},
        authMenu: [],
        resetInfo: {
            email: null
        },
        authPermission: {},
        authDefaultPermission: {},

        // client
        clientStatus: false,
        clientToken: null,
        clientBranchId :"",
        clientInfo: {},
        clientMenu:[],
        clientResetInfo:{
            email:null
        },
        clientPermission:{},
        clientDefaultPermission:{}
    },
    getters: {
        authStatus: function (state) {
            return state.authStatus;
        },
        authToken: function (state) {
            return state.authToken;
        },
        authBranchId: function (state) {
            return state.authBranchId;
        },
        authInfo: function (state) {
            return state.authInfo;
        },
        authMenu: function (state) {
            return state.authMenu;
        },
        authRole:function(state){
            return state.authInfo.role_id ?? null
        },
        authPermission: function (state) {
            return state.authPermission;
        },
        authDefaultPermission: function (state) {
            return state.authDefaultPermission;
        },
        resetInfo: function (state) {
            return state.resetInfo;
        },
        // client
        clientStatus: state => state.clientStatus,
        clientToken: state => state.clientToken,
        clientBranchId: state => state.clientBranchId,
        clientInfo: state => state.clientInfo,
        clientMenu: state => state.clientMenu,
        clientRole: state => state.clientInfo.role_id ?? null,
        clientPermission: state => state.clientPermission,
        clientDefaultPermission: state => state.clientDefaultPermission,
        clientResetInfo: state => state.clientResetInfo,

    },
    actions: {
        login: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post('auth/login', payload).then((res) => {
                    context.commit('authLogin', res.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        authcheck: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post('auth/authcheck', payload).then((res) => {
                    if (res.data.status === false){
                        context.commit('authLogout');
                    };
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        logout: function (context) {
            return new Promise((resolve, reject) => {
                axios.post('auth/logout').then((res) => {
                    context.commit('authLogout');
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        forgetPassword: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post('auth/forgot-password', payload).then((res) => {
                    context.commit('forgetPassword', payload);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        verifyCode: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post('auth/forgot-password/verify-code', payload).then((res) => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        resetPassword: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post('auth/forgot-password/reset-password', payload).then((res) => {
                    context.commit('resetPassword', res.data.token);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        updateAuthInfo: function (context, payload) {
            return new Promise((resolve, reject) => {
                if (context.state.authInfo.id === payload.id) {
                    context.commit('authInfo', payload);
                    resolve(payload);
                } else {
                    reject('user data not match');
                }
            });
        },
        GuestLoginVerify: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post('auth/guest-signup/verify', payload).then((res) => {
                    context.commit('authLogin', res.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        loginDataReset: function (context) {
            context.commit('authLogout');
        },
        // Client Actions
        clientLogin(context, payload) {
            return new Promise((resolve, reject) => {
                axios.post('auth/client/login', payload).then((res) => {
                    context.commit('clientLogin', res.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        clientGoogleRedirect(context,payload){
            return new Promise((resolve,reject)=>{
                clientAxios.get('auth/client/google-redirect').then((res)=>{
                    resolve(res)
                }).catch((err)=>{
                    reject(err)
                })
            })
        },
        clientGoogleCheck(context,payload){
            return new Promise((resolve,reject)=>{
                ssoAxios.post('auth/client/sso-check',payload).then((res)=>{
                    context.commit('clientLogin', res.data);
                    resolve(res);
                })
                .catch((err) => {
                    reject(err);
                });
            });
        },
        clientRegister: function(context, payload){
            return new Promise((resolve, reject) => {
                clientAxios.post('auth/client/signup', payload).then((res) => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            })
        },
        updateClientPassword:function(context, payload){
            return new Promise((resolve,reject) => {
                ssoAxios.post('auth/client/sso-update-password',payload).then((res)=>{
                    context.commit('clientLogin', res.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            })
        },
        clientAuthCheck({ commit }) {
            return clientAxios.post('auth/client/authcheck').then(res => {
                if (!res.data.status) commit('clientLogout');
                return res;
            });
        },
        clientLogout(context) {
            return new Promise((resolve, reject) => {
                clientAxios.post('auth/client/logout').then((res) => {
                    context.commit('clientLogout');
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        clientForgetPassword({ commit }, payload) {
            return clientAxios.post('auth/client/forgot-password', payload).then(res => {
                commit('clientForgetPassword', payload);
                return res;
            });
        },
        updateClientInfo: function (context,payload){
            return new Promise((resolve, reject)=>{
                if (context.state.clientInfo.id === payload.id){
                    context.commit('clientInfo',payload);
                    resolve(payload)
                }
                else {
                    reject('user data not match');
                }
            })
        }

    },
    mutations: {
        authLogin: function (state, payload) {
            state.authStatus = true;
            state.authToken = payload.token;
            state.authBranchId = payload.branch_id;
            state.authInfo = payload.user;
            state.authMenu = payload.menu;
            state.authPermission = payload.permission;
            state.authDefaultPermission = payload.defaultPermission;
        },
        authLogout: function (state) {
            state.authStatus = false;
            state.authToken = null;
            state.authBranchId = '';
            state.authInfo = {};
            state.authMenu = [];
            state.authPermission = {};
            state.authDefaultPermission = {};
        },
        forgetPassword: function (state, payload) {
            state.resetInfo = {
                email: payload.email
            }
        },
        resetPassword: function (state) {
            state.resetInfo = {
                email: null
            }
        },
        authInfo: function (state, payload) {
            state.authInfo = payload;
        },

        // Client Mutations
        clientLogin(state, payload) {
            state.clientStatus = true;
            state.clientToken = payload.token;
            state.clientBranchId = payload.branch_id;
            state.clientInfo = payload.user;
            state.clientMenu = payload.menu;
            state.clientPermission = payload.permission;
            state.clientDefaultPermission = payload.defaultPermission;
        },
        clientLogout(state) {
            state.clientStatus = false;
            state.clientToken = null;
            state.clientBranchId = '';
            state.clientInfo = {};
            state.clientMenu = [];
            state.clientPermission = {};
            state.clientDefaultPermission = {};
        },
        clientForgetPassword(state, payload) {
            state.clientResetInfo = { email: payload.email };
        },
        clientInfo: function (state, payload) {
            state.clientInfo = payload;
        },
    },
}
