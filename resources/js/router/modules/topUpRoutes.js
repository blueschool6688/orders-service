import TopUpComponent from "../../components/frontend/topUp/TopUpComponent.vue";
import TopUpPaymentComponent from "../../components/frontend/topUp/TopUpPaymentComponent.vue";


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
    },
    {
        path: '/topup/payment/:id',
        name: "client.topup.payment",
        component: TopUpPaymentComponent,
        meta:{
            isFrontend: true,
            auth: true,
            hideLayout: false,
        }
    }
]

