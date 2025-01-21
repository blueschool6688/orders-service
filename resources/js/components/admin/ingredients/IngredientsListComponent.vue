<template>
    <LoadingComponent :props="loading"/>
    <div class="col-12">
        <div class="db-card">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t('menu.ingredients') }}</h3>
                <div class="db-card-filter">
                    <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                    <FilterComponent />
                    <IngredientCreateComponent :props="props" :isEdit="isEdit" @resetEditMode="resetEditMode"/>
                </div>
            </div>
            <div class="table-filter-div">
                <form class="p-4 sm:p-5 mb-5" @submit.prevent="search">
                    <div class="row">
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="name" class="db-field-title after:hidden">{{
                                    $t("label.name")
                                }}</label>
                            <input id="name" v-model="props.search.name" type="text" class="db-field-control" />
                        </div>
                        <div class="col-12">
                            <div class="flex flex-wrap gap-3 mt-4">
                                <button class="db-btn py-2 text-white bg-primary">
                                    <i class="lab lab-search-line lab-font-size-16"></i>
                                    <span>{{ $t("button.search") }}</span>
                                </button>
                                <button class="db-btn py-2 text-white bg-gray-600" @click="clear">
                                    <i class="lab lab-cross-line-2 lab-font-size-22"></i>
                                    <span>{{ $t("button.clear") }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="db-table-responsive">
                <table class="db-table stripe" id="print" :dir="direction">
                    <thead class="db-table-head">
                    <tr class="db-table-head-tr">
                        <th class="db-table-head-th">
                            {{ $t('label.name') }}
                        </th>
                        <th class="db-table-head-th">
                            {{ $t('label.quantity') }}
                        </th>
                        <th class="db-table-head-th">
                            {{ $t('label.ingredient_max_quantity') }}
                        </th>
                        <th class="db-table-head-th">
                            {{ $t('label.ingredient_unit') }}
                        </th>
                        <th class="db-table-head-th hidden-print">
                            {{ $t('label.action') }}
                        </th>
                    </tr>
                    </thead>
                    <tbody class="db-table-body" v-if="ingredients.length > 0">
                    <tr class="db-table-body-tr" v-for="item in ingredients" :key="item">
                        <td class="db-table-body-td">
                            {{ textShortener(item.name, 40) }}
                        </td>
                        <td class="db-table-body-td">{{ item.quantity }}</td>
                        <td class="db-table-body-td">{{ item.max_quantity }}</td>
                        <td class="db-table-body-td">
                            {{ item.unit }}
                        </td>
                        <td class="db-table-body-td hidden-print">
                            <div class="flex justify-start items-center sm:items-start sm:justify-start gap-1.5">
                                <SmIconSidebarModalEditComponent @click="edit(item)"/>
                                <SmIconDeleteComponent @click="destroy(item.id)"/>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-6">
                <PaginationSMBox :pagination="pagination" :method="list" />
                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                    <PaginationTextComponent :props="{ page: paginationPage }" />
                    <PaginationBox :pagination="pagination" :method="list" />
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import LoadingComponent from "../components/LoadingComponent";
import alertService from "../../../services/alertService";
import appService from "../../../services/appService";
import ExcelComponent from "../components/buttons/export/ExcelComponent.vue";
import ExportComponent from "../components/buttons/export/ExportComponent.vue";
import UploadFileComponent from "../components/buttons/import/UploadFileComponent.vue";
import TableLimitComponent from "../components/TableLimitComponent.vue";
import ItemUploadComponent from "../items/ItemUploadComponent.vue";
import SampleFileComponent from "../components/buttons/import/SampleFileComponent.vue";
import PrintComponent from "../components/buttons/export/PrintComponent.vue";
import ItemCreateComponent from "../items/ItemCreateComponent.vue";
import ImportComponent from "../components/buttons/import/ImportComponent.vue";
import FilterComponent from "../components/buttons/collapse/FilterComponent.vue";
import SmIconViewComponent from "../components/buttons/SmIconViewComponent.vue";
import SmIconSidebarModalEditComponent from "../components/buttons/SmIconSidebarModalEditComponent.vue";
import SmIconDeleteComponent from "../components/buttons/SmIconDeleteComponent.vue";
import displayModeEnum from "../../../enums/modules/displayModeEnum";
import PaginationBox from "../components/pagination/PaginationBox.vue";
import PaginationSMBox from "../components/pagination/PaginationSMBox.vue";
import PaginationTextComponent from "../components/pagination/PaginationTextComponent.vue";
import IngredientCreateComponent from "./IngredientCreateComponent.vue";
import askEnum from "../../../enums/modules/askEnum";
import itemTypeEnum from "../../../enums/modules/itemTypeEnum";
import statusEnum from "../../../enums/modules/statusEnum";

    export default {
        name:"IngredientsListComponent",
        components: {
            PaginationTextComponent, PaginationSMBox, PaginationBox,
            SmIconDeleteComponent, SmIconSidebarModalEditComponent, SmIconViewComponent,
            FilterComponent,
            ImportComponent, ItemCreateComponent,
            PrintComponent, SampleFileComponent, ItemUploadComponent, TableLimitComponent, UploadFileComponent,
            ExportComponent, ExcelComponent, LoadingComponent, IngredientCreateComponent

        },
        data(){
          return {
              loading: {
                  isActive: false
              },
              props: {
                  search: {
                      paginate: 1,
                      page: 1,
                      per_page: 10,
                      order_column: 'id',
                      order_type: 'desc',
                      name: "",
                  },
                  form: {
                      name: "",
                      quantity:0,
                      unit: "",
                      max_quantity:0
                  },
              },
              isEdit: false,
              printLoading: true,
              printObj: {
                  id: "print",
                  popTitle: this.$t("menu.ingredients"),
              },
          }
        },
        computed:{
            ingredients: function () {
                return this.$store.getters['ingredient/list']
            },
            pagination: function(){
                return this.$store.getters['ingredient/pagination']
            },
            paginationPage: function () {
                return this.$store.getters['ingredient/page']
            },
            direction: function () {
                return this.$store.getters['frontendLanguage/show'].display_mode === displayModeEnum.RTL ? 'rtl' : 'ltr';
            },
        },
        mounted() {
            this.list()
        },
        methods: {
            resetEditMode() {
                this.isEdit = false;
            },
            textShortener: function (text, number = 30) {
                return appService.textShortener(text, number);
            },
            numberOnly: function (e) {
                return appService.floatNumber(e);
            },
            search: function () {
                this.list();
            },
            clear: function () {
                this.props.search.paginate = 1;
                this.props.search.page = 1;
                this.props.search.name = "";
                this.list();
            },
            list: function (page = 1){
                this.loading.isActive = true;
                this.props.search.page = page;
                this.$store.dispatch('ingredient/lists',this.props.search).then(res => {
                    this.loading.isActive = false
                }).catch(err => {
                    this.loading.isActive  = false
                })
            },
            edit: function (item) {
                appService.sideDrawerShow();
                this.loading.isActive = true;
                this.$store.dispatch('ingredient/edit', item.id);
                this.loading.isActive = false;
                this.props.errors = {};
                this.props.form = {
                    name: item.name,
                    quantity: item.quantity,
                    unit: item.unit,
                    max_quantity: item.max_quantity
                };
                this.isEdit = true
            },
            destroy: function (id) {
                appService.destroyConfirmation().then((res) => {
                    try {
                        this.loading.isActive = true;
                        this.$store.dispatch('ingredient/destroy', { id: id, search: this.props.search }).then((res) => {
                            this.loading.isActive = false;
                            alertService.successFlip(null, this.$t('menu.ingredients'));
                        }).catch((err) => {
                            this.loading.isActive = false;
                            alertService.error(err.response.data.message);
                        })
                    } catch (err) {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    }
                }).catch((err) => {
                    this.loading.isActive = false;
                })
            },
            xls: function () {
                // this.loading.isActive = true;
                // this.$store.dispatch("item/export", this.props.search).then((res) => {
                //     this.loading.isActive = false;
                //     const blob = new Blob([res.data], {
                //         type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                //     });
                //     const link = document.createElement("a");
                //     link.href = URL.createObjectURL(blob);
                //     link.download = this.$t("menu.items");
                //     link.click();
                //     URL.revokeObjectURL(link.href);
                // }).catch((err) => {
                //     this.loading.isActive = false;
                //     alertService.error(err.response.data.message);
                // });
            },
            downloadSample:function(){
                // this.loading.isActive = true;
            },
            uploadModal: function(id){
                appService.modalShow(id);
            },

        }
    }
</script>
