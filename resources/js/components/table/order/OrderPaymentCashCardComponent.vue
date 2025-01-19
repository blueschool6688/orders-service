<template>
    <div>
        <div v-if="order.payment_status === paymentStatusEnum.PAID" class="text-center">
            <h3 class="text-green-600 font-medium">{{ $t("label.payment_success") }}</h3>
        </div>
        <div v-else class="text-center">
            <img :src="qrCodeUrl" alt="QR Code" class="mx-auto w-64 h-64">
            <p class="text-gray-600 mt-3">{{ $t("label.scan_to_pay") }}</p>
        </div>
    </div>
</template>
<script>
import paymentStatusEnum from "../../../enums/modules/paymentStatusEnum";
import ENV from "../../../config/env";

export default {
    name: "OrderPaymentCashCardComponent",
    props: {
        order: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            qrCodeUrl: null,
        };
    },
    computed: {
        paymentStatusEnum() {
            return paymentStatusEnum;
        },
    },
    watch: {
        "order.payment_status"(newStatus) {
            if (!this.order || !newStatus) {
                console.error("Invalid payment status or order data.");
                return;
            }
            if (newStatus !== this.paymentStatusEnum.PAID) {
                this.generateQRCode();
            }
        },
    },
    mounted() {
        if (this.order.payment_status !== this.paymentStatusEnum.PAID) {
            this.generateQRCode();
        }
    },
    methods: {
        generateQRCode() {
            if (this.order && this.order.total && this.order.id) {
                this.qrCodeUrl = `https://qr.sepay.vn/img?template=compact&acc=${ENV.BANK_NUMBER}&bank=${ENV.BANK_NAME}&amount=${this.order.total}&des=${ENV.BANK_CONTENT + ENV.CASHCARD_PREFIX + this.order.id}`;
            } else {
                console.error("Order data is incomplete for QR code generation.");
            }
        },
    },
};
</script>

