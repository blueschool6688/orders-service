import TopUpComponent from "../../components/frontend/topUp/TopUpComponent.vue";


export default [
    {
        path: '/topup',
        component: TopUpComponent,
        name: 'client.topup',
        meta: {
            isFrontend:true,
            auth:true,
            hideLayout:false
        }
    }
]

