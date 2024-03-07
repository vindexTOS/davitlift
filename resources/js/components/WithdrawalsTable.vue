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
                    <v-toolbar-title class="text-capitalize">გატანილი თანხა </v-toolbar-title>
                    <v-spacer></v-spacer>
                </v-toolbar>
            </template>
            <template v-slot:item.withdrawal_date="{item}">
                {{formatedDate(item.raw.withdrawal_date)}}
            </template>
            <template v-slot:item.withdrawn_amount="{item}">
                {{item.raw.withdrawn_amount/100}}{{$t('Lari')}}
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

        headers() {
            return   [
                {
                    title: 'თარიღი',
                    align: 'start',
                    sortable: false,
                    key: 'withdrawal_date',
                },
                {title: 'თვე', key: 'withdrawn_amount', align: 'start'},
            ];
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
        deleteItem() {},
        formatedDate(date){
            return new Date(date).toLocaleDateString('ka-GE');
        }
    },
}
</script>
