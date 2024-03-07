<template>
    <div>
        <v-card>
            <v-card-text>
                <v-text-field
                    v-model="search"
                    :label="$t('Search')"
                    single-line
                    hide-details
                ></v-text-field>
            </v-card-text>
        </v-card>
        <v-data-table
            :headers="headers"
            :items-length="totalItems"
            :items="serverItems"
            :search="search"
            class="elevation-1"
            item-value="name"
        >
            <template v-slot:top>
                <v-toolbar >
                    <v-toolbar-title class="text-capitalize">ტრანზაქციების ცხრილი </v-toolbar-title>
                    <v-spacer></v-spacer>
                </v-toolbar>
            </template>
            <template v-slot:item.status="{item}">
                {{$t(item.status.cashback)}}
            </template>
            <template v-slot:item.action="{item}">
                <v-btn @click="checkStatus(item)">check</v-btn>
            </template>
        </v-data-table>

    </div>
</template>
<script>
import {VDataTable} from "vuetify/labs/components";

export default {
    components : {VDataTable},
    props: ['serverItems'],
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
                    title: 'transaction_id',
                    align: 'start',
                    sortable: false,
                    key: 'transaction_id',
                },
                {title: 'status', key: 'status', align: 'start'},
                {title: '', key: 'action', align: 'start'},
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
        },
        checkStatus(item) {
            axios.get('/api/bank/transaction/detail/' + item.raw.transaction_id)
                .then(({data}) => {
                    this.$emit('reload')
                })
        }
    },
}
</script>
