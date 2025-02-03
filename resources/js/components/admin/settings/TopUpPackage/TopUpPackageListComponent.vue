<template>
    <LoadingComponent :props="loading" />
    <div class="db-card db-tab-div active">
        <div class="db-card-header border-none">
            <h3 class="db-card-title">{{ $t("menu.topup_package") }}</h3>
            <div class="db-card-filter">
                <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                <TopUpPackageCreateComponent :props="props"/>
            </div>
        </div>

        <div class="db-table-responsive">
            <table class="db-table stripe">
                <thead class="db-table-head">
                <tr class="db-table-head-tr">
                    <th class="db-table-head-th">
                        {{ $t("label.name") }}
                    </th>
                    <th class="db-table-head-th">
                        {{ $t("label.price") }}
                    </th>
                    <th class="db-table-head-th">
                        {{ $t("label.value") }}
                    </th>
                    <th class="db-table-head-th">
                        {{ $t("label.bonus") }}
                    </th>
                    <th class="db-table-head-th">
                        {{ $t("label.status") }}
                    </th>
                    <th class="db-table-head-th">
                        {{ $t("label.action") }}
                    </th>
                </tr>
                </thead>
                <tbody class="db-table-body" v-if="topupPackages.length > 0">
                <tr class="db-table-body-tr" v-for="topupPackage in topupPackages" :key="topupPackage">
                    <td class="db-table-body-td">
                        {{ textShortener(topupPackage.name) }}
                    </td>
                    <td class="db-table-body-td">
                        {{ topupPackage.price }}
                    </td>
                    <td class="db-table-body-td">
                        {{ topupPackage.points }}
                    </td>
                    <td class="db-table-body-td">
                        {{ topupPackage.bonus }}
                    </td>
                    <td class="db-table-body-td">
                            <span :class="statusClass(topupPackage.status)">
                                {{ enums.statusEnumArray[topupPackage.status] }}
                            </span>
                    </td>
                    <td class="db-table-body-td">
                        <div class="flex justify-start items-center sm:items-start sm:justify-start gap-1.5">
                            <SmModalEditComponent @click="edit(topupPackage)" />
                            <SmDeleteComponent @click="destroy(topupPackage.id)" />
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
</template>
<script>
import LoadingComponent from "../../components/LoadingComponent.vue";
import statusEnum from "../../../../enums/modules/statusEnum";
import appService from "../../../../services/appService";
import PaginationTextComponent from "../../components/pagination/PaginationTextComponent.vue";
import PaginationBox from "../../components/pagination/PaginationBox.vue";
import PaginationSMBox from "../../components/pagination/PaginationSMBox.vue";
import SmModalEditComponent from "../../components/buttons/SmModalEditComponent.vue";
import TableLimitComponent from "../../components/TableLimitComponent.vue";
import SmViewComponent from "../../components/buttons/SmViewComponent.vue";
import SmDeleteComponent from "../../components/buttons/SmDeleteComponent.vue";
import TopUpPackageCreateComponent from "./TopUpPackageCreateComponent.vue";
import alertService from "../../../../services/alertService";
export default {
    name:"TopUpPackageListComponent" ,
    components: {
        TopUpPackageCreateComponent,
        SmDeleteComponent, SmViewComponent, TableLimitComponent, SmModalEditComponent,
        PaginationSMBox, PaginationBox, PaginationTextComponent,
        LoadingComponent
    },
    data(){
        return {
            loading:{
                isActive:false
            },
            enums: {
                statusEnum: statusEnum,
                statusEnumArray: {
                    [statusEnum.ACTIVE]: this.$t("label.active"),
                    [statusEnum.INACTIVE]: this.$t("label.inactive"),
                },
            },
            props:{
                form: {
                    name: "",
                    price: 0,
                    points: 0,
                    bonus: 0,
                    status: statusEnum.ACTIVE,
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 10,
                    order_column: "id",
                    order_type: "desc",
                },
            }
        }
    },
    mounted() {
        this.list();
    },
    computed:{
        topupPackages:function(){
            return this.$store.getters["topupPackage/lists"];
        },
        pagination: function() {
            return this.$store.getters["topupPackage/pagination"];
        },
        paginationPage: function () {
            return this.$store.getters["topupPackage/page"]
        }
    },
    methods:{
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        textShortener: function (text, number = 30) {
            return appService.textShortener(text, number);
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store
                .dispatch("topupPackage/lists", this.props.search)
                .then((res) => {
                    this.loading.isActive = false;
                })
                .catch((err) => {
                    this.loading.isActive = false;
                });
        },
        edit: function(topupPackage){
            appService.modalShow();
            this.loading.isActive = true;
            this.$store.dispatch('topupPackage/edit',topupPackage.id)
            this.props.form = {
                name:topupPackage.name,
                price:topupPackage.price,
                points: topupPackage.points,
                bonus:topupPackage.bonus,
                status: topupPackage.status,
            }
            this.loading.isActive = false;
        },
        destroy: function (id){
            appService
                .destroyConfirmation()
                .then((res)=>{
                    try {
                        this.loading.isActive = true;
                        this.$store
                            .dispatch("topupPackage/destroy",{
                                id:id,
                                search: this.props.search
                            })
                            .then((res)=>{
                                this.loading.isActive = false,
                                    alertService.successFlip(
                                        null,
                                        this.$t("menu.topup_package")
                                    )
                            })
                            .catch((err) => {
                                this.loading.isActive = false;
                                alertService.error(err.response.data.message);
                            });
                    } catch (err) {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    }
                })
                .catch((err) => {
                    this.loading.isActive = false;
                });
        }
    }
}
</script>
