import LoginComponent from "../../components/frontend/auth/LoginComponent";
import ForgetPasswordComponent from "../../components/frontend/auth/ForgetPasswordComponent";
import VerifyEmailComponent from "../../components/frontend/auth/VerifyEmailComponent";
import ResetPasswordComponent from "../../components/frontend/auth/ResetPasswordComponent";
import ClientLoginComponent from "../../components/frontend/clientAuth/ClientLoginComponent.vue";
import ClientRegisterComponent from "../../components/frontend/clientAuth/ClientRegisterComponent.vue";
import ClientGoogleCallback from "../../components/frontend/clientAuth/ClientGoogleCallback.vue";
import ClientUpdatePasswordComponent from "../../components/frontend/clientAuth/ClientUpdatePasswordComponent.vue";
export default [
    // client
    {
        path: '/login',
        component: ClientLoginComponent,
        name: 'client.login',
        meta: {
            isFrontend: true,
            auth: false,
            hideLayout: true,
        }
    },
    {
        path:'/register',
        component: ClientRegisterComponent,
        name:"client.register",
        meta:{
            isFrontend: true,
            auth: false,
            hideLayout: true
        }
    },
    {
        path: '/forget-password',
        component: ForgetPasswordComponent,
        name: 'client.forgetPassword',
        meta: {
            isFrontend: true,
            auth: false,
            hideLayout: true,
        }
    },
    {
        path: '/forget-password/verify',
        name: 'client.verifyEmail',
        component: VerifyEmailComponent,
        meta: {
            isFrontend: true,
            auth: false,
            hideLayout: true,
        }
    },
    {
        path: '/forget-password/reset-password',
        name: 'client.resetPassword',
        component: ResetPasswordComponent,
        meta: {
            isFrontend: true,
            auth: false,
            hideLayout: true,
        }
    },
    {
        path: '/client/google/callback',
        name: 'client.googleCallback',
        component:ClientGoogleCallback,
        meta: {
            isFrontend: true,
            auth: false,
            hideLayout: true,
        }
    },
    {
        path: '/client/update-password/:token',
        name: 'client.updatePassword',
        component: ClientUpdatePasswordComponent,
        meta: {
            isFrontend: true,
            auth: false,
            hideLayout: true,
        }
    },
    // admin
    {
        path: '/admin/login',
        component: LoginComponent,
        name: 'auth.login',
        meta: {
            isFrontend: false,
            auth: false
        }
    },
    {
        path: '/admin/forget-password',
        component: ForgetPasswordComponent,
        name: 'auth.forgetPassword',
        meta: {
            isFrontend: false,
            auth: false
        }
    },
    {
        path: '/admin/forget-password/verify',
        name: 'auth.verifyEmail',
        component: VerifyEmailComponent,
        meta: {
            isFrontend: false,
            auth: false
        }
    },
    {
        path: '/admin/forget-password/reset-password',
        name: 'auth.resetPassword',
        component: ResetPasswordComponent,
        meta: {
            isFrontend: false,
            auth: false
        }
    }


];
