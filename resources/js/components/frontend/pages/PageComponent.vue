<template>
    <section class="pt-8 pb-16 min-h-[85vh] mt-[60px] md:mt-[74px]">
        <div class="container max-w-3xl">
            <div class="mb-6">
                <h2 class="text-[26px] leading-10 font-semibold capitalize mb-2">
                    {{ page.title }}
                </h2>
                <div v-if="page.image" class="w-full mb-6">
                    <img :src="page.image" alt="image">
                </div>
                <p v-html="page.description" class="text-xs text-heading">
                </p>
            </div>
            <TemplateManagerComponent :templateId="page.template_id" />

        </div>
    </section>
</template>
<script>
import TemplateManagerComponent from "../../table/components/TemplateManagerComponent.vue";

export default{
    name:"frontend.pages",
    components: { TemplateManagerComponent },
    computed:{
        page: function(){
            return this.$store.getters['frontendPage/show'];
        }
    },
    mounted() {
        this.pageSetup();
    },
    methods:{
        pageSetup: function (){
            if (Object.keys(this.$route.params).length > 0 && typeof this.$route.params.pageSlug === 'string') {
                this.$store.dispatch('frontendPage/show', this.$route.params.pageSlug).then(res => { }).catch((err) => { })
            }
        }
    },
    watch:{
        $route(){
            this.pageSetup()
        }
    }
}
</script>
