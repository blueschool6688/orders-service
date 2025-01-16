<template>
    <LoadingComponent :props="loading"/>
    <section class="pt-8 pb-16">
        <div class="container max-w-[360px] py-6 p-4 mb-6 sm:px-6 shadow-xs rounded-2xl bg-white">
            <h2 class="capitalize mb-6 text-center text-[22px] font-semibold leading-[34px] text-heading">
                {{ $t('label.welcome_back') }}
            </h2>
            <div v-if="errors.validation"
                 class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 mb-5 rounded relative" role="alert">
                <span class="block sm:inline">{{ errors.validation }}</span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" @click="close">
                    <i class="lab lab-close-circle-line margin-top-5-px"></i>
                </span>
            </div>
            <form @submit.prevent="login">
                <div class="mb-4">
                    <label for="formEmailOrPhone" class="text-sm capitalize mb-1 text-heading">
                        {{ $t('label.email_or_phone') }}
                    </label>
                    <input
                        type="text"
                        :class="errors.email ? 'invalid' : ''"
                        v-model="form.email_or_phone"
                        class="w-full h-12 rounded-lg border px-4 border-[#D9DBE9]"
                        id="formEmailOrPhone"
                        :placeholder="$t('message.enter_email_or_phone')"
                    />
                    <small class="db-field-alert" v-if="errors.login">
                        {{ errors.login[0] }}
                    </small>
                </div>
                <div class="mb-4">
                    <label for="formPassword" class="text-sm capitalize mb-1 text-heading">{{
                            $t('label.password')
                        }}</label>
                    <div class="relative">
                        <input autocomplete="off"  :type="showPassword ? 'text' : 'password'" :class="errors.password ? 'invalid' : ''"
                               v-model="form.password"
                               class="w-full h-12 rounded-lg border px-4 border-[#D9DBE9]" id="formPassword"
                               :placeholder="$t('message.enter_password')"
                        >
                        <i
                            class="absolute right-3 top-[50%] translate-y-[-50%] cursor-pointer"
                            :class="showPassword ? 'fa fa-eye-slash' : 'fa fa-eye'"
                            @click="togglePasswordVisibility"
                        ></i>
                    </div>

                    <small class="db-field-alert" v-if="errors.password">{{ errors.password[0] }}</small>
                </div>
                <div class="flex items-center justify-between mb-6">
                    <div class="db-field-checkbox p-0">
                        <div class="custom-checkbox w-3 h-3">
                            <input type="checkbox" id="checkbox2" class="custom-checkbox-field">
                            <i class="fa-solid fa-check custom-checkbox-icon leading-[9px] text-[9px] rounded-[3px] border-[#6E7191]"></i>
                        </div>
                        <label for="checkbox2" class="db-field-label text-xs text-heading">
                            {{ $t('label.remember_me') }}
                        </label>
                    </div>
<!--                    <router-link :to="{ name: 'client.forgetPassword' }"-->
<!--                                 class="capitalize text-xs font-medium transition text-primary hover:underline">-->
<!--                        {{ $t('button.forget_password') }}-->
<!--                    </router-link>-->
                </div>
                <button type="submit"
                        class="w-full h-12 text-center capitalize font-medium rounded-3xl mb-6 text-white bg-primary">
                    {{ $t('button.login') }}
                </button>
            </form>
            <router-link :to="{ name: 'client.register' }"
                         class="capitalize block text-xs font-medium transition text-dark text-center mx-auto">
                {{ $t('message.dont_have_account') }}
                <span class="font-semibold text-primary"> {{ $t('button.signup') }}</span>
            </router-link>
            <!-- Divider -->
            <div class="flex items-center justify-between my-4">
                <span class="h-px bg-gray-300 flex-1"></span>
                <span class="text-sm text-gray-500 px-2">{{ $t('label.or_continue_with') }}</span>
                <span class="h-px bg-gray-300 flex-1"></span>
            </div>
            <!-- Google Button -->
            <button @click="loginWithGoogle" class="w-full h-12 flex items-center justify-center gap-2 rounded-3xl border border-[#D9DBE9]">
                <i class="fa-brands fa-google text-lg"></i>
                {{ $t('button.continue_with_google') }}
            </button>
            <router-link :to="{ name: 'frontend.home' }"
                         class="capitalize block text-xs font-medium transition text-primary hover:underline text-center mx-auto mt-3">
                {{ $t('button.go_home') }}
            </router-link>
        </div>

        <div v-if="demo === 'true' || demo === 'TRUE' || demo === 'True' || demo === '1' || demo === 1" class="container max-w-[360px] py-6 p-4 sm:px-6 shadow-xs rounded-2xl bg-white">
            <h2 class="mb-6 text-center text-lg font-medium text-heading">{{ $t('message.for_quick_demo') }}</h2>
            <nav class="grid grid-cols-2 gap-3">
                <button @click.prevent="setupCredit('admin')"
                        class="click-to-prop w-full h-10 leading-10 rounded-lg text-center text-sm capitalize text-white bg-orange-500"
                        id="adminClick">
                    {{ $t('label.admin') }}
                </button>
                <button @click.prevent="setupCredit('branchManager')"
                        class="click-to-prop w-full h-10 leading-10 rounded-lg text-center text-sm capitalize text-white bg-sky-600"
                        id="branchManagerClick">
                    {{ $t('label.branch_manager') }}
                </button>
                <button @click.prevent="setupCredit('posOperator')"
                        class="click-to-prop w-full h-10 leading-10 rounded-lg text-center text-sm capitalize text-white bg-purple-500"
                        id="posOperatorClick">
                    {{ $t('label.pos_operator') }}
                </button>
            </nav>
        </div>
    </section>
</template>

<script>
import router from "../../../router";
import LoadingComponent from "../components/LoadingComponent";
import alertService from "../../../services/alertService";
import ENV from "../../../config/env";

export default {
    name: "ClientLoginComponent",
    components: {LoadingComponent},
    data() {
        return {
            loading: {
                isActive: false,
            },
            form: {
                email_or_phone: "",
                password: ""
            },
            errors: {},
            permissions: {},
            firstMenu: null,
            demo : ENV.DEMO,
            showPassword: false
        }
    },
    methods: {
        login: function () {
            try {
                const isPhone = !this.form.email_or_phone.includes("@");
                const payload = {
                    password: this.form.password,
                };
                if (isPhone) {
                    payload.phone = this.form.email_or_phone;
                } else {
                    payload.email = this.form.email_or_phone;
                }

                this.loading.isActive = true;

                this.$store.dispatch('clientLogin', payload).then((res) => {
                    this.loading.isActive = false;
                    alertService.success(res.data.message);
                    router.push({name: "frontend.home"});
                }).catch((err) => {
                    this.loading.isActive = false;
                    this.errors = err.response.data.errors;
                })
            } catch (err) {
                this.loading.isActive = false;
            }
        },
        close: function () {
            this.errors = {}
        },
        setupCredit: function (e) {
            if (e === 'admin') {
                this.form.email = 'admin@example.com';
                this.form.password = '123456';
            } else if (e === 'customer') {
                this.form.email = 'customer@example.com';
                this.form.password = '123456';
            } else if (e === 'branchManager') {
                this.form.email = 'branchmanager@example.com';
                this.form.password = '123456';
            } else if (e === 'posOperator') {
                this.form.email = 'posoperator@example.com';
                this.form.password = '123456';
            }
        },
        togglePasswordVisibility: function () {
           this.showPassword = !this.showPassword
        },
        loginWithGoogle() {
            this.loading.isActive = true;
            this.$store.dispatch('clientGoogleRedirect').then((res)=>{
                this.loading.isActive = false;
                const redirect = res.data?.redirectUrl ?? null;
                console.log(redirect)
                if(redirect){
                    window.location.href = redirect
                }
            })
        }
    }
}
</script>
