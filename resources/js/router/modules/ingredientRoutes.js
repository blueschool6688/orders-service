import IngredientsComponent from "../../components/admin/ingredients/IngredientsComponent.vue";
import IngredientsListComponent from "../../components/admin/ingredients/IngredientsListComponent.vue";



export default [
    {
        path:'/admin/ingredients',
        component:IngredientsComponent,
        name:'admin.ingredients',
        redirect:{name:'admin.ingredients.list'},
        meta: {
            isFrontend: false,
            auth: true,
            // permissionUrl: 'items',
            breadcrumb: 'ingredients'
        },
        children:[
            {
                path:'',
                component: IngredientsListComponent,
                name: 'admin.ingredients.list',
                meta:{
                    isFrontend: false,
                    auth: true,
                    // permissionUrl: 'items',
                    breadcrumb: ''
                }
            }
        ]
    }
]
