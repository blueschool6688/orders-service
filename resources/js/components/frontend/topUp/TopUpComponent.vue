<template>
    <LoadingComponent :props="loading" />
    <div class="container mt-[100px] md:mt-[100px] grid grid-cols-12 gap-6 min-h-[85vh]">
        <!-- Left Section -->
        <div class="col-span-12 lg:col-span-8">
            <!-- Input for Manual Points -->
<!--            <div class="mb-8 p-6 bg-white rounded-lg shadow">-->
<!--                <h3 class="text-lg font-bold mb-4">{{ $t("label.enter_points") }}</h3>-->
<!--                <div class="grid grid-cols-12 gap-4 items-center">-->
<!--                    <div class="col-span-12 md:col-span-6">-->
<!--                        <label for="points" class="db-field-title required">{{ $t("label.points") }}</label>-->
<!--                        <input-->
<!--                            :value="form.points"-->
<!--                            @input="handleInput"-->
<!--                            type="number"-->
<!--                            id="points"-->
<!--                            class="db-field-control w-full"-->
<!--                            min="0"-->
<!--                            placeholder="Enter points"-->
<!--                        />-->
<!--                    </div>-->
<!--                    <div class="col-span-12 md:col-span-6">-->
<!--                        <label for="payment_amount" class="db-field-title">{{ $t("label.payment_amount") }}</label>-->
<!--                        <input-->
<!--                            :value="totalPrice"-->
<!--                            type="text"-->
<!--                            id="payment_amount"-->
<!--                            class="db-field-control w-full"-->
<!--                            readonly-->
<!--                            placeholder="Payment Amount"-->
<!--                        />-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->

            <!-- Packages -->
            <div class="p-6 bg-white rounded-lg shadow">
                <h3 class="text-lg font-bold mb-4">{{ $t("label.select_package") }}</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="pack in packages"
                        :key="pack.id"
                        class="border p-4 rounded-lg shadow cursor-pointer hover:shadow-md transition"
                        :class="{ 'bg-primary text-white': selectedPackage?.id === pack.id }"
                        @click="selectPackage(pack)"
                    >
                        <h4 class="font-bold">{{ pack.name }}</h4>
                        <p class="text-sm">
                            {{ pack.points }} + {{ pack.bonus }} Bonus
                        </p>
                        <p class="text-lg font-bold">
                            {{ pack.price }} {{ currencySymbol }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Section -->
        <div class="col-span-12 lg:col-span-4">
            <div class="p-6 bg-white rounded-lg shadow sticky top-[100px]">
                <h3 class="text-lg font-bold mb-4">{{ $t("label.order_details") }}</h3>
                <div class="mb-6">
                    <p v-if="selectedPackage">
                        <strong>{{ $t("label.selected_package") }}:</strong>
                        {{ selectedPackage.name }} - {{ selectedPackage.points }} VND + {{ selectedPackage.bonus }} Bonus
                    </p>
                    <p v-else>
                        <strong>{{ $t("label.entered_points") }}:</strong>
                        {{ totalPoints }} VND
                    </p>
                </div>

                <!-- Payment Methods -->
                <div>
                    <h4 class="font-bold mb-3">{{ $t("label.payment_methods") }}</h4>
                    <div class="space-y-4">
                        <label class="flex items-center gap-3 p-4 border rounded-lg shadow cursor-pointer transition hover:shadow-md"
                               :class="{ 'border-primary': form.payment_method === 'bank_transfer' }">
                            <input
                                type="radio"
                                v-model="form.payment_method"
                                value="bank_transfer"
                                class="form-radio"
                            />
                            <span>{{ $t("label.bank_transfer") }}</span>
                        </label>
                    </div>
                </div>

                <!-- Payment Summary -->
                <div class="mt-6">
                    <p class="font-bold">{{ $t("label.payment_summary") }}:</p>
                    <p>{{ $t("label.total_points") }}: {{ totalPoints }} VND</p>
                    <p>{{ $t("label.bonus_points") }}: {{ form.bonus_points }} VND</p>
                    <p>{{ $t("label.total_payment") }}: {{ totalPrice }} {{ currencySymbol }}</p>
                </div>

                <!-- Confirm Button -->
                <div class="mt-6">
                    <button
                        class="w-full py-3 font-bold rounded-lg transition"
                        @click="confirmPayment"
                        :class="{
                            'bg-primary text-white': !isDisabled,
                            'bg-gray-400 text-gray-600 cursor-not-allowed opacity-50': isDisabled
                        }"
                        :disabled="isDisabled"
                    >
                        {{ $t("button.confirm_payment") }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../components/LoadingComponent.vue";
import { debounce } from "lodash";
import alertService from "../../../services/alertService";

export default {
    name: "TopUpComponent",
    components: { LoadingComponent },
    data() {
        return {
            loading: {
                isActive: false,
            },
            form: {
                points: 0,
                bonus_points: 0,
                payment_method: "bank_transfer",
            },
            totalPrice: 0,
            selectedPackage: null,
            packages: [
                { id: 1, name: "Nạp 50.000 Đ", points: 50000, bonus: 10000, price: 50000 },
                { id: 2, name: "Nạp 100.000 Đ", points: 100000, bonus: 20000, price: 100000 },
                { id: 3, name: "Nạp 250.000 Đ", points: 250000, bonus: 30000, price: 250000 },
            ],
            currencySymbol: "VND",
        };
    },
    computed: {
        totalPoints() {
            return this.form.points + this.form.bonus_points; // Calculate total points
        },
        isDisabled() {
            return this.form.points <= 0 || this.loading.isActive;
        },
    },
    methods: {
        debouncedUpdateExchange: debounce(function () {
            this.updateExchange();
        }, 300),
        updateExchange() {
            this.selectedPackage = null;
            this.form.bonus_points = 0; // Reset bonus points
            this.loading.isActive = true;
            const payload = {
                point: this.form.points,
            };

            this.$store
                .dispatch("point/exchange", payload)
                .then((res) => {
                    this.totalPrice = res.data.exchange;
                })
                .catch((err) => {
                    console.error(err);
                })
                .finally(() => {
                    this.loading.isActive = false;
                });
        },
        handleInput(event) {
            const value = event.target.value;
            this.form.points = value === "" || value < 0 ? 0 : parseInt(value);
            this.debouncedUpdateExchange();
        },
        selectPackage(packageData) {
            this.form.points = packageData.points; // Base points
            this.form.bonus_points = packageData.bonus; // Bonus points
            this.totalPrice = packageData.price;
            this.selectedPackage = packageData;
        },
        confirmPayment() {
            this.loading.isActive = true;
            const payload = {
                points: this.form.points,
                bonus_points: this.form.bonus_points,
                totalPrice: this.totalPrice,
                payment_method: this.form.payment_method,
                package_id: this.selectedPackage ? this.selectedPackage.id : null,
            };

            this.$store
                .dispatch("point/createRequest", payload)
                .then((res) => {
                    alertService.success(res.data?.message)
                    if (res.data?.data?.id) {
                        this.$router.push({
                            name: "client.topup.payment",
                            params: { id: res.data.data.id },
                        });
                    }
                })
                .catch((err) => {
                    if (err.response && err.response.data.errors) {
                        const errors = err.response.data.errors;
                        const firstError = Object.values(errors)[0][0];
                        alertService.error(firstError);
                    } else {
                        alertService.error("An unexpected error occurred.");
                        console.error(err);
                    }
                })
                .finally(() => {
                    this.loading.isActive = false;
                });
        },
    },
};
</script>
