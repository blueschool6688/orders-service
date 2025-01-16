import ProfileEditProfileComponent from "../../components/admin/profile/ProfileEditProfileComponent";
import ProfileChangePasswordComponent from "../../components/admin/profile/ProfileChangePasswordComponent";
import ClientEditProfileComponent from "../../components/frontend/profile/ClientEditProfileComponent.vue";
import ClientProfileChangePasswordComponent
    from "../../components/frontend/profile/ClientProfileChangePasswordComponent.vue";

export default [
    {
        path: "/admin/profile/edit-profile",
        component: ProfileEditProfileComponent,
        name: "admin.profile.editProfile",
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "",
            breadcrumb: "edit_profile",
        },
    },
    {
        path: "/admin/profile/change-password",
        component: ProfileChangePasswordComponent,
        name: "admin.profile.changePassword",
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "",
            breadcrumb: "change_password",
        },
    },

    {
        path:"/profile/edit-profile",
        component: ClientEditProfileComponent,
        name: "client.profile.editProfile",
        meta:{
            isFrontend: true,
            auth:true,
        }
    },
    {
        path: "/profile/change-password",
        component: ClientProfileChangePasswordComponent,
        name: "client.profile.changePassword",
        meta: {
            isFrontend: true,
            auth: true,
        },
    }
];
