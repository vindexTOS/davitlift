<template>
  <div>
    <v-data-table
      :headers="headers"
      :items-length="totalItems"
      :items="serverItems"
      :search="search"
      class="elevation-1"
      item-value="name"
    >
      <template v-slot:top>
        <v-toolbar>
          <v-toolbar-title class="text-capitalize">
            {{ isCompanyPage ? $t('Transaction table') : $t('Cashback table') }}
          </v-toolbar-title>
          <v-spacer v-if="!$vuetify.display.xs"></v-spacer>
          <v-menu>
            <template v-slot:activator="{ props }">
              <v-icon
                class="ma-2"
                v-if="$store.state.auth.user.lvl >= 2"
                size="small"
                icon="mdi-dots-vertical"
                v-bind="props"
              ></v-icon>
            </template>
            <v-card width="250" class="pa-0 ma-0">
              <v-dialog v-model="dialogExisted" max-width="500px">
                <template v-slot:activator="{ props }">
                  <v-btn
                    v-if="$store.state.auth.user.lvl >= 3"
                    dark
                    style="width: 100%;"
                    class="mb-2"
                    v-bind="props"
                  >
                    {{
                      isCompanyPage
                        ? $t('Declare service fee')
                        : $t('Declare cashback')
                    }}
                  </v-btn>
                </template>
                <v-card>
                  <v-card-title>
                    <span class="text-h5">
                      {{
                        isCompanyPage
                          ? $t('Declare service fee')
                          : $t('Declare cashback')
                      }}
                    </span>
                  </v-card-title>

                  <v-card-text>
                    <v-col cols="12">
                      <p>
                        {{ $t('The maximum amount to be enrolled') }}:
                        {{ maxCashback }} {{ $t('Lari') }}
                      </p>

                      <v-text-field
                        v-model.number="cashback.amount"
                        :label="$t('Amount deposited')"
                        class="text-capitalize"
                        required
                        type="number"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12">
                      {{ $t('Date of amount deposited') }}
                      <v-text-field
                        v-model="cashback.transaction_date"
                        type="date"
                      ></v-text-field>
                    </v-col>
                    <p style="color: red;">{{ errorMessege }}</p>
                  </v-card-text>

                  <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn
                      color="blue-darken-1"
                      variant="text"
                      @click="dialogExisted = false"
                    >
                      {{ $t('Close') }}
                    </v-btn>
                    <v-btn color="blue-darken-1" variant="text" @click="save">
                      {{ $t('Save') }}
                    </v-btn>
                  </v-card-actions>
                </v-card>
              </v-dialog>

              <v-dialog v-model="dialogExistedManager" max-width="500px">
                <template v-slot:activator="{ props }">
                  <v-btn
                    v-if="$store.state.auth.user.lvl >= 2"
                    dark
                    style="width: 100%;"
                    class="mb-2"
                    v-bind="props"
                  >
                    ქეშბექის გამოტანა
                  </v-btn>
                </template>
                <v-card>
                  <v-card-title>
                    <span class="text-h5">ქეშბექის გამოტანა</span>
                  </v-card-title>

                  <v-card-text>
                    <v-col cols="12">
                      <p>
                        {{ $t('The maximum amount to be enrolled') }}:
                        {{ availableCashback }} {{ $t('Lari') }}
                      </p>

                      <v-text-field
                        v-model.number="cashbackManager.amount"
                        :label="$t('Amount deposited')"
                        class="text-capitalize"
                        required
                        type="number"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12">
                      {{ $t('Date of amount deposited') }}
                      <v-text-field
                        v-model="cashbackManager.transaction_date"
                        type="date"
                      ></v-text-field>
                    </v-col>
                  </v-card-text>

                  <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn
                      color="blue-darken-1"
                      variant="text"
                      @click="dialogExistedManager = false"
                    >
                      {{ $t('Close') }}
                    </v-btn>
                    <v-btn
                      color="blue-darken-1"
                      variant="text"
                      @click="saveExisted"
                    >
                      {{ $t('Save') }}
                    </v-btn>
                  </v-card-actions>
                </v-card>
              </v-dialog>
            </v-card>
          </v-menu>
        </v-toolbar>
      </template>
      <template v-slot:item.type="{ item }">
        <span>{{ getText(item.raw.type) }}</span>
      </template>
      <template v-slot:item.transaction_date="{ item }">
        <span>{{ getDateWithoutHours(item.raw.transaction_date) }}</span>
      </template>
      <template v-slot:item.name="{ item }">
        <RouterLink to="/">{{ item.raw.name }}</RouterLink>
      </template>
      <template v-slot:item.balance="{ item }">
        {{ item.raw.balance / 100 }}{{ $t('Lari') }}
      </template>
      <template v-slot:item.actions="{ item }">
        <v-icon
          v-if="$store.state.auth.user.lvl >= 3"
          size="small"
          color="red"
          @click="deleteItem(item.raw.id)"
        >
          mdi-delete
        </v-icon>
      </template>
    </v-data-table>
  </div>
</template>
<script>
import { VDataTable } from 'vuetify/labs/components'

export default {
  components: { VDataTable },
  props: [
    'serverItems',
    'managerId',
    'maxCashback',
    'isCompanyPage',
    'maxServiceFee',
    'availableCashback',
  ],
  data: () => ({
    select: null,
    dialogExisted: false,
    dialogExistedManager: false,
    items: [],
    expanded: [],
    dialog: false,
    itemsPerPage: 1,
    cashback: {
      transaction_date: null,
      amount: 0,
      type: 1,
    },
    search: '',
    loading: true,
    totalItems: 0,
    desserts: [],
    editedIndex: -1,
    existUser: '',
    cashbackManager: {},
    action: {},
    errorMessege: '',
  }),

  computed: {
    headers() {
      return [
        {
          title: this.$t('Amount'),
          align: 'start',
          sortable: false,
          key: 'amount',
        },
        {
          title: this.$t('Date of transfer'),
          key: 'transaction_date',
          align: 'start',
        },
        { title: this.$t('Type'), key: 'type', align: 'start' },
        { title: '', key: 'actions' },
      ]
    },
  },

  methods: {
    deleteItem(id) {
      this.$swal
        .fire({
          title: this.$t('Are you sure you want to delete the transaction?'),
          showCancelButton: false,
          showDenyButton: true,
          confirmButtonText: this.$t('Yes'),
          denyButtonText: this.$t('Close'),
        })
        .then((result) => {
          /* Read more about isConfirmed, isDenied below */
          if (result.isConfirmed) {
            axios.delete('/api/cashback/' + id).then(() => {
              this.$swal.fire({
                icon: 'success',
                position: 'center',
                allowOutsideClick: false,
              })
              this.$emit('getCashback')
            })
          } else if (result.isDenied) {
          }
        })
    },
    getText(type) {
      return type == 1
        ? this.$t('Deposit cashback')
        : type == 2
        ? this.$t('Service fee')
        : this.$t('Withdrawal cashback')
    },
    saveExisted() {
      axios
        .post('/api/pay/cashback', {
          ...this.cashbackManager,
          company_id: this.isCompanyPage
            ? this.$route.params.id
            : this.$route.params.companyId,
          manager_id: this.isCompanyPage ? null : this.$route.params.id,
          type: 3,
        })
        .then(() => {
          this.$swal.fire({
            icon: 'success',
            position: 'center',
            allowOutsideClick: false,
          })
          this.$emit('getCashback')
          this.dialogExisted = false
        })
    },
    save() {
      if (this.maxCashback >= this.cashback.amount) {
        axios
          .post('/api/pay/cashback', {
            ...this.cashback,
            company_id: this.isCompanyPage
              ? this.$route.params.id
              : this.$route.params.companyId,
            manager_id: this.isCompanyPage ? null : this.$route.params.id,
            type: this.isCompanyPage ? 2 : 1,
          })
          .then(() => {
            this.$swal.fire({
              icon: 'success',
              position: 'center',
              allowOutsideClick: false,
            })
            this.$emit('getCashback')
            this.dialogExisted = false
          })
      } else {
        this.errorMessege = 'არა საკმარისი თანხა'
        setTimeout(() => {
          this.errorMessege = ''
        }, 5000)
      }
    },
    getDateWithoutHours(dateString) {
      // Convert the string into a Date object
      const dateObject = new Date(dateString)

      // Extract the year, month, and day
      const year = dateObject.getFullYear()
      const month = dateObject.getMonth() + 1 // getMonth() returns 0-11
      const day = dateObject.getDate()

      // Format the month and day to ensure they are always two digits
      const formattedMonth = month.toString().padStart(2, '0')
      const formattedDay = day.toString().padStart(2, '0')

      // Combine the formatted parts into the final date string
      return `${formattedDay}/${formattedMonth}/${year}`
    },
  },
}
</script>
