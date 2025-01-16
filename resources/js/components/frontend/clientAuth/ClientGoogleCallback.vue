<template>
    <div class="min-h-screen flex justify-center items-center">
        <LoadingComponent :props="{ isActive: true }" />
    </div>
</template>
<script>
    import LoadingComponent from "../components/LoadingComponent";
    import router from "../../../router";
    import alertService from "../../../services/alertService";
    import SsoAxios from "../../../config/SsoAxios";
    export default {
        name:"ClientGoogleCallback",
        components:{
            LoadingComponent
        },
        mounted() {
            const urlParams = new URLSearchParams(window.location.search);
            const token = urlParams.get('token');
            const error = urlParams.get('error');
            const updatePassword = urlParams.get("update_password")??"";
            if (error) {
                alertService.error("Google login failed.");
                router.push({ name: 'client.login' });
            } else if (token) {
                SsoAxios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
                if (updatePassword === "true") {
                    router.push({
                        name: "client.updatePassword",
                        params: { token: token },
                    });
                    return;
                }
                this.$store.dispatch('clientGoogleCheck',{ token }).then((res) => {
                    alertService.success(res.data?.message);
                    router.push({ name: 'frontend.home' });
                }).catch((err) => {
                    const errorMessage =
                        err.response?.data?.errors?.validation ||
                        err.response?.data?.errors?.exception ||
                        "Failed to log in with Google.";
                    alertService.error(errorMessage);
                    router.push({ name: 'client.login' });
                });
            } else {
                router.push({ name: 'client.login' });
            }
        }
    }
</script>
