<template>
    <LoadingComponent :props="loading" />
    <SmSidebarModalCreateComponent :props="addButton" />
    <div id="sidebar" class="drawer">
        <div class="drawer-header">
            <h3 class="drawer-title">{{ $t("menu.ingredients") }}</h3>
            <button class="fa-solid fa-xmark close-btn" @click="reset"></button>
        </div>
        <div class="drawer-body">
            <form @submit.prevent="save">
                <div class="form-row">
                    <div class="form-col-12 sm:form-col-6">
                        <label for="name" class="db-field-title required">{{ $t("label.name") }}</label>
                        <input v-model="props.form.name" v-bind:class="errors.name ? 'invalid' : ''" type="text"
                               id="name" class="db-field-control">
                        <small class="db-field-alert" v-if="errors.name">{{ errors.name[0] }}</small>
                    </div>

                    <div class="form-col-12 sm:form-col-6" v-if="isEdit">
                        <label for="current-quantity" class="db-field-title">{{ $t("label.current_quantity") }}</label>
                        <input v-model="props.form.quantity" type="text" id="current-quantity" class="db-field-control border-none outline-none"
                               disabled>
                    </div>

                    <div class="form-col-12 sm:form-col-6" v-if="isEdit">
                        <label for="quantity-change" class="db-field-title required">{{ $t("label.quantity_change") }}</label>
                        <input
                            v-model="quantityChange"
                            @input="validateQuantityChange"
                            type="number"
                            id="quantity-change"
                            class="db-field-control"
                            :class="errors.quantity_change ? 'invalid' : ''"
                        />
                        <small class="db-field-alert" v-if="errors.quantity_change">{{ errors.quantity_change[0] }}</small>
                    </div>

                    <div class="form-col-12 sm:form-col-6" v-if="isEdit">
                        <label for="change-type" class="db-field-title required">{{ $t("label.change_type") }}</label>
                        <select v-model="changeType" id="change-type" class="db-field-control">
                            <option value="add">{{ $t("label.add") }}</option>
                            <option value="subtract">{{ $t("label.subtract") }}</option>
                        </select>
                    </div>

                    <div class="form-col-12 sm:form-col-6" v-else>
                        <label for="quantity" class="db-field-title required">{{ $t("label.quantity") }}</label>
                        <input v-model="props.form.quantity" v-bind:class="errors.quantity ? 'invalid' : ''" type="text"
                               id="quantity" class="db-field-control">
                        <small class="db-field-alert" v-if="errors.quantity">{{ errors.quantity[0] }}</small>
                    </div>

                    <div class="form-col-12 sm:form-col-6">
                        <label for="unit" class="db-field-title required">{{ $t("label.ingredient_unit") }}</label>
                        <input v-model="props.form.unit" v-bind:class="errors.unit ? 'invalid' : ''" type="text"
                               id="price" class="db-field-control">
                        <small class="db-field-alert" v-if="errors.unit">{{ errors.unit[0] }}</small>
                    </div>
                    <div class="col-12">
                        <div class="flex flex-wrap gap-3 mt-4">
                            <button type="submit" class="db-btn py-2 text-white bg-primary">
                                <i class="lab lab-save"></i>
                                <span>{{ $t("label.save") }}</span>
                            </button>
                            <button type="button" class="modal-btn-outline modal-close" @click="reset">
                                <i class="lab lab-close"></i>
                                <span>{{ $t("button.close") }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

</template>
<script>
import SmSidebarModalCreateComponent from "../components/buttons/SmSidebarModalCreateComponent";
import alertService from "../../../services/alertService";
import appService from "../../../services/appService";
import LoadingComponent from "../components/LoadingComponent";
import askEnum from "../../../enums/modules/askEnum";
import itemTypeEnum from "../../../enums/modules/itemTypeEnum";
import statusEnum from "../../../enums/modules/statusEnum";

export default {
    name:"IngredientCreateComponent",
    components:{ SmSidebarModalCreateComponent, LoadingComponent },
    props:['props','isEdit'],
    data(){
        return {
            loading: {
                isActive: false
            },
            errors: {},
            quantityChange:0,
            changeType: "add",
        }
    },
    computed:{
        addButton: function () {
            return { title: this.$t('button.add_ingredient') };
        },
    },
    mounted() {
        this.loading.isActive = true;
        this.loading.isActive = false;
    },
    methods:{
        validateQuantityChange() {
            if (isNaN(this.quantityChange) || parseFloat(this.quantityChange) < 0) {
                this.quantityChange = 0;
            } else {
                this.quantityChange = Math.abs(parseFloat(this.quantityChange));
            }
        },
        reset: function(){
            appService.sideDrawerHide();
            this.$store.dispatch('ingredient/reset').then().catch();
            this.errors = {};
            this.$props.props.form = {
                name: "",
                quantity: 0,
                max_quantity: 0,
                unit: "",
            };
            this.$props.isEdit = false
            this.changeType = "add"
            this.quantityChange = 0
        },
        save:function(){
            try {
                const fd = new FormData();
                let finalQuantity = parseFloat(this.props.form.quantity);
                if (this.isEdit) {
                    finalQuantity =
                        this.changeType === "add"
                            ? finalQuantity + parseFloat(this.quantityChange)
                            : finalQuantity - parseFloat(this.quantityChange);
                }
                if (finalQuantity < 0){
                    alertService.error(this.$t('message.quantity_not_allow_less_than_zero'))
                    return;
                }
                fd.append('quantity', finalQuantity);
                fd.append('name', this.props.form.name);
                fd.append('max_quantity', this.props.form.max_quantity);
                fd.append('unit', this.props.form.unit);

                const tempId = this.$store.getters['item/temp'].temp_id;
                this.loading.isActive = true;

                this.$store.dispatch('ingredient/save',{
                    form:fd,
                    search: this.props.search
                }).then(res =>{
                    appService.sideDrawerHide()
                    this.loading.isActive = false;
                    alertService.successFlip((tempId === null ? 0 : 1), this.$t('menu.ingredients'));
                    this.props.form = {
                        name:"",
                        quantity:0,
                        unit:""
                    }
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    this.errors = {};
                    if (err.response && err.response.data && err.response.data.errors) {
                        this.errors = err.response.data.errors;
                    } else {
                        alertService.error(err.response.data.message);
                    }
                })
            }
            catch (err) {
                this.loading.isActive = false;
                alertService.error(err)
            }
        }
    }
}
</script>
