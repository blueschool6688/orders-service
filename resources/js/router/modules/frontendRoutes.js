import MainComponent from "../../components/frontend/home/MainComponent.vue";
import QrScanComponent from "../../components/frontend/qr/QrScanComponent.vue";
import PageComponent from "../../components/frontend/pages/PageComponent.vue";
export default [
    {
        path: '/',
        component: MainComponent,
        name: 'frontend.home',
        meta: {
            isFrontend:true
        },
        children: [

        ]
    },
    {
        path: '/qr-scan',
        component: QrScanComponent,
        name: 'frontend.qr',
        meta: {
            isFrontend:true
        },
    },
    {
        path: "/page/:pageSlug",
        component: PageComponent,
        name: "frontend.page",
        meta: {
            isFrontend:true
        },
    },
]
