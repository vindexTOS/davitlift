<template>
  <div v-if="data.companies">
    <v-card class="pa-2 mb-2">
      <v-card-title>
        <h3>{{ $t('Companies') }}</h3>
        <h5>
          {{ $t('Total amounts deposited in companies') }}:{{
            (data['totalEarning'] / 100).toFixed(2)
          }}{{ $t('Lari') }}
        </h5>
      </v-card-title>

      <v-row class="justify-space-between">
        <v-col
          v-if="data['totalEarning']"
          style="min-height: 100%;"
          cols="12"
          md="6"
        >
          <v-card style="height: 100%;" class="overflow-auto pa-2">
            <span>{{ $t('Amounts deposited in months') }}</span>

            <apexchart
              width="400"
              type="bar"
              :options="chartOptions"
              :series="series"
            ></apexchart>
          </v-card>
        </v-col>
        <v-col cols="12" md="6">
          <v-card style="height: 100%;" class="overflow-auto pa-2">
            <span>
              {{ $t('Condition of elevators') }} : {{ seriesC[0] + seriesC[1] }}
            </span>
            <apexchart
              width="400"
              height="350"
              type="donut"
              :options="chartOptionsC"
              :series="seriesC"
            ></apexchart>
          </v-card>
        </v-col>
      </v-row>
      <v-row class="justify-space-between">
        <v-col style="min-height: 100%;" cols="12" md="6"></v-col>
        <v-col v-if="data['totalServiceFee'] > 1" cols="12" md="6">
          <v-card style="height: 100%;" class="overflow-auto pa-2">
            <span>
              {{ $t('ჯამური სერვისის გადასახადი') }} :
              {{ data['totalServiceFee'].toFixed(2) }}{{ $t('Lari') }}
            </span>
            <apexchart
              width="400"
              height="350"
              type="donut"
              :options="chartOptionsB"
              :series="seriesB"
            ></apexchart>
          </v-card>
        </v-col>
      </v-row>
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
      v-model:items-per-page="itemsPerPage"
      v-model:expanded="expanded"
      :headers="headers"
      :search="search"
      :items="data['companies']"
      :color="'purple'"
      class="elevation-1"
      item-value="sk_code"
    >
      <template v-slot:top>
        <v-toolbar>
          <v-toolbar-title>{{ $t('Companies') }}</v-toolbar-title>
          <v-divider class="mx-4"></v-divider>
          <v-spacer></v-spacer>
          <v-dialog v-model="dialog" max-width="500px">
            <template v-slot:activator="{ props }">
              <v-btn color="primary" dark class="mb-2" v-bind="props">
                {{ $t('Company') }}
              </v-btn>
            </template>
            <v-card>
              <v-card-title>
                <span class="text-h5">{{ formTitle }}</span>
              </v-card-title>

              <v-card-text>
                <v-container>
                  <v-row>
                    <v-col cols="12">
                      <v-text-field
                        v-model="editedItem.company_name"
                        :label="$t('Name')"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12">
                      <v-text-field
                        v-model="editedItem.admin_email"
                        :label="$t('Admin email')"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12">
                      <v-text-field
                        v-model="editedItem.sk_code"
                        :label="$t('Tax code')"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12">
                      <v-text-field
                        v-model.number="editedItem.cashback"
                        :label="$t('Service free %')"
                        :rules="numberRules"
                        type="number"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12">
                      <v-textarea
                        v-model="editedItem.comment"
                        :label="$t('Comment')"
                      ></v-textarea>
                    </v-col>
                  </v-row>
                </v-container>
              </v-card-text>

              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue-darken-1" variant="text" @click="close">
                  {{ $t('Close') }}
                </v-btn>
                <v-btn color="blue-darken-1" variant="text" @click="save">
                  {{ $t('Save') }}
                </v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
          <v-dialog v-model="dialogDelete" max-width="500px">
            <v-card>
              <v-card-title class="text-h5">
                Are you sure you want to delete this item?
              </v-card-title>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn
                  color="blue-darken-1"
                  variant="text"
                  @click="closeDelete"
                >
                  Cancel
                </v-btn>
                <v-btn
                  color="blue-darken-1"
                  variant="text"
                  @click="deleteItemConfirm"
                >
                  OK
                </v-btn>
                <v-spacer></v-spacer>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </v-toolbar>
      </template>
      <template v-slot:item.company_name="{ item }">
        <RouterLink :to="`/company/${item.raw.id}`">
          {{ item.raw.company_name }}
        </RouterLink>
      </template>
      <template v-slot:item.cashback="{ item }">
        {{ item.raw.cashback }}%
      </template>
      <template v-slot:item.isBlocked="{ item }">
        <v-chip
          v-if="item.raw.isBlocked"
          class=""
          color="red"
          text-color="white"
        >
          დაბლოკილია
        </v-chip>
        <v-chip v-else class="" color="green" text-color="white">
          {{ $t('Active') }}
        </v-chip>
      </template>
      <template v-slot:item.admin_email="{ item }">
        {{ item.raw.admin.email }}
      </template>
      <template v-slot:item.actions="{ item }">
        <v-menu>
          <template v-slot:activator="{ props }">
            <v-icon
              size="small"
              icon="mdi-dots-vertical"
              v-bind="props"
            ></v-icon>
          </template>
          <v-card max-width="fit-content" class="pa-0 ma-0">
            <v-btn style="width: 100%;" @click="editItem(item.raw)" small>
              <v-icon size="small">
                mdi-pencil
              </v-icon>
              {{ $t('Edit') }}
            </v-btn>
            <v-btn style="width: 100%;" @click="deleteItem(item.raw)" small>
              <v-icon size="small">
                mdi-delete
              </v-icon>
              {{ $t('Delete') }}
            </v-btn>
            <v-btn style="width: 100%;" small @click="getServiceFee(item.raw)">
              <v-icon size="small">
                mdi-cash
              </v-icon>
              {{ $t('Withdrawal service fee') }}
            </v-btn>

            <v-btn
              v-if="!item.raw.isBlocked"
              style="width: 100%;"
              @click="blockCompany(item.raw)"
              small
            >
              <v-icon size="small">
                mdi-no
              </v-icon>
              {{ $t('Block') }}
            </v-btn>
            <v-btn
              v-if="item.raw.isBlocked"
              style="width: 100%;"
              @click="unblockCompany(item.raw)"
              small
            >
              <v-icon size="small">
                mdi-block
              </v-icon>
              {{ $t('Unblock') }}
            </v-btn>
          </v-card>
        </v-menu>
      </template>
      <template v-slot:expanded-row="{ columns, item }">
        <tr>
          <td :colspan="columns.length">
            More info about {{ item.raw.comment }}
          </td>
        </tr>
      </template>
    </v-data-table>
  </div>
  <v-dialog v-model="serviceFeeDialog" max-width="500px">
    <v-card>
      <v-card-title>
        <span class="text-h5">{{ $t('Withdrawal service fee') }}</span>
      </v-card-title>

      <v-card-text>
        <v-col cols="12">
          <p>
            {{ $t('The maximum amount to be enrolled') }}:
            {{
              (serviceFee.max - serviceFee.company.withdrewedTotalFee).toFixed(
                2,
              )
            }}
            {{ $t('Lari') }}
          </p>

          <v-text-field
            v-model.number="serviceFee.amount"
            :label="$t('Amount deposited')"
            class="text-capitalize"
            required
            type="number"
          ></v-text-field>
        </v-col>
        <v-col cols="12">
          {{ $t('Date of amount deposited') }}
          <v-text-field
            v-model="serviceFee.transaction_date"
            type="date"
          ></v-text-field>
        </v-col>
      </v-card-text>

      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn
          color="blue-darken-1"
          variant="text"
          @click="serviceFeeDialog = false"
        >
          {{ $t('Close') }}
        </v-btn>
        <v-btn
          color="blue-darken-1"
          variant="text"
          @click="saveWithdrawalServiceFee"
        >
          {{ $t('Save') }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
<script>
import { VDataTable } from 'vuetify/labs/components'
import Router from '@/router'
import router from '@/router'
import VueApexCharts from 'vue3-apexcharts'
import Swal from 'sweetalert2'

export default {
  components: { VDataTable, apexchart: VueApexCharts },
  data: () => ({
    expanded: [],
    dialog: false,
    dialogDelete: false,
    itemsPerPage: 5,

    numberRules: [
      (v) => !!v || v == 0 || 'Field is required',
      (v) => !isNaN(parseFloat(v)) || 'Must be a number',
      (v) => (v >= 0 && v <= 100) || 'Number must be between 0 and 100',
    ],

    seriesB: [0.01, 0.01],

    seriesC: [0.01, 0.01],
    series: [
      {
        name: 'გამომუშავება',
        data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
      },
    ],
    search: '',
    data: {},
    loading: true,
    desserts: [],
    editedIndex: -1,
    editedItem: {
      company_name: '',
      admin_email: '',
      comment: 0,
      role: 'company',
    },
    serviceFeeDialog: false,
    defaultItem: {
      name: '',
      admin_email: '',
      comment: 0,
    },
    serviceFee: {
      amount: 0,
      transaction_date: null,
      type: 4,
      max: 0,
    },
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? this.$t('New company') : 'Edit company'
    },
    headers() {
      return [
        {
          title: this.$t('Name'),
          align: 'center',
          sortable: false,
          key: 'company_name',
        },
        { title: this.$t('Admin email'), key: 'admin_email', align: 'center' },
        { title: this.$t('ID'), align: 'center', key: 'sk_code' },
        { title: this.$t('Service fee'), align: 'center', key: 'cashback' },
        { title: this.$t('Status'), align: 'center', key: 'isBlocked' },
        { title: '', align: 'center', key: 'actions' },
      ]
    },
    chartOptions() {
      return {
        chart: {
          id: 'vuechart-exampl2e',
        },
        xaxis: {
          categories: [
            this.$t('იან'),
            this.$t('თებ'),
            this.$t('მარტი'),
            this.$t('აპრ'),
            this.$t('მაისი'),
            this.$t('ივნ'),
            this.$t('ივლ'),
            this.$t('აგვ'),
            this.$t('სექ'),
            this.$t('ოქტ'),
            this.$t('ნოემბ'),
            this.$t('დეკ'),
          ],
        },
      }
    },
    chartOptionsC() {
      return {
        labels: [this.$t('Inactive'), this.$t('Active')],
      }
    },
    chartOptionsB() {
      return {
        labels: [this.$t('ჩასარიცხი'), this.$t('ჩარიცხული')],
      }
    },
  },
  created() {
    this.loadItems()
  },
  watch: {
    dialog(val) {
      val || this.close()
    },
    dialogDelete(val) {
      val || this.closeDelete()
    },
  },

  methods: {
    loadItems() {
      axios.get(`/api/companies`).then(({ data }) => {
        console.log(data)
        this.data = data
        this.data.payedServiceFeeByMonths.forEach((x) => {
          this.series[0].data[Number(x.month) - 1] = x.total_amount
        })
        this.seriesC = [
          this.data.deviceStats.inactive,
          this.data.deviceStats.active,
        ]
        this.seriesB = [
          Number(
            (
              data['totalServiceFee'].toFixed(2) - data['payedServiceFee']
            ).toFixed(2),
          ),
          Number(data['payedServiceFee']),
        ]
      })
    },
    getServiceFee(item) {
      this.serviceFeeDialog = true
      this.serviceFee.max = item.payedCompanyFee
      this.serviceFee.company = item
    },
    saveWithdrawalServiceFee() {
      axios
        .post('/api/pay/cashback', {
          company_id: this.serviceFee.company.id,
          manager_id: null,
          type: 4,
          amount: this.serviceFee.amount,
          transaction_date: this.serviceFee.transaction_date,
        })
        .then(() => {
          this.$swal.fire({
            icon: 'success',
            position: 'center',
            allowOutsideClick: false,
          })
          this.loadItems()
          this.serviceFeeDialog = false
        })
    },

    editItem(item) {
      this.editedIndex = this.desserts.indexOf(item)
      this.editedItem = Object.assign(
        {},
        { admin_email: item.admin.email, ...item },
      )
      this.dialog = true
    },

    deleteItem(item) {
      this.editedItem = item
      this.$swal
        .fire({
          title: this.$t('ნამდვილად გსურთ კომპანიის წაშლა'),
          showCancelButton: false,
          showDenyButton: true,
          confirmButtonText: this.$t('Yes'),
          denyButtonText: this.$t('Close'),
        })
        .then((result) => {
          /* Read more about isConfirmed, isDenied below */
          if (result.isConfirmed) {
            axios.delete('/api/companies/' + this.editedItem.id).then(() => {
              this.$swal.fire({
                icon: 'success',
                position: 'center',
                allowOutsideClick: false,
              })
            })
          } else if (result.isDenied) {
          }
        })
    },
    async blockCompany(item) {
      this.$swal
        .fire({
          title: 'ნამდვილად გსურთ კომპანიის დაბლოკვა?',
          showCancelButton: false,
          showDenyButton: true,
          confirmButtonText: this.$t('Yes'),
          denyButtonText: this.$t('Close'),
        })
        .then((result) => {
          /* Read more about isConfirmed, isDenied below */
          if (result.isConfirmed) {
            axios.get('/api/companies/block/' + item.id).then((x) => {
              this.$swal.fire({
                icon: 'success',
                position: 'center',
                allowOutsideClick: false,
              })
              this.loadItems()
            })
          } else if (result.isDenied) {
          }
        })
    },
    async unblockCompany(item) {
      this.$swal
        .fire({
          title: 'ნამდვილად გსურთ კომპანიის განბლოკვა?',
          showCancelButton: false,
          showDenyButton: true,
          confirmButtonText: this.$t('Yes'),
          denyButtonText: this.$t('Close'),
        })
        .then((result) => {
          /* Read more about isConfirmed, isDenied below */
          if (result.isConfirmed) {
            axios.get('/api/companies/unblock/' + item.id).then((x) => {
              this.$swal.fire({
                icon: 'success',
                position: 'center',
                allowOutsideClick: false,
              })
              this.loadItems()
            })
          } else if (result.isDenied) {
          }
        })
    },

    async deleteItemConfirm() {
      await axios.delete('/api/companies/' + this.editedItem.id)
      this.$swal.fire({
        icon: 'success',
        position: 'center',
        allowOutsideClick: false,
      })
      this.loadItems({ page: 1, itemsPerPage: 3, sortBy: 4 })
      this.closeDelete()
    },

    close() {
      this.dialog = false
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      })
    },

    closeDelete() {
      this.dialogDelete = false
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      })
    },

    async save() {
      console.log(this.editedItem)
      if (this.editedItem.id) {
        console.log('put')
        console.log(this.editedItem)
        const res = await axios.put(
          '/api/companies/' + this.editedItem.id,
          this.editedItem,
        )
        console.log(res)
      } else {
        console.log('post')
        const res = await axios.post('/api/companies', this.editedItem)
        console.log(res)
      }
      this.$swal.fire({
        icon: 'success',
        position: 'center',
        allowOutsideClick: false,
      })
      this.loadItems({ page: 1, itemsPerPage: 3, sortBy: 4 })

      this.close()
    },
  },
}
</script>
<style scoped>
.blocked-row {
  background-color: #ffcccc !important; /* Light red background for blocked rows */
}
</style>
