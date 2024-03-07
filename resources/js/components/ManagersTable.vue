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
                <v-toolbar>
                    <v-toolbar-title class="text-capitalize">{{ $t("Chairman") }}</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-text-field
                        v-if="!$vuetify.display.xs"
                        v-model="search"
                        :label="$t('Search')"
                        single-line
                        hide-details
                    ></v-text-field>
                </v-toolbar>
                <v-text-field
                    v-if="$vuetify.display.xs"
                    v-model="search"
                    :label="$t('Search')"
                    single-line
                    hide-details
                ></v-text-field>
            </template>
            <template v-slot:item.name="{item}">
                <RouterLink :to="`/manager/${item.raw.id}/${companyId}`">
                    {{ item.raw.name }}
                </RouterLink>
            </template>
            <template v-slot:item.isBlocked="{item}">
                <v-chip
                    v-if="item.raw.isBlocked"
                    class=""
                    color="red"
                    text-color="white"
                >
                    {{ $t('Blocked') }}
                </v-chip>
                <v-chip
                    v-else
                    class=""
                    color="green"
                    text-color="white"
                >
                    {{ $t('Active') }}
                </v-chip>
            </template>
            <template v-slot:item.cashback="{item}">
                {{ item.raw.cashback }}%
            </template>
            <template v-slot:item.actions="{ item }">
                <v-menu>
                    <template v-slot:activator="{ props }">
                        <v-icon
                            v-if="$store.state.auth.user.lvl >= 3"
                            size="small" icon="mdi-dots-vertical" v-bind="props"></v-icon>
                    </template>
                    <v-card width="250" class="pa-0 ma-0">
                        <v-btn

                            style="width: 100%"
                            @click="() => {managerDialog = true; managerItem.oldId = item.raw.id; }"
                            small
                        >
                            <v-icon
                                size="small"
                            >
                                mdi-pencil
                            </v-icon>
                            {{ $t('Change of chairman') }}
                        </v-btn>


                        <v-btn
                            style="width: 100%"
                            @click="() => {cashbackDialog = true; changeCashbackItem.id = item.raw.id; changeCashbackItem.cashback = item.raw.cashback}"
                            small
                        >
                            <v-icon
                                size="small"
                            >
                                mdi-cash
                            </v-icon>
                            {{ $t('Cashback percentage') }}
                        </v-btn>
                        <v-btn
                            v-if="!item.raw.isBlocked"
                            style="width: 100%"
                            @click="blockCompany(item.raw)"

                            small
                        >
                            <v-icon
                                size="small"
                            >
                                mdi-no
                            </v-icon>
                            {{ $t('Block') }}
                        </v-btn>
                        <v-btn
                            v-if="item.raw.isBlocked"
                            style="width: 100%"
                            @click="unblockCompany(item.raw)"

                            small
                        >
                            <v-icon
                                size="small"
                            >
                                mdi-block
                            </v-icon>
                            {{ $t('UnBlock') }}
                        </v-btn>
                        <v-btn
                            v-if="!item.raw.hide_statistic"
                            style="width: 100%"
                            @click="hideStatistic(item.raw)"

                            small
                        >
                            <v-icon
                                size="small"
                            >
                                mdi-no
                            </v-icon>
                            {{ $t('დამალე თანხები') }}
                        </v-btn>
                        <v-btn
                            v-if="item.raw.hide_statistic"
                            style="width: 100%"
                            @click="unhideStatistic(item.raw)"

                            small
                        >
                            <v-icon
                                size="small"
                            >
                                mdi-block
                            </v-icon>
                            {{ $t('აჩვენე თანხები') }}
                        </v-btn>
                    </v-card>

                </v-menu>
            </template>


        </v-data-table>
        <v-dialog v-model="cashbackDialog" max-width="600">
            <v-card>
                <v-card-title class="headline">{{ $t('Cashback') }}</v-card-title>

                <v-card-text class="pl-3">
                    <v-text-field
                        v-model.number="changeCashbackItem.cashback"
                        :label="$t('Cashback')"
                        required
                        type="number"
                    ></v-text-field>
                </v-card-text>

                <v-card-actions>
                    <v-btn color="primary" @click="cashbackDialog = false">{{ $t('close') }}</v-btn>
                    <v-btn color="green darken-1" @click="changeCashback">{{ $t('save') }}</v-btn>
                    <v-spacer></v-spacer>

                </v-card-actions>
            </v-card>
        </v-dialog>
        <v-dialog v-model="managerDialog" max-width="600">
            <v-card>
                <v-card-title class="headline">{{ $t('Change of chairman') }}</v-card-title>

                <v-card-text class="pl-3">
                    <v-text-field
                        v-model="managerItem.newMail"
                        :label="$t('Email')"
                        required
                    ></v-text-field>
                </v-card-text>

                <v-card-actions>
                    <v-btn color="primary" @click="managerDialog = false">{{ $t('Close') }}</v-btn>
                    <v-btn color="green darken-1" @click="changeManager">{{ $t('Save') }}</v-btn>
                    <v-spacer></v-spacer>

                </v-card-actions>
            </v-card>
        </v-dialog>

    </div>
</template>
<script>
import {VDataTable} from "vuetify/labs/components";
import router from "@/router";
import Swal from "sweetalert2";

export default {
    components: {VDataTable},
    props: ['serverItems', 'isFixed', 'deviceId', 'companyId'],
    data: () => ({
        cashbackDialog: false,
        managerDialog: false,
        select: null,
        dialogExisted: false,
        items: [],
        expanded: [],
        dialog: false,
        dialogDelete: false,
        itemsPerPage: 1,

        search: '',
        loading: true,
        totalItems: 0,
        desserts: [],
        editedIndex: -1,
        existUser: '',
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
        action: {},
        changeCashbackItem: {
            id: null,
            cashback: 0,
        },
        managerItem: {
            oldId: null,
            newMail: null,
        },
    }),

    computed: {
        formTitle() {
            return this.editedIndex === -1 ? 'New user' : 'Edit user'
        },
        headers() {
            return [
                {
                    title: 'სახელი',
                    align: 'Center',
                    sortable: false,
                    key: 'name',
                },
                {title: this.$t('Email'), key: 'email', align: 'Center'},
                {title: this.$t('Phone'), key: 'phone', align: 'Center'},
                {title: this.$t('Cashback'), key: 'cashback', align: 'Center'},
                {title: this.$t('Status'), key: 'isBlocked', align: 'Center'},
                {title: '', key: 'actions', align: 'Center'},

            ];
        }
    },


    methods: {
        deleteItem(id) {
            this.$swal.fire({
                title: 'ნამდვილად გსურთ მომხმარებლის ამოშლა ?',
                showCancelButton: false,
                showDenyButton: true,
                confirmButtonText: this.$t('Yes'),
                denyButtonText: this.$t('Close'),
            }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
                    axios.delete(`/api/userRemoveDevice/${id}/${this.deviceId}`).then(() => {
                        this.$swal.fire({
                            icon: 'success',
                            position: 'center',
                            allowOutsideClick: false,
                        })
                        this.$emit('loadDevice');
                        this.dialogExisted = false;
                    })
                } else if (result.isDenied) {
                }
            })
        },

        save() {
            axios.get(`/api/userToDevice/${this.existUser}/${this.deviceId}`).then(() => {
                this.$swal.fire({
                    icon: 'success',
                    position: 'center',
                    allowOutsideClick: false,
                })
                this.$emit('loadDevice');
                this.dialogExisted = false;
            })
        },
        changeCashback() {
            if (this.changeCashbackItem.cashback < 0 || this.changeCashbackItem.cashback > 100) {
                this.$swal.fire({
                    icon: 'error',
                    position: 'center',
                    allowOutsideClick: false,
                    text: 'შეიყვანეთ 0 დან 100ამდე',
                })
                return;
            }
            axios.get(`/api/cashback/${this.changeCashbackItem.id}/${this.changeCashbackItem.cashback}`,).then(() => {
                this.$emit('reload');
                this.cashbackDialog = false;
                this.$swal.fire({
                    icon: 'success',
                    position: 'center',
                    allowOutsideClick: false,
                })
            })
        },
        async blockCompany(item) {
            this.$swal.fire({
                title: 'ნამდვილად გსურთ თავიმჯდომარის დაბლოკვა?',
                showCancelButton: false,
                showDenyButton: true,
                confirmButtonText: this.$t('Yes'),
                denyButtonText: this.$t('Close'),
            }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
                    axios.get('/api/companies/block/manager/' + item.id).then(x => {
                        this.$swal.fire({
                            icon: 'success',
                            position: 'center',
                            allowOutsideClick: false,
                        })
                        this.$emit('reload');

                    })
                } else if (result.isDenied) {
                }
            })

        },
        async hideStatistic(item) {
            this.$swal.fire({
                title: 'ნამდვილად გსურთ სტატისტიკის  დამალვა მომხმარებლისთვის?',
                showCancelButton: false,
                showDenyButton: true,
                confirmButtonText: this.$t('Yes'),
                denyButtonText: this.$t('Close'),
            }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
                    axios.get('/api/companies/hideStatistic/manager/' + item.id).then(x => {
                        this.$swal.fire({
                            icon: 'success',
                            position: 'center',
                            allowOutsideClick: false,
                        })
                        this.$emit('reload');

                    })
                } else if (result.isDenied) {
                }
            })
        },
        async unhideStatistic(item) {
            this.$swal.fire({
                title: 'ნამდვილად გსურთ სტატისტიკის  ჩვენება მომხმარებლისთვის?',
                showCancelButton: false,
                showDenyButton: true,
                confirmButtonText: this.$t('Yes'),
                denyButtonText: this.$t('Close'),
            }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
                    axios.get('/api/companies/unhideStatistic/manager/' + item.id).then(x => {
                        this.$swal.fire({
                            icon: 'success',
                            position: 'center',
                            allowOutsideClick: false,
                        })
                        this.$emit('reload');

                    })
                } else if (result.isDenied) {
                }
            })
        },
        async unblockCompany(item) {
            this.$swal.fire({
                title: 'ნამდვილად გსურთ თავიმჯდომარის განბლოკვა?',
                showCancelButton: false,
                showDenyButton: true,
                confirmButtonText: this.$t('Yes'),
                denyButtonText: this.$t('Close'),
            }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
                    axios.get('/api/companies/unblock/manager/' + item.id).then(x => {
                        this.$swal.fire({
                            icon: 'success',
                            position: 'center',
                            allowOutsideClick: false,
                        })
                        this.$emit('reload');

                    })
                } else if (result.isDenied) {
                }
            })
        },
        changeManager() {
            axios.get(`/api/changeManager/${this.companyId}/${this.managerItem.oldId}/${this.managerItem.newMail}`,).then(() => {
                this.$emit('reload');
                this.managerDialog = false;
                this.$swal.fire({
                    icon: 'success',
                    position: 'center',
                    allowOutsideClick: false,
                })
            }).catch(() => {

            })
        }
    },
}
</script>
