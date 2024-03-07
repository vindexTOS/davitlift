<template>
    <div>
        <v-data-table
            v-model:expanded="expanded"
            :headers="headers"
            :items-length="totalItems"
            :items="serverItems"
            :search="search"
            class="elevation-1"
            item-value="name"
        >
            <template v-slot:top>
                <v-toolbar >
                    <v-toolbar-title class="text-capitalize">{{$t('Earning')}} </v-toolbar-title>
                </v-toolbar>
            </template>
            <template v-slot:item.earnings="{item}">
                {{item.raw.earnings/100}}₾
            </template>


        </v-data-table>

    </div>
</template>
<script>
import {VDataTable} from "vuetify/labs/components";

export default {
    components : {VDataTable},
    props: ['serverItems','isFixed'],
    data: () => ({
        select: null,
        dialogExisted: false,
        items: [],
        expanded: [],
        dialog: false,
        dialogDelete: false,
        itemsPerPage: 5,

        search: '',
        loading: true,
        totalItems: 0,
        desserts: [],
        editedIndex: -1,
        editedItem: {
            name: '',
            phone: 0,
            email: 0,
            password: 0,
            companies: 0,
            comment: ''
        },
        defaultItem: {
            name: '',
            calories: 0,
            fat: 0,
            carbs: 0,
            protein: 0,
        },
        action: {}
    }),

    computed: {
        formTitle () {
            return this.editedIndex === -1 ? 'New user' : 'Edit user'
        },
        headers() {
            let header =  [
                {
                    title: this.$t('Year'),
                    align: 'start',
                    sortable: false,
                    key: 'year',
                },
                {title: this.$t('თვე'), key: 'month', align: 'start'},
                {title: this.$t('Amount'), key: 'earnings', align: 'start'},
            ];
            if(this.isFixed) {
                header.push({title: this.$t('has paid'), key: 'paid', align: 'start'})

            }
            header.push({title: '', key: 'action'})
            return header;
        }
    },

    watch: {
        dialog (val) {
            val || this.close()
        },
        dialogDelete (val) {
            val || this.closeDelete()
        },
    },

    methods: {
        deleteItem() {}
    },
}
</script>
