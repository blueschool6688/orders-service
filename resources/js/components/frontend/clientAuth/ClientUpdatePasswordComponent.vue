<template>
    <LoadingComponent :props="loading"/>
    <section class="pt-8 pb-16">
        <div class="container max-w-[360px] py-6 p-4 mb-6 sm:px-6 shadow-xs rounded-2xl bg-white">
            <h2 class="capitalize mb-6 text-center text-[22px] font-semibold leading-[34px] text-heading">
                {{ $t('label.update_password') }}
            </h2>
            <div v-if="errors.validation"
                 class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 mb-5 rounded relative" role="alert">
                <span class="block sm:inline">{{ errors.validation }}</span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" @click="close">
                    <i class="lab lab-close-circle-line margin-top-5-px"></i>
                </span>
            </div>
            <form @submit.prevent="updatePassword">
                <div class="mb-4">
                    <label for="formPassword" class="text-sm capitalize mb-1 text-heading">
                        {{ $t('label.password') }}
                    </label>
                    <div class="relative">
                        <input
                            :type="showPassword ? 'text' : 'password'"
                            :class="errors.password ? 'invalid' : ''"
                            v-model="form.password"
                            class="w-full h-12 rounded-lg border px-4 border-[#D9DBE9]"
                            id="formPassword"
                            :placeholder="$t('message.enter_password')"
                        />
                        <i
                            class="absolute right-3 top-[50%] translate-y-[-50%] cursor-pointer"
                            :class="showPassword ? 'fa fa-eye-slash' : 'fa fa-eye'"
                            @click="togglePasswordVisibility('password')"
                        ></i>
                    </div>
                    <small class="db-field-alert" v-if="errors.password">
                        {{ errors.password[0] }}
                    </small>
                </div>
                <div class="mb-4">
                    <label for="formPasswordConfirmation" class="text-sm capitalize mb-1 text-heading">
                        {{ $t('label.password_confirmation') }}
                    </label>
                    <div class="relative">
                        <input
                            :type="showConfirmationPassword ? 'text' : 'password'"
                            :class="errors.password_confirmation ? 'invalid' : ''"
                            v-model="form.password_confirmation"
                            class="w-full h-12 rounded-lg border px-4 border-[#D9DBE9]"
                            id="formPasswordConfirmation"
                            :placeholder="$t('message.confirmation_password')"
                        />
                        <i
                            class="absolute right-3 top-[50%] translate-y-[-50%] cursor-pointer"
                            :class="showConfirmationPassword ? 'fa fa-eye-slash' : 'fa fa-eye'"
                            @click="togglePasswordVisibility('confirmationPassword')"
                        ></i>
                    </div>
                    <small class="db-field-alert" v-if="errors.password_confirmation">
                        {{ errors.password_confirmation[0] }}
                    </small>
                </div>
                <button type="submit"
                        class="w-full h-12 text-center capitalize font-medium rounded-3xl mb-6 text-white bg-primary">
                    {{ $t('button.update_password') }}
                </button>
            </form>
            <router-link :to="{ name: 'frontend.home' }"
                         class="capitalize block text-xs font-medium transition text-primary hover:underline text-center mx-auto mt-3">
                {{ $t('button.go_home') }}
            </router-link>
        </div>
    </section>
</template>
<script>
import LoadingComponent from "../components/LoadingComponent";
import router from "../../../router";
import alertService from "../../../services/alertService";
import SsoAxios from "../../../config/SsoAxios";
export default {
    name:"ClientUpdatePasswordComponent",
    components:{LoadingComponent},
    data() {
        return {
            loading: {
                isActive: false,
            },
            form: {
                password: "",
                password_confirmation: "",
            },
            errors: {},
            token: null,
            showPassword:false,
            showConfirmationPassword:false,
        };
    },
    mounted() {
        this.token = this.$route.params.token;
        SsoAxios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
    },
    methods: {
        updatePassword() {
            const payload = {
                password: this.form.password,
                password_confirmation: this.form.password_confirmation,
                token: this.token,
            };
            this.loading.isActive = true;
            this.$store
                .dispatch("updateClientPassword", payload)
                .then((res) => {
                    alertService.success(res.data?.message);
                    this.loading.isActive = false;
                    router.push({ name: "frontend.home" });
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    const errors = err.response?.data?.errors || {};
                    if (errors.validation) {
                        alertService.error(errors.validation);
                    } else if (errors.password) {
                        this.errors = { password: errors.password };
                    } else {
                        alertService.error("An unexpected error occurred.");
                    }
                });
        },
        togglePasswordVisibility: function (type) {
            if (type === 'password'){
                this.showPassword = !this.showPassword
            }
            if (type ==="confirmationPassword"){
                this.showConfirmationPassword = !this.showConfirmationPassword
            }
        },
    },
}
</script>
