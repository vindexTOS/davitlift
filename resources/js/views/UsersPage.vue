<template>
    <div>
        <v-card>
            <v-card-text class="d-flex  align-items " :class="{
              'flex-column' : $vuetify.display.xs
            }">
                <v-text-field
                    v-model="userId"
                    :label="$t('მომხმარებლის აიდი')"
                    single-line
                    hide-details
                ></v-text-field>
                <RouterLink  class="mt-3" :to="'/user/'+userId"><v-btn>გადასვლა პროფილზე</v-btn></RouterLink>
            </v-card-text>
        </v-card>
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
            v-model:expanded="expanded"
            :headers="headers"
            :items-length="totalItems"
            :items="serverItems"
            :loading="loading"
            :search="search"
            class="elevation-1"
            item-value="name"
        >
            <template v-slot:top>
                <v-toolbar>
                    <v-toolbar-title class="text-capitalize">{{ $t("users") }}</v-toolbar-title>
                    <v-spacer></v-spacer>
                </v-toolbar>
            </template>
            <template v-slot:item.actions="{ item }">
                <v-menu>
                    <template v-slot:activator="{ props }">
                        <v-icon size="small" icon="mdi-dots-vertical" v-bind="props"></v-icon>
                    </template>
                    <v-card width="200" class="pa-0 ma-0">
                        <v-btn style="width: 100%" @click="() => {dialog=true;this.passwordItem.id=item.raw.id}">
                          {{$t('Change password')}}
                        </v-btn>
                    </v-card>
                </v-menu>

            </template>
            <template v-slot:item.name="{item}">
              <RouterLink v-if="$store.state.auth.user.lvl >= 2" :to="`/user/${item.raw.id}`">{{ item.raw.name }}</RouterLink>
              <span v-else>{{ item.raw.name }}</span>
            </template>
            <template v-slot:item.balance="{item}">
                {{item.raw.balance/100}}{{$t('Lari')}}
            </template>
            <template v-slot:no-data>
                <v-btn
                    color="primary"
                    @click="initialize"
                >
                    Reset
                </v-btn>
            </template>
        </v-data-table>
        <v-dialog v-model="dialog" max-width="600">
            <v-card>
                <v-card-title class="headline">{{ $t('Change password') }}</v-card-title>

                <v-card-text class="pl-3">
                    <v-text-field
                        v-model="passwordItem.password"
                        :label="$t('Password')"
                        required
                    ></v-text-field>
                </v-card-text>

                <v-card-actions>
                    <v-btn color="primary" @click="dialog = false">{{ $t('close') }}</v-btn>
                    <v-btn color="green darken-1" @click="changePassword">{{ $t('save') }}</v-btn>
                    <v-spacer></v-spacer>

                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>
<script>
import {VDataTable} from "vuetify/labs/components";


export default {
    components: {VDataTable},
    data: () => ({
        select: null,
        dialogExisted: false,
        items: [
            {state: 'Florida', abbr: 'FL'},
            {state: 'Georgia', abbr: 'GA'},
            {state: 'Nebraska', abbr: 'NE'},
            {state: 'California', abbr: 'CA'},
            {state: 'New York', abbr: 'NY'},
        ],
        expanded: [],
        dialog: false,
        dialogDelete: false,
        itemsPerPage: 5,

        search: '',
        serverItems: [],
        loading: true,
        userId: null,
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
        passwordItem : {
            id:null,
            password:'12345678',
        }
    }),
    created() {
        this.loadItems()
    },
  computed: {
    headers() {
     return [
        {
          title: this.$t('Name'),
          align: 'center',
          sortable: false,
          key: 'name',
        },
        {title: this.$t('Email'), key: 'email', align: 'center'},
        {title: this.$t('Phone'), key: 'phone', align: 'center'},
        {title: this.$t('Balance'), key: 'balance', align: 'center'},
        {title: '', key: 'actions'}
      ]
    }
  },


    watch: {
        dialogDelete(val) {
            val || this.closeDelete()
        },
    },

    methods: {
        loadItems() {
            this.loading = true
            axios.get('/api/users').then(({data}) => {
                this.serverItems = data
                this.loading = false
            })
        },
        editItem(item) {
            this.editedIndex = this.desserts.indexOf(item)
            this.editedItem = Object.assign({}, item)
            this.dialog = true
        },

        deleteItem(item) {
            this.editedIndex = this.desserts.indexOf(item)
            this.editedItem = Object.assign({}, item)
            this.dialogDelete = true
        },

        deleteItemConfirm() {
            this.desserts.splice(this.editedIndex, 1)
            this.closeDelete()
        },

        close() {
            this.dialog = false
            this.$nextTick(() => {
                this.editedItem = Object.assign({}, this.defaultItem)
                this.editedIndex = -1
            })
        },
        changePassword() {
            axios.get(`/api/change/user/password/admin/${this.passwordItem.id}/${this.passwordItem.password}`).then(()=> {
                this.$swal.fire({
                    icon: 'success',
                    position:'center',
                    allowOutsideClick: false,
                })
            })
        },
        closeDelete() {
            this.dialogDelete = false
            this.$nextTick(() => {
                this.editedItem = Object.assign({}, this.defaultItem)
                this.editedIndex = -1
            })
        },

        save() {
            if (this.editedIndex > -1) {
                Object.assign(this.desserts[this.editedIndex], this.editedItem)
            } else {
                this.desserts.push(this.editedItem)
            }
            this.close()
        },
    },
}
</script>
<style scoped>

</style>
