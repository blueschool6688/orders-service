<template>
    <LoadingComponent :props="loading" />
    <div class="col-12 mt-[60px] md:mt-[74px] container min-h-[85vh]">
        <div class="p-6 rounded-xl mb-8 shadow-xs bg-white">
            <div class="flex flex-wrap gap-4 sm:gap-6">
                <img class="w-[120px] h-[120px] object-cover rounded-lg" :src="previewImage" alt="avatar" />
                <div>
                    <h3 class="text-[26px] font-semibold font-rubik leading-[40px] capitalize">
                        {{ textShortener(customerName, 20) }}
                    </h3>
                    <label
                        class="p-0.5 px-2 rounded text-[10px] leading-4 font-medium font-rubik uppercase mb-[22px] text-[#E89806] bg-[#FFF5DE]">
                        {{ $t("label.customer") }}
                    </label>
                    <form @submit.prevent="saveImage">
                        <div class="flex gap-3 md:gap-4">
                            <label for="photo"
                                   class="db-btn relative cursor-pointer h-[38px] shadow-[0px_6px_10px_rgba(23,_114,_255,_0.24)] bg-primary text-white">
                                <i class="lab lab-upload-image"></i>
                                <span class="hidden sm:inline-block">{{
                                        $t("button.upload_new_photo")
                                    }}</span>
                                <input v-if="uploadButton" @change="changePreviewImage" ref="imageProperty"
                                       accept="image/png, image/jpeg, image/jpg" type="file" id="photo"
                                       class="absolute top-0 left-0 w-full h-full -z-10 opacity-0" />
                            </label>
                            <button v-if="saveButton" type="submit"
                                    class="db-btn h-[38px] shadow-[0px_6px_10px_rgba(26,_183,_89,_0.24)] text-white bg-[#1AB759]">
                                <i class="lab lab-tick-circle-2"></i>
                                <span class="hidden sm:inline-block">{{ $t("button.save") }}</span>
                            </button>
                            <button v-if="resetButton" @click="resetPreviewImage" type="button"
                                    class="db-btn-outline h-[38px] shadow-[0px_6px_10px_rgba(251,_78,_78,_0.24)] !text-[#FB4E4E] !bg-white !border-[#FB4E4E]">
                                <i class="lab lab-reset"></i>
                                <span class="hidden sm:inline-block">{{ $t("button.reset") }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="db-card">
            <div class="db-card-header">
                <h3 class="db-card-title">{{ $t("button.edit_profile") }}</h3>
            </div>

            <div class="db-card-body">
                <form @submit.prevent="save">
                    <div class="form-row">
                        <div class="form-col-12 sm:form-col-6">
                            <label for="first_name" class="db-field-title required">{{ $t('label.first_name') }}</label>
                            <input type="text" id="first_name" class="db-field-control" v-model="form.first_name"
                                   :class="errors.first_name ? 'invalid' : ''">
                            <small class="db-field-alert" v-if="errors.first_name">
                                {{ errors.first_name[0] }}
                            </small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="last_name" class="db-field-title required">{{ $t('label.last_name') }}</label>
                            <input type="text" id="last_name" class="db-field-control" v-model="form.last_name"
                                   :class="errors.last_name ? 'invalid' : ''">
                            <small class="db-field-alert" v-if="errors.last_name">
                                {{ errors.last_name[0] }}
                            </small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="email" class="db-field-title required">{{ $t('label.email') }}</label>
                            <input type="text" id="email" class="db-field-control" v-model="form.email"
                                   :class="errors.email ? 'invalid' : ''">
                            <small class="db-field-alert" v-if="errors.email">
                                {{ errors.email[0] }}
                            </small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="phone" class="db-field-title required">{{ $t('label.phone') }}</label>
                            <div :class="errors.phone ? 'invalid' : ''" class="db-field-control flex items-center">
                                <select
                                    v-model="form.country_code"
                                    @change="onChangeCountryCode(form.country_code)"
                                    class="form-select w-[80px]">
                                    <option v-for="code in countryCodes" :key="code.calling_code" :value="code.calling_code">
                                        {{ code.flag_emoji }} {{ code.calling_code }}
                                    </option>
                                </select>
                                <input
                                    class="pl-2 text-sm w-full h-full"
                                    v-model="form.phone"
                                    v-on:keypress="phoneNumber($event)"
                                    type="text"
                                    id="phone" />
                            </div>
                            <small class="db-field-alert" v-if="errors.phone">
                                {{ errors.phone[0] }}
                            </small>
                        </div>

                        <div class="form-col-12">
                            <div class="flex flex-wrap gap-3">
                                <button type="submit" class="db-btn py-2 text-white bg-primary">
                                    <i class="lab lab-save"></i>
                                    <span>{{ $t("button.save") }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../components/LoadingComponent";
import alertService from "../../../services/alertService";
import appService from "../../../services/appService";

export default {
    name: "ClientEditProfileComponent",
    components: {
        LoadingComponent
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            form: {
                first_name: "",
                last_name: "",
                email: "",
                phone: "",
                country_code: ""
            },
            defaultImage: null,
            previewImage: null,
            uploadButton: true,
            resetButton: false,
            saveButton: false,
            customerId: null,
            flag: "",
            errors: {},
        }
    },
    computed: {
        countryCodes() {
            return this.$store.getters["countryCode/lists"];
        },
        customerName(){
            const info = this.$store.getters.clientInfo;
            return info.name ?? "N/A";
        }
    },
    mounted() {
        try {
            this.loading.isActive = true;
            this.$store.dispatch('frontendSetting/lists').then(res => {
                const profile = this.$store.getters.clientInfo;
                console.log(profile)
                this.form.first_name = profile.first_name;
                this.form.last_name = profile.last_name;
                this.form.email = profile.email;
                this.form.phone = profile.phone;
                this.customerId = profile.id
                this.defaultImage = profile.image;
                this.previewImage = profile.image;
                this.$store.dispatch('frontendCountryCode/show', res.data.data.company_country_code).then(res => {
                    this.flag = res.data.data.flag_emoji;

                    this.form.country_code = profile.country_code ?? res.data.data.calling_code;
                }).catch()
            }).catch();
            this.$store.dispatch("countryCode/lists").then(() => {
            }).catch((err) => {
                console.error("Lỗi khi tải danh sách mã quốc gia:", err);
            });
            this.loading.isActive = false;
        } catch (err) {
            this.loading.isActive = false;
            alertService.error(err);
        }
    },
    methods: {
        textShortener: function (text, number = 30) {
            return appService.textShortener(text, number);
        },
        phoneNumber(e) {
            return appService.phoneNumber(e);
        },
        save: function () {
            try {
                this.loading.isActive = true;
                this.$store.dispatch("frontendEditProfile/updateClientProfile", this.form).then((res) => {
                    this.$store.dispatch('updateClientInfo', res.data.data).then(res => {
                        this.loading.isActive = false;
                        this.customerName = res.data?.data?.name
                        alertService.successFlip(1, this.$t("menu.profile"));
                        this.errors = {};
                    }).catch((err) => {
                        this.loading.isActive = false;
                        alertService.error(err);
                    });
                }).catch((err) => {
                    this.loading.isActive = false;
                    this.errors = err.response.data.errors;
                });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err);
            }
        },
        changePreviewImage: function (e) {
            if (e.target.files[0]) {
                this.previewImage = URL.createObjectURL(e.target.files[0]);
                this.saveButton = true;
                this.resetButton = true;
            }
        },
        resetPreviewImage: function () {
            this.$refs.imageProperty.value = null;
            this.previewImage = this.defaultImage;
            this.saveButton = false;
            this.resetButton = false;
        },
        saveImage: function () {
            if (this.$refs.imageProperty?.files[0]) {
                try {
                    this.loading.isActive = true;
                    const formData = new FormData();
                    formData.append("image", this.$refs.imageProperty.files[0]);
                    this.$store
                        .dispatch("customer/changeImage", {
                            id: this.customerId,
                            form: formData,
                        })
                        .then((res) => {
                            const updatedImage = res.data?.data?.image || this.defaultImage;
                            this.$store.dispatch('updateClientInfo', res.data.data).then(() => {
                                this.loading.isActive = false;
                                alertService.success(this.$t("message.photo_update"));
                                this.$refs.imageProperty.value = null;
                                this.defaultImage = updatedImage;
                                this.previewImage = updatedImage;
                                this.saveButton = false;
                                this.resetButton = false;
                            }).catch((err) => {
                                this.loading.isActive = false;
                                alertService.error(err);
                            });
                        })
                        .catch((err) => {
                            this.loading.isActive = false;
                            alertService.error(err.response?.data?.message || this.$t("message.error_occurred"));
                        });
                } catch (err) {
                    this.loading.isActive = false;
                    alertService.error(err.message || this.$t("message.error_occurred"));
                }
            }
        },
        onChangeCountryCode:function (selectedCode){
            const selectedCountry = this.countryCodes.find((code) => code.calling_code === selectedCode);
            if (selectedCountry) {
                this.form.country_code = selectedCountry.calling_code;
                this.flag = selectedCountry.flag_emoji;
            }
        }
    }
}
</script>
