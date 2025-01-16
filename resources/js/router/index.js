import {createRouter, createWebHistory} from "vue-router";
import ENV from '../config/env';
import appService from "../services/appService";
import DashboardComponent from "../components/admin/dashboard/DashboardComponent";
import NotFoundComponent from "../components/frontend/otherPage/NotFoundComponent";
import ExceptionComponent from "../components/frontend/otherPage/ExceptionComponent";
import store from "../store";
import authRoutes from "./modules/authRoutes";
import settingRoutes from "./modules/settingRoutes";
import offerRoutes from "./modules/offerRoutes";
import itemRoutes from "./modules/itemRoutes";
import customerRoutes from "./modules/customerRoutes";
import administratorRoutes from "./modules/administratorRoutes";
import employeeRoutes from "./modules/employeeRoutes";
import salesReportRoutes from "./modules/salesReportRoutes";
import itemsReportRoutes from "./modules/itemsReportRoutes";
import posRoutes from "./modules/posRoutes";
import profileRoutes from "./modules/profileRoutes";
import posOrderRoutes from "./modules/posOrderRoutes";
import transactionRoutes from "./modules/transactionRoutes";
import creditBalanceReportRoutes from "./modules/creditBalanceReportRoutes";
import tableOrderRoutes from "./modules/tableOrderRoutes";
import adminTableOrderRoutes from "./modules/adminTableOrderRoutes";
import diningTableRoutes from "./modules/diningTableRoutes";
import frontendRoutes from "./modules/frontendRoutes";


const baseRoutes = [
    {
        path: "/admin",
        redirect: {name: "auth.login"},
        name: "root",
    },
    {
        path: "/:pathMatch(.*)*",
        name: "route.notFound",
        component: NotFoundComponent,
        meta: {
            isFrontend: true,
        },
    },
    {
        path: "/exception",
        name: "route.exception",
        component: ExceptionComponent,
    },
    {
        path: "/admin",
        name: "admin",
        meta: {
            auth: true,
            isFrontend: false,
        },
        children: [
            {
                path: "dashboard",
                component: DashboardComponent,
                name: "admin.dashboard",
                meta: {
                    auth: true,
                    isFrontend: false,
                    permissionUrl: "dashboard",
                    breadcrumb: "Dashboard",
                },
            },
        ],
    },
];

const routes = baseRoutes.concat(
    authRoutes,
    settingRoutes,
    offerRoutes,
    itemRoutes,
    customerRoutes,
    administratorRoutes,
    employeeRoutes,
    salesReportRoutes,
    itemsReportRoutes,
    profileRoutes,
    posRoutes,
    posOrderRoutes,
    transactionRoutes,
    creditBalanceReportRoutes,
    tableOrderRoutes,
    adminTableOrderRoutes,
    diningTableRoutes,
    frontendRoutes
);

const permission = store.getters.authPermission;
appService.recursiveRouter(routes, permission);

const API_URL = ENV.API_URL;
const router = createRouter({
    linkActiveClass: "active",
    mode: 'history',
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const isAdminArea = to.meta.isFrontend === false;
    const isClientArea = to.meta.isFrontend === true;
    const requiresAuth = to.meta.auth === true;

    const isAuthenticated = store.getters.authStatus;
    const isClientAuthenticated = store.getters.clientStatus;
    const redirectToLogin = () => {
        if (isAdminArea) {
            next({ name: "auth.login" });
        } else if (isClientArea) {
            next({ name: "client.login" });
        } else {
            next();
        }
    };
    const handleAuthenticatedAccess = () => {
        if (isAdminArea && to.meta.access === false) {
            next({ name: "route.exception" });
        } else {
            next();
        }
    };
    if (requiresAuth) {
        if (isAdminArea && !isAuthenticated) {
            redirectToLogin();
        } else if (isClientArea && !isClientAuthenticated) {
            redirectToLogin();
        } else {
            handleAuthenticatedAccess();
        }
    }
    else if (to.name === "auth.login" && isAuthenticated) {
        next({ name: "admin.dashboard" });
    } else if (to.name === "client.login" && isClientAuthenticated) {
        next({ name: "frontend.home" });
    } else {
        next();
    }
    // if (to.meta.auth === true) {
    //     if (!store.getters.authStatus && to.meta.isFrontend === false) {
    //         next({name: "auth.login"});
    //     }
    //     else if(!store.getters.clientStatus && to.meta.isFrontend === true){
    //         next({name: "client.login"})
    //     }
    //     else {
    //         if (to.meta.isFrontend === false) {
    //             if (to.meta.access === false) {
    //                 next({
    //                     name: "route.exception",
    //                 });
    //             } else {
    //                 next();
    //             }
    //         } else {
    //             next();
    //         }
    //     }
    // } else if (to.name === "auth.login" && store.getters.authStatus) {
    //     next({name: "admin.dashboard"});
    // }
    // else if (to.name === 'client.login' && store.getters.clientStatus){
    //     next({name: "frontend.home"})
    // }
    // else {
    //     next();
    // }
});
export default router;
