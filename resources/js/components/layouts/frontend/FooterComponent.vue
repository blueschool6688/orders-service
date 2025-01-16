<template>
    <LoadingComponent :props="loading" />

    <footer class="bg-primary">
        <div class="container">
            <div class="flex flex-col items-start justify-between gap-5 py-8">
                <!-- Logo -->
                <div class="w-auto md:w-auto text-center md:text-left bg-white">
                    <img
                        :src="setting.theme_footer_logo"
                        alt="Logo"
                        class="h-12 mx-auto md:mx-0 max-w-[48px] max-h-[48px]"
                    />
                </div>
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-5 py-8 md:w-full">
                    <p class="text-sm text-white text-center md:text-left">
                        {{ setting.site_copyright }}
                    </p>
                    <nav v-if="pages.length > 0" class="flex flex-col items-start md:items-center md:flex-row md:gap-6 justify-start md:justify-around gap-2">
                        <router-link
                            v-for="page in pages"
                            :key="page.slug"
                            class="text-sm capitalize text-white"
                            :to="{ name: 'frontend.page', params: { pageSlug: page.slug } }"
                        >
                            {{ page.title }}
                        </router-link>
                    </nav>
                </div>
            </div>
        </div>
    </footer>
</template>


<script>
import statusEnum from "../../../enums/modules/statusEnum";
import menuSectionEnum from "../../../enums/modules/menuSectionEnum";
import LoadingComponent from "../../frontend/components/LoadingComponent";

export default {
    name: "FooterComponent",
    components: { LoadingComponent },
    data() {
        return {
            loading: {
                isActive: false,
            }
        }
    },
    computed: {
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
        pages: function () {
            return this.$store.getters['frontendPage/lists'];
        }
    },
    mounted() {
        this.loading.isActive = true;
        this.$store.dispatch("frontendPage/lists", {
            paginate: 0,
            order_column: "id",
            order_type: "asc",
            menu_section_id: menuSectionEnum.FOOTER,
            status: statusEnum.ACTIVE
        }).then(res => {
            this.loading.isActive = false;
        }).catch((err) => {
            this.loading.isActive = false;
        });
    }
}
</script>
