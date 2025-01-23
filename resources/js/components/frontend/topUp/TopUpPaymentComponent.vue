<template>
    <LoadingComponent :props="loading" />
    <div class="container mt-[100px] md:mt-[100px] min-h-[85vh] flex justify-center items-center">
        <div class="p-6 bg-white rounded-lg shadow w-full h-full">
            <h3 class="text-lg font-bold mb-4">{{ $t("label.payment_details") }}</h3>
            <div v-if="paymentDetails">
                <p>
                    <strong>{{ $t("label.amount") }}:</strong>
                    {{ paymentDetails.amount }} {{ currencySymbol }}
                </p>
                <p>
                    <strong>{{ $t("label.points") }}:</strong>
                    {{ paymentDetails.points }} {{ currencySymbol }}
                </p>
                <p>
                    <strong>{{ $t("label.bonus_points") }}:</strong>
                    {{ paymentDetails.bonus_points }} {{ currencySymbol }}
                </p>
                <div v-if="paymentDetails.payment_method === 'bank_transfer' && paymentDetails.status !== 'success'" class="mt-6">
                    <img :src="qrCodeUrl" alt="QR Code" class="w-64 h-64 mx-auto" />
                    <p class="text-center mt-3 text-gray-600">{{ $t("label.scan_to_pay") }}</p>
                </div>
                <div v-if="paymentDetails.status === 'success'" class="mt-6 text-center">
                    <img :src="setting.image_payment_success" alt="Payment Success" class="w-24 h-24 mx-auto" />
                    <p class="text-lg font-bold text-green-600 mt-4">{{ $t("label.payment_success") }}</p>
                </div>
            </div>
            <div v-else>
                <p class="text-center text-gray-500">{{ $t("label.no_details_found") }}</p>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../components/LoadingComponent.vue";
import alertService from "../../../services/alertService";
import ENV from "../../../config/env";

export default {
    name: "TopUpPaymentComponent",
    components: { LoadingComponent },
    data() {
        return {
            loading: {
                isActive: false,
            },
            paymentDetails: null,
            currencySymbol: "VND",
            qrCodeUrl: null,
        };
    },
    computed: {
        requestId() {
            return this.$route.params.id || null;
        },
        clientProfile() {
            return this.$store.getters.clientInfo;
        },
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
    },
    mounted() {
        if (!this.requestId) {
            alertService.error(this.$t("label.invalid_request"));
            this.$router.push({ name: "client.topup" });
            return;
        }
        this.fetchPaymentDetails();
        this.socketUpdatePoint();
    },
    methods: {
        fetchPaymentDetails() {
            this.loading.isActive = true;
            this.$store
                .dispatch("point/details", { id: this.requestId })
                .then(() => {
                    this.paymentDetails = this.$store.getters["point/details"];
                    if (this.paymentDetails.payment_method === "bank_transfer") {
                        this.qrCodeUrl = `https://qr.sepay.vn/img?template=compact&acc=${ENV.BANK_NUMBER}&bank=${ENV.BANK_NAME}&amount=${this.paymentDetails.amount}&des=${ENV.BANK_CONTENT + ENV.BANK_PREFIX + this.requestId}`;
                    }
                })
                .catch((err) => {
                    alertService.error(this.$t("label.fetch_error"));
                    this.$router.push({ name: "client.topup" });
                })
                .finally(() => {
                    this.loading.isActive = false;
                });
        },
        socketUpdatePoint() {
            const profile = this.clientProfile;
            const userId = profile.id ?? "#";
            window.Echo.channel(`updateUserPoint-${userId}`).listen(".updateUserPoint", (event) => {
                this.$store.dispatch("updateClientInfo", event);
                    this.paymentDetails.status = "success";
            });
        },
    },
};
</script>
