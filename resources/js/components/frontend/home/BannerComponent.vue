<template>
    <section
        class="w-full h-90vh min-h-85vh flex items-center justify-center"
        :style="themeMainBannerStyle">
        <div class="text-center">
            <h3 class="text-white text-4xl sm:text-4xl font-bold mb-6">{{$t('slogan.where_time_slows_down_flavors_linger_on')}}</h3>
            <p class="text-white text-lg sm:text-xl mb-8">
                {{$t('slogan.scan_to_discover')}}
            </p>
            <button
                @click="redirectQrCode"
                type="button"
                class="text-white border-2 border-white rounded-lg px-4 py-2 text-md hover:bg-white hover:text-[#d1992c] transition-all duration-300">
                <i class="fa-solid fa-qrcode"></i> {{ $t('menu.scan') }}
            </button>
        </div>
    </section>
</template>

<script>
import statusEnum from "../../../enums/modules/statusEnum";

export default {
    name: "FullScreenBanner",
    computed: {
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
        themeMainBannerStyle:function (){
            const banner = this.setting?.theme_main_banner;
            if (banner) {
                return {
                    backgroundImage: `url(${banner})`,
                    backgroundSize: "cover",
                    backgroundPosition: "center",
                    backgroundRepeat: "no-repeat",
                };
            }
            return {
                display: "none",
            };
        }
    },
    mounted() {
        this.$store.dispatch("frontendPage/lists", {
            paginate: 0,
            order_column: "id",
            order_type: "asc",
            status: statusEnum.ACTIVE
        });
    },
    methods:{
        redirectQrCode() {
            this.$router.push({ name: 'frontend.qr' });
        }
    }
};
</script>

<style scoped>
/* Optional: Responsive design tweaks */
@media (max-width: 640px) {
    h3 {
        font-size: 2rem;
    }
    p {
        font-size: 1rem;
    }
}
</style>
