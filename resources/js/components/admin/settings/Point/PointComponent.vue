<template>
    <LoadingComponent :props="loading" />

    <div id="point" class="db-card db-tab-div active">
        <div class="db-card-header">
            <h3 class="db-card-title">{{ $t("menu.point_exchange_rate") }}</h3>
        </div>
        <div class="db-card-body">
            <form @submit.prevent="save">
                <div class="form-row">
                    <div class="form-col-12 sm:form-col-6">
                        <label for="unit" class="db-field-title required">
                            {{ $t("label.unit") }}
                        </label>
                        <div class="relative flex items-center">
                            <input v-model="form.unit" v-bind:class="errors.unit ? 'invalid' : ''" type="text"
                                   id="unit" class="db-field-control flex-1" />
                            <span class="absolute right-5 text-sm text-gray-500">
                                <i class="fa-solid fa-coin-vertical text-primary"></i>
                            </span>
                        </div>

                        <small class="db-field-alert" v-if="errors.unit">{{ errors.unit[0] }}</small>
                    </div>

                    <div class="form-col-12 sm:form-col-6">
                        <label for="rate" class="db-field-title required">
                            {{ $t("label.rate") }}
                        </label>
                        <div class="relative flex items-center">
                            <input
                                v-model="form.rate"
                                v-bind:class="errors.rate ? 'invalid' : ''"
                                type="text"
                                id="rate"
                                class="db-field-control flex-1"
                            />
                            <span class="absolute right-5 text-sm text-gray-500">
                                {{ site_default_currency || "" }}
                            </span>
                        </div>
                        <small class="db-field-alert" v-if="errors.rate">{{ errors.rate[0] }}</small>
                    </div>
                    <div class="form-col-12">
                        <button type="submit" class="db-btn text-white bg-primary">
                            <i class="lab lab-save"></i>
                            <span>{{ $t("button.save") }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    import LoadingComponent from "../../components/LoadingComponent.vue";
    import alertService from "../../../../services/alertService";
    export default {
        name:"PointComponent",
        components: {LoadingComponent},
        data() {
            return {
                loading: {
                    isActive: false,
                },
                form:{
                    unit:null,
                    rate:null
                },
                errors: {},
                site_default_currency:null
            };
        },
        mounted() {
            this.loadSettings()
        },
        methods:{
            save:function(){
                this.loading.isActive = true;
                this.$store.dispatch('point/save',this.form).then((res)=>{
                    alertService.success(res.data?.message);
                    this.errors = {};
                    this.loading.isActive = false;
                })
                .catch((err) => {
                    this.errors = err.response?.data?.errors || {};
                    alertService.error("Failed to update exchange rate");
                    this.loading.isActive = false;
                });
            },
            loadSettings:function (){
                this.$store.dispatch('frontendSetting/lists').then((res)=>{
                    this.site_default_currency = res.data?.data?.site_default_currency_symbol
                })
                .catch((err) => {
                    alertService.error("Failed to load site settings.");
                });
                this.$store.dispatch('point/settings').then((res)=>{
                    this.form.rate = res.data?.rate || null;
                    this.form.unit = res.data?.unit || null;
                    this.loading.isActive = false;
                }).catch((err) => {
                    alertService.error("Failed to load point exchange settings.");
                    this.loading.isActive = false;
                });
            }
        }
    }
</script>
