<template>
    <LoadingComponent :props="loading" />
    <div class="col-12 mt-[60px] md:mt-[74px] container min-h-[85vh]">
        <div class="db-card">
            <div class="db-card-header">
                <h3 class="db-card-title">{{ $t("label.change_password") }}</h3>
            </div>
            <div class="db-card-body">
                <form @submit.prevent="changePassword">
                    <div class="form-row">
                        <!-- Old Password -->
                        <div class="form-col-12 sm:form-col-6 relative">
                            <label for="old_password" class="db-field-title required">
                                {{ $t('label.old_password') }}
                            </label>
                            <div class="relative">
                                <input
                                    v-model="form.old_password"
                                    :type="showOldPassword ? 'text' : 'password'"
                                    :class="errors.old_password ? 'invalid' : ''"
                                    id="old_password"
                                    class="db-field-control"
                                />
                                <i
                                    class="absolute right-3 top-[50%] translate-y-[-50%] cursor-pointer"
                                    :class="showOldPassword ? 'fa fa-eye-slash' : 'fa fa-eye'"
                                    @click="togglePasswordVisibility('old')"
                                ></i>
                            </div>

                            <small class="db-field-alert" v-if="errors.old_password">
                                {{ errors.old_password[0] }}
                            </small>
                        </div>

                        <!-- New Password -->
                        <div class="form-col-12 sm:form-col-6 relative">
                            <label for="password" class="db-field-title required">
                                {{ $t("label.password") }}
                            </label>
                            <div class="relative">
                                <input
                                    v-model="form.password"
                                    :type="showNewPassword ? 'text' : 'password'"
                                    :class="errors.password ? 'invalid' : ''"
                                    id="password"
                                    class="db-field-control"
                                    autocomplete="off"
                                />
                                <i
                                    class="absolute right-3 top-[50%] translate-y-[-50%] cursor-pointer"
                                    :class="showNewPassword ? 'fa fa-eye-slash' : 'fa fa-eye'"
                                    @click="togglePasswordVisibility('new')"
                                ></i>
                            </div>

                            <small class="db-field-alert" v-if="errors.password">
                                {{ errors.password[0] }}
                            </small>
                        </div>

                        <!-- Confirm Password -->
                        <div class="form-col-12 sm:form-col-6 relative">
                            <label for="confirm_password" class="db-field-title required">
                                {{ $t("label.confirm_password") }}
                            </label>
                            <div class="relative">
                                <input
                                    v-model="form.password_confirmation"
                                    :type="showConfirmPassword ? 'text' : 'password'"
                                    :class="errors.password_confirmation ? 'invalid' : ''"
                                    id="confirm_password"
                                    class="db-field-control"
                                    autocomplete="off"
                                />
                                <i
                                    class="absolute right-3 top-[50%] translate-y-[-50%] cursor-pointer"
                                    :class="showConfirmPassword ? 'fa fa-eye-slash' : 'fa fa-eye'"
                                    @click="togglePasswordVisibility('confirm')"
                                ></i>
                            </div>

                            <small class="db-field-alert" v-if="errors.password_confirmation">
                                {{ errors.password_confirmation[0] }}
                            </small>
                        </div>

                        <!-- Submit Button -->
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
import alertService from "../../../services/alertService";
import LoadingComponent from "../components/LoadingComponent";

export default {
    name: "ClientProfileChangePasswordComponent",
    components: {
        LoadingComponent
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            form: {
                old_password: "",
                password: "",
                password_confirmation: "",
            },
            errors: {},
            showOldPassword: false,
            showNewPassword: false,
            showConfirmPassword: false,
        }
    },
    methods: {
        changePassword: function () {
            try {
                this.loading.isActive = true;
                this.$store.dispatch("frontendEditProfile/changeClientPassword", this.form).then((res) => {
                    this.loading.isActive = false;
                    alertService.successFlip(
                        res.config.method === "put" ?? 0,
                        this.$t("menu.password")
                    );
                    this.form = {
                        old_password: "",
                        password: "",
                        password_confirmation: "",
                    };
                    this.errors = {};
                    this.showOldPassword = false;
                    this.showNewPassword = false;
                    this.showConfirmPassword = false;
                }).catch((err) => {
                    this.loading.isActive = false;
                    this.errors = err.response.data.errors;
                });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err);
            }
        },
        togglePasswordVisibility: function (type) {
            if (type === "old") {
                this.showOldPassword = !this.showOldPassword;
            } else if (type === "new") {
                this.showNewPassword = !this.showNewPassword;
            } else if (type === "confirm") {
                this.showConfirmPassword = !this.showConfirmPassword;
            }
        },
    }
}
</script>
