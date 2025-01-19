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
                    <h2 class="text-lg font-semibold text-gray-700">{{$t('message.order_ready')}}</h2>
                </div>
                <div class="py-4">
                    <img :src="setting.image_order_placed" alt="New Order" class="mx-auto w-32 mb-4">
                    <p class="text-gray-600 mb-2"><strong>{{$t('label.table_name')}}:</strong> {{ currentNotification.table }}</p>
                    <p class="text-gray-600 mb-2"><strong>{{$t('label.order_total')}}:</strong> {{ currencyFormat(currentNotification.price, setting.site_digit_after_decimal_point,
                        setting.site_default_currency_symbol, setting.site_currency_position) }}</p>
                </div>
                <div class="border-t pt-4">
                    <h3 class="text-gray-700 font-semibold mb-2">{{$t('label.order_details')}}:</h3>
                    <ul class="space-y-2">
                        <li v-for="item in currentNotification.items" :key="item.id" class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-700">{{ item.name }}</p>
                                <p class="text-sm text-gray-500">{{$t('label.amount')}}: {{ item.quantity }}</p>
                            </div>
                            <p class="text-gray-600"> {{ currencyFormat(item.price, setting.site_digit_after_decimal_point,
                                setting.site_default_currency_symbol, setting.site_currency_position) }} </p>
                        </li>
                    </ul>
                </div>
                <div class="flex justify-end mt-4">
                    <button type="button" @click="closeOrderModal" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg">
                        <span class="text-sm capitalize">{{ $t("button.close") }}</span>
                    </button>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
import LoadingComponent from "../components/LoadingComponent.vue";
import appService from "../../../services/appService";

export default {
    name: "OrderCompletedNotifications",
    components: { LoadingComponent },
    data() {
        return {
            loading: {
                isActive: false,
            },
            visible: false,
            notificationStack: [],
            currentNotification: null,
        };
    },
    computed: {
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
        authInfo: function () {
            return this.$store.getters.authInfo;
        },
        pendingOrder: function(){
            return this.$store.getters['tableOrder/pendingOrders'];
        }
    },
    mounted() {
        if (this.authInfo.role_id === 7){
            this.socketOrderCompleted();
        }
    },
    methods: {
        socketOrderCompleted() {
            window.Echo.channel('order-completed')
                .listen('.OrderCompleted', (event) => {
                    this.addNotification(event);
                });
        },
        addNotification(order) {
            this.notificationStack.push(order);
            if (!this.currentNotification) {
                this.showNextNotification();
            }
        },
        showNextNotification() {
            if (this.notificationStack.length > 0) {
                this.currentNotification = this.notificationStack.shift();
                console.log(this.currentNotification)
                this.visible = true;
            } else {
                this.currentNotification = null;
                this.visible = false;
            }
        },
        closeOrderModal() {
            this.visible = false;
            setTimeout(() => {
                this.showNextNotification();
            }, 300);
        },
        currencyFormat(amount, decimal, currency, position) {
            return appService.currencyFormat(amount, decimal, currency, position);
        },
    },
};
</script>
