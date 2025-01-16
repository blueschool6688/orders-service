<template>
    <section class="h-full mt-[60px] md:mt-[74px]">
        <div class="w-full">
            <LoadingComponent :props="loading" />
            <FullScreenBanner></FullScreenBanner>
        </div>
    </section>
</template>
<script>

import {defineComponent} from "vue";
import LoadingComponent from "../../table/components/LoadingComponent.vue";
import FullScreenBanner from "./BannerComponent.vue";

export default {
    name:"MainComponent",
    components: {
        FullScreenBanner,
        LoadingComponent
    },
    data(){
        return{
            loading:{
                isActive:false
            }
        }
    },
    mounted() {
        this.handleLoading(500)
    },
    methods:{
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
        }
    }
}
</script>
