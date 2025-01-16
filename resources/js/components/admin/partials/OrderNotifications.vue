<template>
    <LoadingComponent :props="loading" />
    <transition
        name="fade"
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 transform scale-95"
        enter-to-class="opacity-100 transform scale-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 transform scale-100"
        leave-to-class="opacity-0 transform scale-95"
    >
        <div v-if="visible" id="order-modal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="relative bg-white rounded-lg shadow-lg w-full max-w-md md:max-w-lg p-6">
                <button class="absolute top-4 right-4 text-gray-400 hover:text-gray-600" @click="closeOrderModal">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <div class="flex justify-center items-center border-b pb-4">
                    <h2 class="text-lg font-semibold text-gray-700">{{$t('message.new_order_placed')}}</h2>
                </div>
                <div class="py-4">
                    <img :src="setting.image_order_placed" alt="New Order" class="mx-auto w-32 mb-4">
                    <p class="text-gray-600 mb-2"><strong>{{$t('label.table_name')}}:</strong> {{ table }}</p>
                    <p class="text-gray-600 mb-2"><strong>{{$t('label.order_total')}}:</strong> {{ currencyFormat(total, setting.site_digit_after_decimal_point,
                        setting.site_default_currency_symbol, setting.site_currency_position) }}</p>
                </div>
                <div class="border-t pt-4">
                    <h3 class="text-gray-700 font-semibold mb-2">{{$t('label.order_details')}}:</h3>
                    <ul class="space-y-2">
                        <li v-for="item in orderItems" :key="item.id" class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-700">{{ item.name }}</p>
                                <p class="text-sm text-gray-500">{{$t('label.amount')}}: {{ item.quantity }}</p>
                            </div>
                            <p class="text-gray-600"> {{ currencyFormat(item.price, setting.site_digit_after_decimal_point,
                                setting.site_default_currency_symbol, setting.site_currency_position) }} </p>
                        </li>
                    </ul>
                </div>
                <div class="flex justify-end space-x-2 mt-4">
                    <button type="button" @click="orderReasonModal" data-modal="#orderReasonModal" class="flex items-center justify-center gap-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg">
                        <i class="lab lab-close"></i>
                        <span class="text-sm capitalize">{{ $t("button.reject") }}</span>
                    </button>
                    <button type="button" class="flex items-center justify-center gap-2 px-4 py-2 bg-primary text-white rounded-lg" @click="changeStatus(enums.orderStatusEnum.ACCEPT)">
                        <i class="lab lab-save"></i>
                        <span class="text-sm capitalize text-white">{{ $t("button.accept") }}</span>
                    </button>
                </div>
            </div>
        </div>
    </transition>
    <div id="orderReasonModal" class="modal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title">{{ $t("label.reason") }}</h3>
                <button class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500"
                        @click.prevent="resetOrderReasonModal"></button>
            </div>
            <div class="modal-body">
                <form @submit.prevent="rejectOrder">
                    <div class="form-row">
                        <div class="form-col-12">
                            <label for="name" class="db-field-title">
                                {{ $t("label.reason") }}
                            </label>
                            <input v-model="form.reason" v-bind:class="error ? 'invalid' : ''" type="text" id="name"
                                   class="db-field-control" />
                            <small class="db-field-alert" v-if="error">
                                {{ error }}
                            </small>
                        </div>
                        <div class="form-col-12">
                            <div class="modal-btns">
                                <button type="button" class="modal-btn-outline modal-close" @click.prevent="resetModal">
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
import appService from "../../../services/appService";
import isAdvanceOrderEnum from "../../../enums/modules/isAdvanceOrderEnum";
import paymentStatusEnum from "../../../enums/modules/paymentStatusEnum";
import orderStatusEnum from "../../../enums/modules/orderStatusEnum";
import paymentTypeEnum from "../../../enums/modules/paymentTypeEnum";
import orderTypeEnum from "../../../enums/modules/orderTypeEnum";
import LoadingComponent from "../components/LoadingComponent.vue";
import alertService from "../../../services/alertService";

export default {
    name: "OrderNotifications",
    components: {LoadingComponent},
    data() {
        return {
            loading: {
                isActive: false,
            },
            visible: false,
            order_id:null,
            table: '',
            total: 0,
            orderItems: [],
            enums:{
                isAdvanceOrderEnum: isAdvanceOrderEnum,
                paymentStatusEnum: paymentStatusEnum,
                paymentStatusEnumArray: {
                    [paymentStatusEnum.PAID]: this.$t("label.paid"),
                    [paymentStatusEnum.UNPAID]: this.$t("label.unpaid"),
                },
                paymentStatusObject: [
                    {
                        name: this.$t("label.paid"),
                        value: paymentStatusEnum.PAID,
                    },
                    {
                        name: this.$t("label.unpaid"),
                        value: paymentStatusEnum.UNPAID,
                    },
                ],
                orderStatusEnum: orderStatusEnum,
                orderStatusEnumArray: {
                    [orderStatusEnum.PENDING]: this.$t("label.pending"),
                    [orderStatusEnum.ACCEPT]: this.$t("label.accept"),
                    [orderStatusEnum.PROCESSING]: this.$t("label.processing"),
                    [orderStatusEnum.DELIVERED]: this.$t("label.delivered"),
                    [orderStatusEnum.REJECTED]: this.$t("label.rejected"),
                },
                orderStatusObject: [
                    {
                        name: this.$t("label.accept"),
                        value: orderStatusEnum.ACCEPT,
                    },
                    {
                        name: this.$t("label.processing"),
                        value: orderStatusEnum.PROCESSING,
                    },
                    {
                        name: this.$t("label.delivered"),
                        value: orderStatusEnum.DELIVERED,
                    },
                ],
                paymentTypeEnum: paymentTypeEnum,
                paymentTypeEnumArray: {
                    [paymentTypeEnum.CASH_ON_DELIVERY]: this.$t("label.cash_card"),
                },
                orderTypeEnum: orderTypeEnum,
                orderTypeEnumArray: {
                    [orderTypeEnum.DINING_TABLE]: this.$t("label.dining_table"),
                },
            },
            form:{
                reason: ""
            },
            error:""
        };
    },
    mounted() {
        if (!this.visible){
            this.listenForOrders();
        }
    },
    computed:{
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
    },
    methods: {
        orderReasonModal:function(){
            appService.modalShow("#orderReasonModal");
        },
        resetOrderReasonModal: function () {
            appService.modalHide("#orderReasonModal");
            this.form.reason = "";
            this.error = "";
        },
        listenForOrders() {
            window.Echo.channel('order-placed')
                .listen('.OrderPlaced', (event) => {
                    console.log('New Order Received:', event);
                    this.order_id = event.order_id;
                    this.table = event.table;
                    this.total = event.price;
                    this.orderItems = event.items;
                    this.visible = true;
                });
        },
        closeOrderModal() {
            this.visible = false
        },
        handleConfirm() {
            alert('Đơn hàng đã được xác nhận!');
            this.closeModal();
        },
        currencyFormat(amount, decimal, currency, position) {
            return appService.currencyFormat(amount, decimal, currency, position);
        },
        redirectToOrder: function (order_id) {
            if (!order_id) {
                console.error("Order ID is missing for redirection.");
                alertService.error(this.$t("message.order_id_missing"));
                return;
            }
            this.$router.push({
                name: "admin.table.order.show",
                params: { id: order_id },
            });
        },
        changeStatus:function(status){
            appService.acceptOrder()
                .then((res)=>{
                try{
                    this.loading.isActive = true;
                    this.$store
                        .dispatch("tableOrder/changeStatus", {
                            id: this.order_id,
                            status: status,
                        })
                        .then((res)=>{
                            this.visible = false;
                            this.loading.isActive = false;
                            alertService.successFlip(1, this.$t("label.status"));
                            // redirect tới đơn hàng
                            this.redirectToOrder(this.order_id)
                        })
                        .catch((err) => {
                            this.loading.isActive = false;
                            alertService.error(err.response.data.message);
                        });
                }catch (err){
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                }
            })
            .catch((err) => {
                this.loading.isActive = false;
            });
        },
        rejectOrder: function () {
            try {
                this.loading.isActive = true;
                this.$store
                    .dispatch("tableOrder/changeStatus", {
                        id: this.order_id,
                        status: orderStatusEnum.REJECTED,
                        reason: this.form.reason,
                    })
                    .then((res) => {
                        this.loading.isActive = false;
                        appService.modalHide();
                        this.form = {
                            reason: "",
                        };
                        this.error = "";
                        this.visible = false;
                        alertService.successFlip(1, this.$t("label.status"));
                        // redirect tới đơn hàng
                        this.redirectToOrder(this.order_id)
                    })
                    .catch((err) => {
                        this.loading.isActive = false;
                        this.error = err.response.data.message;
                    });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            }
        },
    },
};
</script>
