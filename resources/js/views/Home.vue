<template>
  <v-card class="mb-3 d-md-flex justify-md-space-between">
    <v-col style="min-height: 100%;" cols="12" md="6">
      <v-card style="min-height: 100%;">
        <v-card-title class="d-flex justify-space-between">
          <span class="headline">{{ user.name }}</span>
          <v-menu>
            <template v-slot:activator="{ props }">
              <v-icon
                size="small"
                icon="mdi-dots-vertical"
                v-bind="props"
              ></v-icon>
            </template>
            <v-card width="200" class="pa-0 ma-0">
              <v-btn style="width: 100%;" @click="showModal = true" small>
                <v-icon size="small">mdi-pencil</v-icon>
                {{ $t('Edit') }}
              </v-btn>
              <v-btn @click="showBalance = true" style="width: 100%;" small>
                <v-icon size="small">mdi-cash</v-icon>
                {{ $t('Add Balance') }}
              </v-btn>
              <v-btn @click="dialog = true" style="width: 100%;">
                {{ $t('Change password') }}
              </v-btn>
            </v-card>
          </v-menu>
        </v-card-title>

        <v-card-text class="px-5">
          <div class="d-flex justify-space-between align-center">
            <v-list-item-title>
              {{ $t('Email') }}:
              <div class="text-grey">{{ user.email }}</div>
            </v-list-item-title>
          </div>

          <div class="d-sm-flex justify-space-between align-center">
            <v-list-item-title>
              {{ $t('Phone') }}:
              <div class="text-grey">{{ user.phone }}</div>
            </v-list-item-title>
          </div>
          <div class="d-sm-flex justify-space-between align-center">
            <v-list-item-title>
              {{ $t('Balance') }}:
              <div class="text-grey">
                {{ userBalance / 100 }} {{ $t('Lari') }}
              </div>
            </v-list-item-title>
          </div>
        </v-card-text>
      </v-card>
    </v-col>
    <v-col cols="12" md="6">
      <v-card min-width="48%" class="pb-16 mt-md-0 mt-10">
        <v-card-title
          v-if="Manager.id"
          class="mt-5 d-sm-flex justify-space-between"
        >
          <div>{{ $t('Elevator cards') }}</div>
          <!-- კარტების დამატება -->
          <v-btn v-if="isAdmin" @click="showElevator = true">
            {{ $t('Add card') }}
          </v-btn>
        </v-card-title>
        <div v-if="Manager.id">
          <v-card v-for="item in desserts" class="ma-4 pa-5" :key="item.name">
            <div class="justify-space-between">
              <div>
                <div class="d-flex justify-space-between">
                  <span>{{ $t('Name') }}: {{ item.name }}</span>
                  <v-menu>
                    <template v-slot:activator="{ props }">
                      <v-icon
                        size="small"
                        icon="mdi-dots-vertical"
                        v-bind="props"
                      ></v-icon>
                    </template>
                    <v-card width="200" class="pa-0 ma-0">
                      <v-btn
                        @click="generateCode(item.card_number, item.device_id)"
                        style="width: 100%;"
                        small
                      >
                        {{ $t('Guest code') }}
                      </v-btn>
                      <v-btn
                        style="width: 100%;"
                        @click="cardEditFun(item)"
                        small
                      >
                        <v-icon size="small">
                          mdi-pencil
                        </v-icon>
                        {{ $t('Edit') }}
                      </v-btn>
                      <!--                      <v-btn-->
                      <!--                          style="width: 100%"-->
                      <!--                          @click="deleteCard(item.id)"-->

                      <!--                          small-->
                      <!--                      >-->
                      <!--                        <v-icon-->
                      <!--                            size="small"-->
                      <!--                        >-->
                      <!--                          mdi-delete-->
                      <!--                        </v-icon>-->
                      <!--                        {{ $t('Delete') }}-->
                      <!--                      </v-btn>-->
                    </v-card>
                  </v-menu>
                </div>
                <div>
                  {{ $t('Card Number') }}:
                  {{ item.card_number }}
                </div>
              </div>
              <!--                            <div>-->
              <!--                                <v-menu>-->
              <!--                                    <template v-slot:activator="{ props }">-->
              <!--                                        <v-icon size="small" icon="mdi-dots-vertical" v-bind="props"></v-icon>-->
              <!--                                    </template>-->
              <!--                                    <v-card width="200" class="pa-0 ma-0">-->
              <!--                                        <v-btn-->
              <!--                                            @click="generateCode(item.card_number,item.device_id) "-->
              <!--                                            style="width: 100%"-->
              <!--                                            small-->
              <!--                                        >-->
              <!--                                            {{ $t('Guest code') }}-->
              <!--                                        </v-btn>-->
              <!--                                        <v-btn-->
              <!--                                            style="width: 100%"-->
              <!--                                            @click="cardEditFun(item)"-->
              <!--                                            small-->
              <!--                                        >-->
              <!--                                            <v-icon-->
              <!--                                                size="small"-->
              <!--                                            >-->
              <!--                                                mdi-pencil-->
              <!--                                            </v-icon>-->
              <!--                                            {{ $t('Edit') }}-->
              <!--                                        </v-btn>-->
              <!--                                        <v-btn-->
              <!--                                            style="width: 100%"-->
              <!--                                            @click="deleteCard(item.id)"-->

              <!--                                            small-->
              <!--                                        >-->
              <!--                                            <v-icon-->
              <!--                                                size="small"-->
              <!--                                            >-->
              <!--                                                mdi-delete-->
              <!--                                            </v-icon>-->
              <!--                                            {{ $t('Delete') }}-->
              <!--                                        </v-btn>-->
              <!--                                    </v-card>-->
              <!--                                </v-menu>-->

              <!--                            </div>-->
            </div>
          </v-card>
        </div>
        <v-card-title
          v-if="!Manager.id"
          class="mt-5 d-sm-flex justify-space-between"
        >
          თქვენ არ ხართ მიბმული არცერთ ლიფტს, გთხოვთ დაუკავშირდით თქვენს
          თავმჯდომარეს
        </v-card-title>
      </v-card>
    </v-col>
  </v-card>
  <v-card v-if="Manager.id !== user.id && !Manager.hide_statistic" class="pa-2">
    <v-row class="justify-space-between">
      <v-col v-if="totalMoney" style="min-height: 100%;" cols="12" md="6">
        <v-card style="height: 100%;" class="overflow-auto pa-2">
          <h3>{{ $t('Amounts deposited in months') }}</h3>
          <h4>
            {{ $t('Total amount deposited') }}:{{ totalMoney }}{{ $t('Lari') }}
          </h4>
          <apexchart
            width="400"
            type="bar"
            :options="chartOptions"
            :series="series"
          ></apexchart>
        </v-card>
      </v-col>
      <v-col cols="12" md="6">
        <v-card
          v-if="seriesB[0] + seriesB[1]"
          style="height: 100%;"
          class="overflow-auto pa-2"
        >
          <h3>{{ $t('Condition of elevators') }}</h3>
          <h4>
            {{ $t('Total number of elevators') }}:{{ seriesB[0] + seriesB[1] }}
          </h4>
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
    <v-row class="justify-space-between">
      <v-col style="min-height: 100%;" cols="12" md="6"></v-col>
      <v-col v-if="seriesC[0] + seriesC[1]" cols="12" md="6">
        <v-card style="height: 100%;" class="overflow-auto pa-2">
          <h3>{{ $t('ქეშბექი') }}</h3>
          <h4>{{ $t('ჯამური ქეშბექი') }}:{{ seriesC[0] + seriesC[1] }}</h4>
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
  </v-card>
  <v-card class="mt-4 pa-2">
    <v-card class="my-4">
      <v-card-title>
        <b>{{ $t('Chairman') }}:</b>
        {{ Manager.name }}
      </v-card-title>
      <v-card-text>
        <div></div>
        <div>
          <b>{{ $t('Phone') }}:</b>
          {{ Manager.phone }}
        </div>
        <div>
          <b>{{ $t('Email') }}:</b>
          {{ Manager.email }}
        </div>
      </v-card-text>
    </v-card>
    <v-card class="mt-4">
      <v-card-title>
        <b>{{ $t('Lifts') }}</b>
      </v-card-title>

      <v-container>
        <v-row v-if="devices && devices.length">
          <v-col
            style="min-width: fit-content;"
            v-for="item in devices"
            cols="12"
            sm="6"
            md="4"
            lg="3"
            :key="item.id"
          >
            <v-card @click="detailDevice(item.id)">
              <template v-slot:title>
                <div class="d-flex justify-space-between">
                  <span>
                    {{ item.name }}
                    <SignalIcon :signal="Math.ceil(item.signal / 20)" />
                  </span>
                </div>
              </template>

              <template v-slot:subtitle>
                <div class="d-flex justify-space-between">
                  <v-chip
                    v-if="
                      new Date(item.lastBeat).getTime() > new Date().getTime()
                    "
                    class=""
                    color="green"
                    text-color="white"
                  >
                    {{ $t('Active') }}
                  </v-chip>
                  <v-chip v-else class="" color="red" text-color="white">
                    {{ $t('Inactive') }}
                  </v-chip>
                </div>
                <b>{{ $t('Count of cards') }}: {{ item.limit }}</b>
                <hr />
                <b>{{ item.dev_id }}</b>
                <hr />
                <b>
                  {{
                    item.op_mode == 0 ? 'გადახდის რიცხვი: ' + item.pay_day : ''
                  }}
                </b>
              </template>

              <template v-slot:text></template>
            </v-card>
            <v-btn
              @click="callToLift(item.dev_id, item.id)"
              style="width: 100%;"
              small
              :disabled="!canGamodzaxeba"
              color="primary"
            >
              {{ $t('გამოიძახე ლიფტი') }}
            </v-btn>
          </v-col>
        </v-row>
      </v-container>
    </v-card>
  </v-card>
  <v-dialog v-model="showModal" max-width="600">
    <v-card>
      <v-card-title class="headline">{{ $t('Edit profile') }}</v-card-title>

      <v-card-text class="pl-3">
        <v-text-field
          v-model="userUpdate.name"
          :label="$t('Name')"
          required
        ></v-text-field>
        <v-text-field
          v-model="userUpdate.email"
          :label="$t('Email')"
          required
        ></v-text-field>
        <v-text-field
          v-model="userUpdate.phone"
          :rules="phoneRules"
          :label="$t('Phone')"
          required
        ></v-text-field>
      </v-card-text>

      <v-card-actions>
        <v-btn color="primary" @click="showModal = false">
          {{ $t('Close') }}
        </v-btn>
        <v-btn color="green darken-1" @click="updatePhone">
          {{ $t('Save') }}
        </v-btn>
        <v-spacer></v-spacer>
      </v-card-actions>
    </v-card>
  </v-dialog>
  <v-dialog :persistent="true" v-model="showElevator" max-width="600">
    <!-- კარტების დამატება -->
    <v-card>
      <v-card-title class="headline">{{ $t('Add card') }}</v-card-title>

      <v-card-text class="pl-3">
        {{ $t('Add_card_description_1') }}
        <br />
        {{ $t('Add_card_description_2') }}
      </v-card-text>
      <v-card-text class="pl-3">
        {{ $t('Card name') }}
        <v-text-field
          v-model="card.name"
          :label="$t('Card name')"
          required
        ></v-text-field>
      </v-card-text>
      <v-card-text class="pl-3">
        {{ $t('6 Digit Code') }}
        <v-text-field
          v-model="card.card_number"
          :label="$t('6 Digit Code')"
          required
        ></v-text-field>
      </v-card-text>

      <v-card-actions>
        <v-btn color="primary" @click="showElevator = false">
          {{ $t('Close') }}
        </v-btn>
        <v-btn color="green darken-1" @click="createCard">
          {{ $t('Save') }}
        </v-btn>
        <v-spacer></v-spacer>
      </v-card-actions>
    </v-card>
  </v-dialog>
  <v-dialog v-model="showCardEditModal" max-width="600">
    <v-card>
      <v-card-title class="headline">{{ $t('Edit') }}</v-card-title>

      <v-card-text class="pl-3">
        {{ $t('Card name') }}
        <v-text-field
          v-model="cardEdit.name"
          :label="$t('Card name')"
          required
        ></v-text-field>
      </v-card-text>

      <v-card-actions>
        <v-btn color="primary" @click="showCardEditModal = false">
          {{ $t('Close') }}
        </v-btn>
        <v-btn color="green darken-1" @click="updateCard">
          {{ $t('Save') }}
        </v-btn>
        <v-spacer></v-spacer>
      </v-card-actions>
    </v-card>
  </v-dialog>
  <v-dialog v-model="showCode" max-width="600">
    <v-card>
      <v-card-title class="headline">{{ $t('Guest code') }}</v-card-title>
      <h1 class="mx-5">{{ code }}</h1>
    </v-card>
  </v-dialog>
  <v-dialog v-model="dialog">
    <v-card>
      <v-card-title>
        <span class="headline">{{ $t('Change password') }}</span>
      </v-card-title>

      <v-card-text>
        <v-form ref="form" v-model="valid">
          <v-text-field
            v-model="old_password"
            :rules="passwordRules"
            :label="$t('Old Password')"
            type="password"
            required
          ></v-text-field>

          <v-text-field
            v-model="password"
            :rules="passwordRules"
            :label="$t('New Password')"
            type="password"
            required
          ></v-text-field>

          <v-text-field
            v-model="password_confirmation"
            :rules="passwordConfirmationRules"
            :label="$t('Confirm New Password')"
            type="password"
            required
          ></v-text-field>
        </v-form>
      </v-card-text>

      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="blue darken-1" @click="dialog = false">
          {{ $t('Close') }}
        </v-btn>
        <v-btn color="blue darken-1" @click="changePassword">
          {{ $t('Save') }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <v-dialog max-width="500px" v-model="showBalance">
    <v-card>
      <v-card-title>
        <span class="headline">{{ $t('Add Balance') }}</span>
      </v-card-title>
      <v-card-text>
        <v-form ref="form" v-model="valid">
          <v-text-field
            v-model.number="amount"
            :label="$t('Amount')"
            type="text"
            required
          ></v-text-field>
          <v-alert v-if="amount" color="warning">
            <v-icon>mdi-alert</v-icon>
            საკომოსიოდან გამომდინარე აღნიშნული თანხის ჩასარიცხად გადასახდელი
            იქნება {{ realAmount }}ლარი
          </v-alert>
        </v-form>
      </v-card-text>

      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="blue darken-1" @click="showBalance = false">
          {{ $t('Close') }}
        </v-btn>
        <v-btn color="blue darken-1" @click="addBalance">{{ $t('Pay') }}</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
  <TransactionUserTable
    :reload="getTransactions"
    :server-items="transaction"
  ></TransactionUserTable>
</template>

<script>
import { mapActions } from 'vuex'
import SignalIcon from '@/components/icon/SignalIcon.vue'
import VueApexCharts from 'vue3-apexcharts'
import TransactionUserTable from '../components/TransactionUserTable.vue'
import router from '@/router'

export default {
  components: { TransactionUserTable, SignalIcon, apexchart: VueApexCharts },
  data() {
    return {
      showElevator: false,
      showBalance: false,
      showCode: false,
      user: this.$store.state.auth.user,
      card: {
        name: '',
        card_number: '',
      },
      cardEdit: {
        name: '',
        id: '',
      },
      isAdmin: false,
      showCardEditModal: false,
      showModal: false,
      newPhone: '',
      desserts: [],
      phoneRules: [
        (v) => !!v || 'ტელეფონის ნომერი აუცილებელია',
        function validatePhone(v) {
          // Check if v is present
          if (!v) return 'ტელეფონის ნომერი აუცილებელია'

          // Check if v starts with a 5 and has a total length of 9 digits
          const regex = /^5\d{8}$/

          return (
            regex.test(v) ||
            'ტელეფონის ნომერი უნდა იწყებოდეს 5ით და შეიცავდეს 9 ციფრს'
          )
        },
      ],
      code: null,
      devices: [],
      userUpdate: {
        name: this.$store.state.auth.user.name,
        phone: this.$store.state.auth.user.phone,
        email: this.$store.state.auth.user.email,
      },
      company: {},
      Manager: {},

      seriesB: [0, 0],

      seriesC: [0, 0],
      series: [
        {
          name: 'გამომუშავება',
          data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        },
      ],
      transaction: [],
      totalMoney: 0,
      dialog: false,
      valid: true,
      old_password: '',
      password: '',
      password_confirmation: '',
      passwordRules: [
        (v) => !!v || 'Password is required',
        (v) => (v && v.length >= 8) || 'Password must be at least 8 characters',
      ],
      passwordConfirmationRules: [
        (v) => !!v || 'Password Confirmation is required',
        (v) =>
          v === this.password ||
          'Password Confirmation must match New Password',
      ],
      amount: null,
      userBalance: 0,
      canGamodzaxeba: true,
    }
  },
  watch: {
    amount(val) {
      if (typeof val !== 'number') {
        this.amount = 0
      }
      if (typeof val < 0) {
        this.amount = 0
      }
    },
  },
  computed: {
    chartOptionsB() {
      return {
        labels: [this.$t('Inactive'), this.$t('Active')],
      }
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
    realAmount() {
      // let result = this.amount / 98 * 100  ;
      // return Math.floor(result * 100 + 2) / 100;
      let result = ((this.amount / 98) * 100).toFixed(2)
      if (!Number.isInteger(this.amount)) {
        result = (Math.ceil(result * 100) / 100).toFixed(2)
      }
      return result
    },
    chartOptionsC() {
      return {
        labels: [this.$t('ჩასარიცხი'), this.$t('ჩარიცხული')],
      }
    },
  },

  async created() {
    this.getBalance()
    setInterval(() => {
      this.getBalance()
    }, 60000)
    this.getCards()
    this.getUserDevice()
    setTimeout(() => {
      this.getTransactions()
    }, 5000)

    this.chackAdminEmail()
  },
  mounted() {
    if (this.$route.query && this.$route.query.status) {
      const status = this.$route.query.status
      if (status === 'success') {
        this.$swal.fire({
          icon: 'success',
          position: 'center',
          allowOutsideClick: false,
          text: 'გადახდა წარმატებით დარეგისტრირდა',
        })
      }
      if (status === 'fail') {
        this.$swal.fire({
          icon: 'error',
          position: 'center',
          allowOutsideClick: false,
          text: 'გადახდა ვერ მოხერხდა',
        })
      }
      this.$router.replace({ query: { status: undefined } })
    }
  },
  methods: {
    chackAdminEmail() {
      const token = localStorage.getItem('vuex')
      let email = JSON.parse(token).auth.user.email
      this.isAdmin = email === 'info@eideas.io'
    },
    getBalance() {
      axios.get('/api/balance/user').then(({ data }) => {
        if (this.userBalance !== data.balance) {
          this.getTransactions()
        }
        this.userBalance = data.balance
      })
    },
    ...mapActions({
      signIn: 'auth/login',
    }),
    getTransactions() {
      axios.get('/api/transactions').then(({ data }) => {
        this.transaction = data
      })
    },
    getCards() {
      axios.get('/api/cards').then(({ data }) => {
        this.desserts = data
      })
    },
    openBalance() {
      this.showBalance = true
    },
    addBalance() {
      // Your logic for adding balance
      axios
        .get(
          '/api/bank/transaction/create/' +
            this.realAmount +
            '/' +
            this.user.id,
        )
        .then(({ data }) => {
          window.location = data
        })
    },
    async deleteCard(id) {
      this.$swal
        .fire({
          title: this.$t('Are you sure you want to delete the card?'),
          showCancelButton: false,
          showDenyButton: true,
          confirmButtonText: this.$t('Yes'),
          denyButtonText: this.$t('Close'),
        })
        .then((result) => {
          /* Read more about isConfirmed, isDenied below */
          if (result.isConfirmed) {
            axios.delete(`/api/cards/${id}`).then((x) => {
              this.$swal.fire({
                icon: 'success',
                position: 'center',
                allowOutsideClick: false,
              })
              this.getCards()
            })
          } else if (result.isDenied) {
          }
        })
    },
    async createCard() {
      await axios.post('/api/cards', this.card)
      this.getCards()
      this.card = {}
      this.showElevator = false
      this.$swal.fire({
        icon: 'success',
        position: 'center',
        allowOutsideClick: false,
      })
    },
    updateCard() {
      axios.put('/api/cards/' + this.cardEdit.id, this.cardEdit).then(() => {
        this.getCards()
        this.showCardEditModal = false
        this.$swal.fire({
          icon: 'success',
          position: 'center',
          allowOutsideClick: false,
        })
      })
    },
    cardEditFun(item) {
      console.log(item)
      this.showCardEditModal = true
      this.cardEdit.name = item.name
      this.cardEdit.id = item.id
    },
    updatePhone() {
      axios.put(`/api/users/${this.user.id}`, this.userUpdate).then(() => {
        this.signIn()
        this.user = { ...this.user, ...this.userUpdate }
        this.$swal.fire({
          icon: 'success',
          position: 'center',
          allowOutsideClick: false,
        })
      })
      this.showModal = false
    },
    generateCode(card, divice) {
      axios
        .get(`/api/cards/generate/code`, {
          params: {
            card: card,
            device_id: divice,
          },
        })
        .then(({ data }) => {
          this.code = data
          this.showCode = true
        })
    },
    callToLift(dev_id, divice) {
      if (this.canGamodzaxeba) {
        this.canGamodzaxeba = false
        axios
          .get(`/api/cards/getLift/calltolift`, {
            params: {
              dev_id: dev_id,
              device_id: divice,
            },
          })
          .then(() => {
            this.$swal.fire({
              icon: 'success',
              position: 'center',
              allowOutsideClick: false,
            })
          })
      }
      setTimeout(() => {
        this.canGamodzaxeba = true
      }, 3000)
    },
    getUserDevice() {
      axios.get('/api/get/devices/user').then(({ data }) => {
        this.devices = data['device']
        if (this.devices) {
          console.log(data['manager'])
          this.Manager = { ...data['manager'] }
          Object.values(data.earnings).forEach((x) => {
            this.series[0].data[x.month - 1] = x.earnings / 100
            this.totalMoney += x.earnings / 100
          })
          this.payedCashback = data['payedCashback']
          this.seriesB = [
            data.deviceActivity.inactive,
            data.deviceActivity.active,
          ]
          this.seriesC = [
            Number(
              (
                (this.totalMoney * this.Manager.cashback) / 100 -
                data['payedCashback']
              ).toFixed(2),
            ),
            Number(data['payedCashback']),
          ]
        }
      })
    },
    detailDevice(id) {
      if (!this.Manager.hide_statistic) {
        router.push({ name: `devicesDetail`, params: { id: id } })
      }
    },
    async changePassword() {
      if (await this.$refs.form.validate()) {
        try {
          await axios.post('api/password/change', {
            old_password: this.old_password,
            password: this.password,
            password_confirmation: this.password_confirmation,
          })
          this.$swal.fire({
            icon: 'success',
            position: 'center',
            allowOutsideClick: false,
          })
          this.dialog = false
        } catch (error) {}
      }
    },
  },
}
</script>

<style scoped>
.v-footer {
  height: 60px;
}
</style>
