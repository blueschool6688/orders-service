<template>
    <LoadingComponent :props="loading" />
    <div class="col-12 xl:col-6">
        <div class="db-card">
            <div class="db-card-header">
                <div class="db-card-title">{{ $t("label.most_popular_items") }}</div>
                <div id="item-range" class="cursor-pointer flex items-center gap-3">
                    <Datepicker hideInputIcon autoApply :enableTimePicker="false" utc="false"
                                @update:modelValue="fetchPopularItems" v-model="date" range :preset-ranges="presetRanges">
                        <template #yearly="{ label, range, presetDateRange }">
                            <span @click="presetDateRange(range)">{{ label }}</span>
                        </template>
                    </Datepicker>
                    <i class="lab lab-calendar lab-font-size-24 lab-color-pink"></i>
                </div>
            </div>
            <div class="db-card-body">
                <div v-if="noData" class="text-center text-gray-500">{{ $t("label.no_data_available") }}</div>
                <div v-else id="pie-chart"></div>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../components/LoadingComponent";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import { ref, onMounted } from "vue";
import { endOfMonth, endOfYear, startOfMonth, startOfYear, subMonths, subYears } from "date-fns";
import {nextTick} from "vue";
export default {
    name: "MostPopularItemsComponent",
    components: { LoadingComponent, Datepicker },
    data() {
        return {
            loading: { isActive: false },
            date: null,
            first_date: null,
            last_date: null,
            chart: null,
            noData: false,
            presetRanges: [
                { label: "Today", range: [new Date(), new Date()] },
                { label: "This month", range: [startOfMonth(new Date()), endOfMonth(new Date())] },
                { label: "Last month", range: [startOfMonth(subMonths(new Date(), 1)), endOfMonth(subMonths(new Date(), 1))] },
                { label: "This year", range: [startOfYear(new Date()), endOfYear(new Date())] },
                { label: "Last year", range: [startOfYear(subYears(new Date(), 1)), endOfYear(subYears(new Date(), 1))] },
            ],
        };
    },
    mounted() {
        const date = new Date();
        this.date = [startOfYear(date), endOfYear(date)];
        this.fetchPopularItems();
    },
    methods: {
        fetchPopularItems(e) {
            let dateFilter = {first_date: "", last_date: ""};

            if (e) {
                this.first_date = e[0];
                this.last_date = e[1];
                dateFilter.first_date = e[0];
                dateFilter.last_date = e[1];
            }

            this.loading.isActive = true;

            this.$store.dispatch("dashboard/mostPopularItems", dateFilter).then((res) => {
                const items = res.data.data;

                if (!items || items.length === 0) {
                    this.noData = true;
                    this.loading.isActive = false;

                    if (this.chart) {
                        this.chart.destroy();
                        this.chart = null;
                    }
                    return;
                }

                this.noData = false;
                const labels = items.map((item) => item.name);
                const series = items.map((item) => item.total_sold);

                const options = {
                    series: series,
                    chart: {
                        type: "pie",
                        height: 300,
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800,
                            animateGradually: {
                                enabled: true,
                                delay: 150
                            },
                            dynamicAnimation: {
                                enabled: true,
                                speed: 350
                            }
                        }
                    },
                    labels: labels,
                    legend: {
                        position: "bottom",
                    },
                    tooltip: {
                        style: {
                            fontSize: "14px",
                            fontFamily: "inherit",
                        },
                    },
                    colors: [
                        "#567DFF", "#FF4560", "#00E396", "#FEB019", "#775DD0",
                        "#3F51B5", "#F44336", "#9C27B0", "#4CAF50", "#FFC107"
                    ],
                };


                if (this.chart) {
                    this.chart.destroy();
                    this.chart = null;
                }


                nextTick(() => {
                    const chartElement = document.querySelector("#pie-chart");
                    if (chartElement) {
                        this.chart = new ApexCharts(chartElement, options);
                        this.chart.render();
                    }
                });

                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
                this.noData = true;

                if (this.chart) {
                    this.chart.destroy();
                    this.chart = null;
                }
            });
        },
    }
};
</script>
