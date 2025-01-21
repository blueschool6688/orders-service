<template>
    <LoadingComponent :props="loading" />

    <header class="shadow-xs bg-white ff-header main-header fixed top-0 left-0 w-full z-60"  ref="ffHeader">
        <div class="container flex flex-col lg:flex-row items-center justify-between">
            <div class="w-full flex items-center justify-between gap-3 xl:gap-8 lg:justify-start lg:w-fit">
                <router-link :to="{ name: 'frontend.home'}"
                             class="flex-shrink-0 logo-img">
                    <img class="w-12 sm:w-16" :src="setting.theme_logo" alt="logo">
                </router-link>

                <!-- This code for mobile device -->

                <button @click="toggleMobileMenu" v-if="!isMobileMenuOpen" class="lg:hidden text-2xl text-[#D1992C]">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <nav
                    class="fixed inset-0 bg-white z-50 p-4 flex flex-col gap-4 transform transition-transform duration-300 ease-in-out lg:hidden"
                    :class="isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'">
                    <button @click="toggleMobileMenu" class="self-end text-[#D1992C] text-2xl mb-4 lg:hidden">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                    <!-- QR Code Button -->
                    <button @click="redirectQrCode" type="button"
                            class="text-white bg-gradient-to-r from-[#D1992C] to-[#A6795E] hover:bg-gradient-to-l hover:from-[#A6795E] hover:to-[#D1992C] focus:outline-none font-medium rounded-lg text-sm px-3 py-2 text-center me-2 transition-all ease-in-out duration-300 shadow-lg lg:hidden">
                        <i class="fa-solid fa-qrcode"></i> {{ $t('menu.scan') }}
                    </button>
                    <!-- Login and Signup Buttons -->

                    <div v-if="clientLogged" class="flex flex-col gap-4">
                        <div class="flex items-center justify-center gap-2">
                            <img class="w-10 h-10 rounded-full object-cover" :src="clientInfo.image" alt="avatar">
                            <h3 class="text-sm font-medium">{{ textShortener(clientInfo.name, 20) }}</h3>
                        </div>
                        <div class="flex flex-col gap-1 items-center justify-center">
                            <p class="text-xs mb-0.5">{{ clientInfo.email }}</p>
                            <p dir="ltr" class="text-xs">{{ clientInfo.country_code }} {{ clientInfo.phone }}</p>
                            <h3 class="font-medium text-sm leading-6 capitalize mb-0.5">{{ clientInfo.balance }} <i class="fa-solid fa-coin-vertical text-[#d1992c]"></i></h3>
                        </div>
                        <nav>
                            <router-link @click="closeMobileMenu" :to="{ name: 'client.topup' }"
                                         class="transition w-full flex items-center gap-3 py-2 border-b border-gray-200">
                                <i class="fa-solid fa-money-check-dollar"></i>
                                <span class="text-sm">{{ $t('button.top_up') }}</span>
                            </router-link>
                            <router-link @click="closeMobileMenu" :to="{ name: 'client.profile.editProfile' }"
                                         class="transition w-full flex items-center gap-3 py-2 border-b border-gray-200">
                                <i class="lab lab-edit"></i>
                                <span class="text-sm">{{ $t('button.edit_profile') }}</span>
                            </router-link>
                            <router-link @click="closeMobileMenu" :to="{ name: 'client.profile.changePassword' }"
                                         class="transition w-full flex items-center gap-3 py-2 border-b border-gray-200">
                                <i class="lab lab-key"></i>
                                <span class="text-sm">{{ $t('button.change_password') }}</span>
                            </router-link>
                            <button @click="logout"
                                    class="transition w-full flex items-center gap-3 py-2 border-b border-gray-200">
                                <i class="lab lab-logout"></i>
                                <span class="text-sm">{{ $t('button.logout') }}</span>
                            </button>
                        </nav>
                    </div>
                    <div v-else class="flex flex-col gap-2">
                        <button @click="redirectLogin" type="button" class="px-4 py-2 text-sm font-medium text-white bg-[#D1992C] rounded-lg hover:bg-[#A6795E] transition-all">
                            {{ $t('button.login') }}
                        </button>
                        <button @click="redirectRegister" type="button" class="px-4 py-2 text-sm font-medium text-[#D1992C] border border-[#D1992C] rounded-lg hover:bg-[#D1992C] hover:text-white transition-all">
                            {{ $t('button.signup') }}
                        </button>
                    </div>
                    <!-- Language Dropdown -->
                    <div v-if="setting.site_language_switch === enums.activityEnum.ENABLE"
                         class="relative dropdown-group flex items-center justify-center">
                        <button
                            class="flex items-center justify-center gap-2 w-50 text-left text-sm font-medium h-8 px-2 border border-gray-200 rounded-md lg:w-fit lg:justify-center lg:px-3 dropdown-btn">
                            <img :src="language.image" alt="flag" class="w-4 h-4 rounded-full">
                            <span class="whitespace-nowrap">{{ language.name }}</span>
                        </button>
                        <ul v-if="languages.length > 0"
                            class="p-2 rounded-lg shadow-xl absolute left-1/2 -translate-x-2/4 top-10 bg-white border border-gray-200 z-10 lg:right-0 hidden dropdown-list">
                            <li @click="changeLanguage(language.id, language.code)" v-for="language in languages" :key="language.id"
                                class="flex items-center gap-2 py-1 px-2 rounded-md cursor-pointer hover:bg-gray-100">
                                <img :src="language.image" alt="flag" class="w-4 h-4 rounded-full">
                                <span class="text-sm capitalize">{{ language.name }}</span>
                            </li>
                        </ul>
                    </div>
                </nav>
<!--                <button-->
<!--                    @click = "redirectQrCode"-->
<!--                    type="button"-->
<!--                    class="lg:hidden text-white bg-gradient-to-r from-[#D1992C] to-[#A6795E] hover:bg-gradient-to-l hover:from-[#A6795E] hover:to-[#D1992C] focus:outline-none font-medium rounded-lg text-sm px-3 py-2 text-center me-2 transition-all ease-in-out duration-300 shadow-lg">-->
<!--                    <i class="fa-solid fa-qrcode"></i> {{ $t('menu.scan') }}-->
<!--                </button>-->
<!--                <div class="flex items-center gap-2 lg:hidden">-->
<!--                    <div v-if="setting.site_language_switch === enums.activityEnum.ENABLE"-->
<!--                         class="block relative dropdown-group w-full sm:w-fit">-->
<!--                        <button-->
<!--                            class="flex items-center justify-center gap-1.5 w-fit rounded-3xl capitalize text-sm font-medium h-8 px-2 border transition text-heading bg-white border-gray-200 dropdown-btn">-->
<!--                            <img :src="language.image" alt="flag" class="w-4 h-4 rounded-full">-->
<!--                            <span class="whitespace-nowrap text-sm hidden">{{ language.name }}</span>-->
<!--                        </button>-->
<!--                        <ul v-if="languages.length > 0"-->
<!--                            class="p-2 min-w-[180px] rounded-lg shadow-xl absolute top-14 right-0 z-10 border border-gray-200 bg-white hidden dropdown-list">-->
<!--                            <li @click="changeLanguage(language.id, language.code)" v-for="language in languages"-->
<!--                                class="flex items-center gap-2 py-1.5 px-2.5 rounded-md cursor-pointer hover:bg-gray-100">-->
<!--                                <img :src="language.image" alt="flag" class="w-4 h-4 rounded-full">-->
<!--                                <span class="text-heading capitalize text-sm">{{ language.name }}</span>-->
<!--                            </li>-->
<!--                        </ul>-->
<!--                    </div>-->
<!--                    <div class="flex items-center gap-2 lg:hidden">-->
<!--                        <button @click="redirectLogin" class="px-4 py-2 text-sm font-medium text-white bg-[#D1992C] rounded-lg hover:bg-[#A6795E] transition-all">-->
<!--                            {{ $t('button.login') }}-->
<!--                        </button>-->
<!--                        <button @click="redirectRegister" class="px-4 py-2 text-sm font-medium text-[#D1992C] border border-[#D1992C] rounded-lg hover:bg-[#D1992C] hover:text-white transition-all">-->
<!--                            {{ $t('button.signup') }}-->
<!--                        </button>-->
<!--                    </div>-->
<!--&lt;!&ndash;                    <button&ndash;&gt;-->
<!--&lt;!&ndash;                        class="webcart flex items-center justify-center gap-1.5 w-fit rounded-3xl capitalize text-sm font-medium h-8 px-2.5 transition text-white bg-heading">&ndash;&gt;-->
<!--&lt;!&ndash;                        <i class="fa-solid fa-bag-shopping text-sm"></i>&ndash;&gt;-->
<!--&lt;!&ndash;                        <span class="whitespace-nowrap text-sm">&ndash;&gt;-->
<!--&lt;!&ndash;                            {{ currencyFormat(subtotal, setting.site_digit_after_decimal_point,&ndash;&gt;-->
<!--&lt;!&ndash;                            setting.site_default_currency_symbol, setting.site_currency_position) }}&ndash;&gt;-->
<!--&lt;!&ndash;                        </span>&ndash;&gt;-->
<!--&lt;!&ndash;                    </button>&ndash;&gt;-->
<!--                </div>-->
            </div>

            <div class="hidden lg:flex flex-col items-center justify-end gap-3 w-full lg:flex-row lg:w-fit lg:mt-0">
                <button
                    @click = "redirectQrCode"
                    type="button"
                    class="hidden lg:block text-white bg-gradient-to-r from-[#D1992C] to-[#A6795E] hover:bg-gradient-to-l hover:from-[#A6795E] hover:to-[#D1992C] focus:outline-none font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 transition-all ease-in-out duration-300 shadow-lg">
                    <i class="fa-solid fa-qrcode"></i> {{ $t('menu.scan') }}
                </button>
<!--                <form @submit.prevent="search"-->
<!--                      class="header-search-group group flex items-center justify-center border border-solid gap-2 px-2 w-full lg:w-52 h-8 rounded-3xl transition border-[#EFF0F6] bg-[#EFF0F6] focus-within:bg-white focus-within:border-primary">-->
<!--                    <button type="submit" class="header-search-submit">-->
<!--                        <i class="lab lab-search-normal"></i>-->
<!--                    </button>-->
<!--                    <input type="search" v-model="searchItem" :placeholder="$t('button.search')"-->
<!--                           class="header-search-field w-full h-full text-xs appearance-none placeholder:font-normal placeholder:text-paragraph text-heading">-->
<!--                    <button type="button" @click.prevent="searchReset"-->
<!--                            class="header-search-button transition invisible group-focus-within:visible">-->
<!--                        <i class="lab lab-close-circle-line lab-font-size-16 lab-font-weight-600 text-red-500"></i>-->
<!--                    </button>-->
<!--                </form>-->

                <div v-if="setting.site_language_switch === enums.activityEnum.ENABLE"
                     class="hidden lg:block relative dropdown-group w-full sm:w-fit">
                    <button
                        class="flex items-center justify-center gap-1.5 w-fit rounded-3xl capitalize text-sm font-medium h-8 px-3 border transition text-heading bg-white border-gray-200 dropdown-btn">
                        <img :src="language.image" alt="flag" class="w-4 h-4 rounded-full">
                        <span class="whitespace-nowrap">{{ language.name }}</span>
                    </button>
                    <ul v-if="languages.length > 0"
                        class="p-2 min-w-[180px] rounded-lg shadow-xl absolute top-14 ltr:right-0 rtl:left-0 z-10 border border-gray-200 bg-white hidden dropdown-list">
                        <li @click="changeLanguage(language.id, language.code)" v-for="language in languages"
                            class="flex items-center gap-2 py-1.5 px-2.5 rounded-md cursor-pointer hover:bg-gray-100">
                            <img :src="language.image" alt="flag" class="w-4 h-4 rounded-full">
                            <span class="text-heading capitalize text-sm">{{ language.name }}</span>
                        </li>
                    </ul>
                </div>
                <div class="hidden lg:flex items-center gap-4" v-if="!clientLogged">
                    <button @click="redirectLogin" class="px-4 py-2 text-sm font-medium text-white bg-[#D1992C] rounded-lg hover:bg-[#A6795E] transition-all">
                        {{ $t('button.login') }}
                    </button>
                    <button @click="redirectRegister" class="px-4 py-2 text-sm font-medium text-[#D1992C] border border-[#D1992C] rounded-lg hover:bg-[#D1992C] hover:text-white transition-all">
                        {{ $t('button.signup') }}
                    </button>
                </div>
                <div class="hidden lg:flex items-center gap-4" v-if="clientLogged">
                    <div class="dropdown-group">
                        <button class="dropdown-btn flex items-center gap-2">
                            <img class="flex-shrink-0 w-9 h-9 object-cover rounded-lg" :src="clientInfo.image" alt="avatar">
                            <h3 class="whitespace-nowrap text-sm capitalize text-left leading-[17px]">{{ $t('label.hello') }} <b
                                class="block font-semibold">{{ textShortener(clientInfo.name, 15) }}</b></h3>
                            <i class="lab lab-arrow-down text-xs ml-1.5 lab-font-size-14"></i>
                        </button>
                        <div
                            class="dropdown-list fixed sm:absolute top-[75px] sm:top-12 ltr:right-0 rtl:left-0 z-[60] rounded-xl w-full h-[calc(100vh_-_75px)] overflow-y-auto sm:h-auto sm:w-[300px] p-4 shadow-paper bg-white">
                            <div class="w-fit mx-auto text-center mb-5">
                                <figure
                                    class="relative z-10 w-[98px] h-[98px] border-2 border-dashed rounded-full inline-flex items-center justify-center border-white bg-gradient-to-t from-[#FF7A00] to-[#FF016C] before:absolute before:top-1/2 before:left-1/2 before:-translate-x-1/2 before:-translate-y-1/2 before:w-24 before:h-24 before:rounded-full before:-z-10 before:bg-white">
                                    <img class="w-[90px] h-[90px] rounded-full shadow-avatar" :src="clientInfo.image" alt="avatar">
                                </figure>

                                <label for="imageProperty"
                                       class="block w-11 h-11 mx-auto -mt-7 mb-3 relative z-10 rounded-full border-2 cursor-pointer bg-heading border-white">
                                    <input @change="saveImage" accept="image/png, image/jpeg, image/jpg" ref="imageProperty"
                                           type="file" id="imageProperty" class="w-full h-full rounded-full opacity-0 cursor-pointer">
                                    <i
                                        class="lab lab-edit-2 absolute top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 -z-10 lab-font-size-24 lab-font-color-1"></i>
                                </label>

                                <h3 class="font-medium text-sm leading-6 capitalize mb-0.5">{{ textShortener(clientInfo.name, 20) }}
                                </h3>
                                <p class="text-xs mb-0.5">{{ clientInfo.email }}</p>
                                <p dir="ltr" class="text-xs">{{ clientInfo.country_code }} {{ clientInfo.phone }}</p>
                                <h3 class="font-medium text-sm leading-6 capitalize mb-0.5">{{ clientInfo.balance }} <i class="fa-solid fa-coin-vertical text-[#d1992c]"></i></h3>
                            </div>
                            <nav>
                                <router-link @click="closeMobileMenu" :to="{ name: 'client.topup' }"
                                             class="paper-link transition w-full flex items-center gap-3.5 py-3 border-b last:border-none border-[#EFF0F6]">
                                    <i class="fa-solid fa-money-check-dollar"></i>
                                    <span class="text-sm leading-6 capitalize">{{ $t('button.top_up') }}</span>
                                </router-link>
                                <router-link @click="closeMobileMenu" :to="{ name: 'client.profile.editProfile' }"
                                             class="paper-link transition w-full flex items-center gap-3.5 py-3 border-b last:border-none border-[#EFF0F6]">
                                    <i class="lab lab-edit lab-font-size-17"></i>
                                    <span class="text-sm leading-6 capitalize">{{ $t('button.edit_profile') }}</span>
                                </router-link>

                                <router-link @click="closeMobileMenu" :to="{ name: 'client.profile.changePassword' }"
                                             class="paper-link transition w-full flex items-center gap-3.5 py-3 border-b last:border-none border-[#EFF0F6]">
                                    <i class="lab lab-key lab-font-size-17"></i>
                                    <span class="text-sm leading-6 capitalize">{{ $t('button.change_password') }}</span>
                                </router-link>

                                <button @click="logout()"
                                        class="paper-link transition w-full flex items-center gap-3.5 py-3 border-b last:border-none border-[#EFF0F6]">
                                    <i class="lab lab-logout lab-font-size-17"></i>
                                    <span class="text-sm leading-6 capitalize">{{ $t('button.logout') }}</span>
                                </button>
                            </nav>
                        </div>
                    </div>
                </div>
<!--                <button-->
<!--                    class="webcart hidden lg:flex items-center justify-center gap-1.5 w-fit rounded-3xl capitalize text-sm font-medium h-8 px-3 transition text-white bg-heading">-->
<!--                    <i class="fa-solid fa-bag-shopping text-sm"></i>-->
<!--                    <span class="whitespace-nowrap">-->
<!--                        {{ currencyFormat(subtotal, setting.site_digit_after_decimal_point,-->
<!--                        setting.site_default_currency_symbol, setting.site_currency_position) }}-->
<!--                    </span>-->
<!--                </button>-->
            </div>
        </div>
    </header>
</template>

<script>
import statusEnum from "../../../enums/modules/statusEnum";
import appService from "../../../services/appService";
import LoadingComponent from "../../frontend/components/LoadingComponent";
import activityEnum from "../../../enums/modules/activityEnum";
import alertService from "../../../services/alertService";

export default {
    name: "NavbarComponent",
    components: { LoadingComponent },
    data() {
        return {
            loading: {
                isActive: false,
            },
            searchItem: "",
            enums: {
                activityEnum: activityEnum,
            },
            languageProps: {
                paginate: 0,
                order_column: "id",
                order_type: "asc",
                status: statusEnum.ACTIVE
            },
            isMobileMenuOpen: false,
        }
    },
    computed: {
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
        language: function () {
            return this.$store.getters['frontendLanguage/show'];
        },
        languages: function () {
            return this.$store.getters['frontendLanguage/lists'];
        },
        subtotal: function () {
            return this.$store.getters['tableCart/subtotal'];
        },
        clientInfo: function () {
            return this.$store.getters.clientInfo
        },
        clientLogged: function (){
            return this.$store.getters.clientStatus
        },
        clientToken: function (){
            return this.$store.getters.clientToken
        }
    },
    mounted() {

        window.addEventListener('scroll', () => {
            const resetRoutes = [
                'table.tableOrder.details',
                'table.page',
                'table.checkout'
            ];
            if (this.$refs.ffHeader) {
                if (!resetRoutes.includes(this.$route.name)) {
                    if (window.scrollY > 0) {
                        this.$refs.ffHeader.classList.add('active');
                    } else {
                        this.$refs.ffHeader.classList.remove('active');
                    }
                } else {
                    this.$refs.ffHeader.classList.remove('active');
                }
            }
        });

        this.loading.isActive = true;
        this.$store.dispatch('frontendSetting/lists').then(res => {
            this.defaultLanguage = res.data.data.site_default_language;
            const globalState = this.$store.getters['globalState/lists'];

            if (globalState.language_id > 0) {
                this.defaultLanguage = globalState.language_id;
            }

            this.$store.dispatch('frontendLanguage/lists', this.languageProps).then().catch();
            this.$store.dispatch('frontendLanguage/show', this.defaultLanguage).then(res => {
                this.$i18n.locale = res.data.data.code;
                this.$store.dispatch("globalState/init", {
                    language_code: res.data.data.code
                });
            }).catch();

            // window.setTimeout(() => {
            //     this.$store.dispatch('tableDiningTable/show', this.$route.params.slug).then(res => {
            //         this.$store.dispatch('tableCart/initTable', res.data.data);
            //     }).catch((err) => { });
            // }, 300);

            this.loading.isActive = false;
        }).catch((err) => {
            this.loading.isActive = false;
        });
    },
    methods: {
        textShortener: function (text, number = 30) {
            return appService.textShortener(text, number);
        },
        toggleMobileMenu: function () {
            this.isMobileMenuOpen = !this.isMobileMenuOpen;
        },
        changeLanguage: function (id, code) {
            this.defaultLanguage = id;
            this.$store.dispatch("globalState/set", { language_id: id, language_code: code }).then(res => {
                this.$store.dispatch('frontendLanguage/show', id).then(res => {
                    this.$i18n.locale = res.data.data.code;
                }).catch();
            }).catch();
        },
        currencyFormat(amount, decimal, currency, position) {
            return appService.currencyFormat(amount, decimal, currency, position);
        },
        search: function () {
            if (typeof this.searchItem !== "undefined" && this.searchItem !== "") {
                this.$router.push({ name: "table.search", query: { s: this.searchItem } });
                this.searchItem = "";
            }
        },
        searchReset: function () {
            this.searchItem = "";
        },
        saveImage: function () {
            if (this.$refs.imageProperty.files[0]) {
                try {
                    this.loading.isActive = true;
                    const formData = new FormData();
                    formData.append("image", this.$refs.imageProperty.files[0]);
                    this.$store
                        .dispatch("frontendEditProfile/clientChangeImage", {
                            id: this.clientInfo.id,
                            form: formData,
                        })
                        .then((res) => {
                            this.$store.dispatch('updateClientInfo', res.data.data).then(res => {
                                this.loading.isActive = false;
                                alertService.success(this.$t("message.photo_update"));
                                this.$refs.imageProperty.value = null;
                            }).catch((err) => {
                                this.loading.isActive = false;
                                alertService.error(err);
                            });
                        })
                        .catch((err) => {
                            this.loading.isActive = false;
                            this.imageErrors = err.response.data.errors;
                        });
                } catch (err) {
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                }
            }
        },
        logout: function () {
            this.isMobileMenuOpen = false;
            this.$store.dispatch("clientLogout").then(res => {
                this.$router.push({ name: "client.login" });
            }).catch();
        },
        redirectQrCode() {
            this.isMobileMenuOpen = false;
            this.$router.push({ name: 'frontend.qr' });
        },
        redirectLogin() {
            this.isMobileMenuOpen = false;
            this.$router.push({ name: 'client.login' });
        },
        redirectRegister() {
            this.isMobileMenuOpen = false;
            this.$router.push({ name: 'client.register' });
        },
        closeMobileMenu: function(){
            this.isMobileMenuOpen = false;
        }
    }
}
</script>
