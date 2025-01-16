<template>
    <section class="h-full mt-[60px] md:mt-[74px]">
        <div class="w-full min-h-85vh flex flex-col items-center justify-center">
<!--            <div id="qr-reader" class="w-full md:w-[calc(100%-50px)] max-w-[350px] aspect-square"></div>-->
            <div v-show="!isStopped" id="qr-reader" class="lg:w-full w-[calc(100%-50px)] max-w-[350px] aspect-square lg:h-full h-[calc(100%-50px)] max-h-[350px]"></div>
            <button
                v-if="isStopped"
                @click="retryScan"
                class="mt-4 px-6 py-2 bg-[#d1992c] text-white rounded-lg hover:bg-[#a97320] transition-all duration-300"
            >
                Thử lại
            </button>
            <p class="mt-4 text-center text-lg text-gray-700">
                {{ $t('slogan.scan_to_redirect_menu') }}
            </p>
        </div>
    </section>
</template>
<script>
import LoadingComponent from "../../frontend/components/LoadingComponent.vue";
import {Html5Qrcode} from "html5-qrcode";
import alertService from "../../../services/alertService";
export default {
    name: "QrScanComponent",

    components:{
        LoadingComponent
    },
    data(){
        return{
            isCameraSupported: false,
            isStopped: false,
            qrResult: null,
            loading:{
                isActive:false
            }
        }
    },
    mounted() {
        this.isCameraSupported = "mediaDevices" in navigator && "getUserMedia" in navigator.mediaDevices;
        this.handleLoading(500)
        if (this.isCameraSupported) {
            this.initQrReader();
        }
    },
    methods:{
        async initQrReader() {
            this.isStopped = false;
            const isMobile = /Mobi|Android/i.test(navigator.userAgent);
            const facingMode = isMobile ? "environment" : "user";
            this.html5QrCode = new Html5Qrcode("qr-reader");
            try {
                const qrbox = this.calculateQrboxWidth();
                await this.html5QrCode.start(
                    { facingMode },
                    { fps: 30, qrbox ,aspectRatio:1},
                    this.onScanSuccess,
                );
            } catch (err) {
                console.error("Camera initialization failed:", err);
            }
        },
        calculateQrboxWidth() {
            const container = document.getElementById("qr-reader");
            if (!container) {
                return { width: 350, height: 350 };
            }

            const width = Math.max(container.offsetWidth, 350);
            const height = Math.max(container.offsetHeight, 350);
            return { width, height };
        },
        onScanSuccess(decodedText) {
            this.qrResult = decodedText;
            if (this.isValidUrl(decodedText)) {
                const successMessage = this.$t('message.table_scan_success')
                alertService.success(successMessage,'top-center');
                this.stopQrReader()
                console.log(decodedText)
                window.location.href = decodedText;
            } else {
                const message = this.$t('message.table_scan_false')
                alertService.warning(message,'top-center');
                this.stopQrReader()
                this.isStopped = true
            }
        },
        isValidUrl(string) {
            try {
                const url = new URL(string);
                return ["http:", "https:"].includes(url.protocol);
            } catch (_) {
                return false;
            }
        },
        stopQrReader() {
            if (this.html5QrCode) {
                this.html5QrCode.stop().then(() => {
                    this.html5QrCode.clear();
                });
            }
        },
        retryScan() {
            this.isStopped = false;
            this.$nextTick(() => {
                this.initQrReader();
            });
        },
        showLoading(){
            this.loading.isActive = true;
        },
        hideLoading(){
            this.loading.isActive = false;
        },
        handleLoading(duration = 500){
            this.showLoading()
            setTimeout(() =>{
                this.hideLoading()
            },duration)
        },
    },
    beforeUnmount() {
        this.stopQrReader();
    },
}
</script>
<style scoped>

#qr-reader {
    border: 2px solid #d1992c;
    border-radius: 8px;
}
</style>
