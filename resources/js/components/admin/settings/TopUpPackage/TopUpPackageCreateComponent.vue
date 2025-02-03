<template>
    <LoadingComponent :props="loading" />
    <SmModalCreateComponent :props="addButton" />
    <div id="modal" class="modal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title">{{ $t("menu.topup_package") }}</h3>
                <button class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500"
                        @click="reset"></button>
            </div>
            <div class="modal-body">
                <form @submit.prevent="save">
                    <div class="form-row">
                        <div class="form-col-12 sm:form-col-6">
                            <label for="title" class="db-field-title required">{{ $t("label.name") }}</label>
                            <input v-model="props.form.name" v-bind:class="errors.name ? 'invalid' : ''" type="text"
                                   id="name" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.name">{{
                                    errors.name[0]
                                }}</small>
                        </div>
                        <div class="form-col-12 sm:form-col-6">
                            <label for="title" class="db-field-title required">{{ $t("label.price") }}</label>
                            <input v-model="props.form.price" v-bind:class="errors.price ? 'invalid' : ''" type="text"
                                   id="price" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.price">{{
                                    errors.price[0]
                                }}</small>
                        </div>
                        <div class="form-col-12 sm:form-col-6">
                            <label for="title" class="db-field-title required">{{ $t("label.value") }}</label>
                            <input v-model="props.form.points" v-bind:class="errors.points ? 'invalid' : ''" type="text"
                                   id="price" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.points">{{
                                    errors.points[0]
                                }}</small>
                        </div>
                        <div class="form-col-12 sm:form-col-6">
                            <label for="title" class="db-field-title required">{{ $t("label.bonus") }}</label>
                            <input v-model="props.form.bonus" v-bind:class="errors.bonus ? 'invalid' : ''" type="text"
                                   id="price" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.bonus">{{
                                    errors.bonus[0]
                                }}</small>
                        </div>
                        <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title required" for="active">{{ $t("label.status") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.status" id="active"
                                               type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="active" class="db-field-label">{{ $t("label.active") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.status"
                                               type="radio" id="inactive" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="inactive" class="db-field-label">{{ $t("label.inactive") }}</label>
                                </div>
                            </div>
                            <small class="db-field-alert" v-if="errors.status">{{ errors.status[0] }}</small>
                        </div>
                        <div class="form-col-12">
                            <div class="modal-btns">
                                <button type="button" class="modal-btn-outline modal-close" @click="reset">
                                    <i class="lab lab-close"></i>
                                    <span>{{ $t("button.close") }}</span>
                                </button>

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
import SmModalCreateComponent from "../../components/buttons/SmModalCreateComponent";
import LoadingComponent from "../../components/LoadingComponent";
import statusEnum from "../../../../enums/modules/statusEnum";
import alertService from "../../../../services/alertService";
import appService from "../../../../services/appService";
export default {
    name:"TopUpPackageCreateComponent",
    components:{
        SmModalCreateComponent, LoadingComponent
    },
    props:[
        'props'
    ],
    data(){
        return {
            loading: {
                isActive: false,
            },
            enums: {
                statusEnum: statusEnum,
                statusEnumArray: {
                    [statusEnum.ACTIVE]: this.$t("label.active"),
                    [statusEnum.INACTIVE]: this.$t("label.inactive"),
                },
            },
            errors: {},
        }
    },
    computed:{
        addButton: function(){
            return { title: this.$t('button.add_package') };
        }
    },
    methods:{
        reset: function () {
            appService.modalHide();
            this.$store.dispatch("topupPackage/reset")
            this.errors = {};
            this.$props.props.form = {
                name:"",
                price:0,
                points:0,
                bonus:0,
                status: statusEnum.ACTIVE,
            }
        },
        save: function (){
            try {
                const fd = new FormData();
                fd.append("name", this.props.form.name);
                fd.append("price", this.props.form.price);
                fd.append("points", this.props.form.points);
                fd.append("bonus", this.props.form.bonus);
                fd.append("status", this.props.form.status);
                const tempId = this.$store.getters["topupPackage/temp"].temp_id;
                this.loading.isActive = true;
                this.$store
                    .dispatch("topupPackage/save",{
                        form:fd,
                        search: this.props.search
                    })
                    .then((res)=>{

                        appService.modalHide();
                        this.loading.isActive = false
                        // alert service
                        alertService.successFlip(
                            tempId === null ? 0 : 1,
                            this.$t("menu.topup_package")
                        );
                        this.props.form = {
                            name: "",
                            price: 0,
                            points: 0,
                            bonus: 0,
                            status: statusEnum.ACTIVE,
                        };
                        this.errors = {};
                    })
                    .catch((err) => {
                        this.loading.isActive = false;
                        this.errors = err.response.data.errors;
                    });
            }catch (err){
                this.loading.isActive = false;
                alertService.error(err);
            }
        }
    }
}
</script>
