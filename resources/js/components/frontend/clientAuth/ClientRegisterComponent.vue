<template>
    <LoadingComponent :props="loading" />
    <section class="pt-8 pb-16">
        <div class="container max-w-[360px] py-6 p-4 mb-6 sm:px-6 shadow-xs rounded-2xl bg-white">
            <h2 class="capitalize mb-6 text-center text-[22px] font-semibold leading-[34px] text-heading">
                {{ $t('label.register_account') }}
            </h2>
            <div v-if="errors.validation"
                 class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 mb-5 rounded relative" role="alert">
                <span class="block sm:inline">{{ errors.validation }}</span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" @click="close">
                    <i class="lab lab-close-circle-line margin-top-5-px"></i>
                </span>
            </div>
            <form @submit.prevent="register">
                <!-- First Name -->
                <div class="mb-4">
                    <label for="formFirstName" class="text-sm capitalize mb-1 text-heading">{{ $t('label.first_name') }}</label>
                    <input type="text" v-model="form.first_name" :class="errors.first_name ? 'invalid' : ''"
                           class="w-full h-12 rounded-lg border px-4 border-[#D9DBE9]" id="formFirstName"
                           :placeholder="$t('message.enter_first_name')"
                    >
                    <small class="db-field-alert" v-if="errors.first_name">{{ errors.first_name[0] }}</small>
                </div>
                <!-- Last Name -->
                <div class="mb-4">
                    <label for="formLastName" class="text-sm capitalize mb-1 text-heading">{{ $t('label.last_name') }}</label>
                    <input type="text" v-model="form.last_name" :class="errors.last_name ? 'invalid' : ''"
                           class="w-full h-12 rounded-lg border px-4 border-[#D9DBE9]" id="formLastName"
                           :placeholder="$t('message.enter_last_name')"
                    >
                    <small class="db-field-alert" v-if="errors.last_name">{{ errors.last_name[0] }}</small>
                </div>
                <!-- Tab Selector -->
                <div class="flex mb-4">
                    <button type="button" @click="toggleTab('email')"
                            :class="tab === 'email' ? 'active-tab' : ''"
                            class="flex-1 py-2 text-center capitalize">
                        {{ $t('label.email') }}
                    </button>
                    <button type="button" @click="toggleTab('phone')"
                            :class="tab === 'phone' ? 'active-tab' : ''"
                            class="flex-1 py-2 text-center capitalize">
                        {{ $t('label.phone') }}
                    </button>
                </div>
                <!-- Email Input -->
                <div class="mb-4" v-if="tab === 'email'">
                    <label for="formEmail" class="text-sm capitalize mb-1 text-heading">{{ $t('label.email') }}</label>
                    <input type="text" v-model="form.email" :class="errors.email ? 'invalid' : ''"
                           class="w-full h-12 rounded-lg border px-4 border-[#D9DBE9]" id="formEmail"
                        :placeholder="$t('message.enter_email')"
                    >
                    <small class="db-field-alert" v-if="errors.email">{{ errors.email[0] }}</small>
                </div>
                <!-- Phone Input -->
                <div class="flex items-center gap-2" v-if="tab === 'phone'">
                    <select
                        v-model="form.country_code"
                        :class="errors.country_code ? 'invalid' : ''"
                        class="w-20 h-12 rounded-lg border px-2 border-[#D9DBE9]">
                        <option
                            v-for="code in countryCodes"
                            :key="code.calling_code"
                            :value="code.calling_code">
                            {{ code.calling_code }}
                        </option>
                    </select>
                    <input type="text" v-model="form.phone" :class="errors.phone ? 'invalid' : ''"
                           class="flex-1 h-12 rounded-lg border px-4 border-[#D9DBE9]" id="formPhone"
                    :placeholder="$t('message.enter_phone')">
                </div>
                <small class="mb-4 db-field-alert" v-if="errors.phone">{{ errors.phone[0] }}</small>
                <small class="mb-4 db-field-alert" v-if="errors.login">{{ errors.login[0] }}</small>
                <!-- Password -->
                <div class="mb-4">
                    <label for="formPassword" class="text-sm capitalize mb-1 text-heading">{{ $t('label.password') }}</label>
                    <input type="password" v-model="form.password" :class="errors.password ? 'invalid' : ''"
                           class="w-full h-12 rounded-lg border px-4 border-[#D9DBE9]" id="formPassword"
                    :placeholder="$t('message.enter_password')"
                    >
                    <small class="db-field-alert" v-if="errors.password">{{ errors.password[0] }}</small>
                </div>
                <!-- Confirm Password -->
                <div class="mb-4">
                    <label for="formConfirmPassword" class="text-sm capitalize mb-1 text-heading">{{
                            $t('label.confirm_password') }}</label>
                    <input type="password" v-model="form.password_confirmation"
                           :class="errors.password_confirmation ? 'invalid' : ''"
                           class="w-full h-12 rounded-lg border px-4 border-[#D9DBE9]" id="formConfirmPassword"
                            :placeholder="$t('message.confirmation_password')"
                    >
                    <small class="db-field-alert" v-if="errors.password_confirmation">{{ errors.password_confirmation[0] }}</small>
                </div>
                <button type="submit" class="w-full h-12 text-center capitalize font-medium rounded-3xl mb-6 text-white bg-primary">
                    {{ $t('button.sign_up') }}
                </button>
            </form>
            <router-link :to="{ name: 'client.login' }"
                         class="capitalize block text-xs font-medium transition text-dark text-center mx-auto">
                {{ $t('message.already_have_account') }}
                <span class="font-semibold text-primary"> {{ $t('button.login') }}</span>
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
    </section>
</template>
<script>
import LoadingComponent from "../components/LoadingComponent";
import alertService from "../../../services/alertService";
export default {
    name: "ClientRegisterComponent",
    components: { LoadingComponent },
    data() {
        return {
            loading: {
                isActive: false,
            },
            tab: "email",
            form: {
                first_name: "",
                last_name: "",
                email: "",
                phone: "",
                country_code: "",
                password: "",
                password_confirmation: "",
            },
            errors: {},
        };
    },
    computed:{
        countryCodes() {
            return this.$store.getters["countryCode/lists"];
        },
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
    },
    mounted() {
        this.$store.dispatch("countryCode/lists").then(() => {
            const defaultCountryCode = this.countryCodes.find(
                (code) => code.country_code === this.setting.company_country_code
            );

            if (defaultCountryCode) {
                this.form.country_code = defaultCountryCode.calling_code;
            }
        }).catch((err) => {
            console.error("Lỗi khi tải danh sách mã quốc gia:", err);
        });
    },
    methods: {
        toggleTab(tab) {
            this.tab = tab;
            if (tab === "email") {
                this.form.phone = "";
            } else {
                this.form.email = "";
            }
        },
        register() {
            this.loading.isActive = true;
            this.$store
                .dispatch("clientRegister", this.form)
                .then((res) => {
                    this.loading.isActive = false;
                    alertService.success(res.data.message);
                    this.$router.push({ name: "frontend.home" });
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    this.errors = err.response.data.errors;
                });
        },
        close() {
            this.errors = {};
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
    },
};
</script>
<style scoped>
.active-tab {
    background-color: #d1992c;
    color: white;
}
</style>
