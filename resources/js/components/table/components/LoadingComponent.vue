<template>
    <VueElementLoading :active="props.isActive" :is-full-screen="true">
        <i
            :class="['fa-regular fa-coffee-pot text-4xl text-[#d1992c]', { 'bounce-animation': props.isActive }]"
        ></i>
    </VueElementLoading>
</template>


<script>
import VueElementLoading from 'vue-element-loading';

export default {
    name: "LoadingComponent",
    components: {VueElementLoading},
    props: ['props']
}
</script>
