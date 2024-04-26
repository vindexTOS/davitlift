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
              <v-btn style="width: 100%;" @click="showBalance = true" small>
                <v-icon size="small">mdi-cash</v-icon>
                {{ $t('Add Balance') }}
              </v-btn>
              <v-btn @click="dialog = true" style="width: 100%;">
                {{ $t('Change password') }}
              </v-btn>
            </v-card>
          </v-menu>
        </v-card-title>
        <!--  უზერის ინფორმაცია -->
        <v-card-text class="px-5">
          <div class="d-flex justify-space-between align-center">
            <v-list-item-title>
              {{ $t('Email') }}:
              <div class="text-grey">{{ user.email }}</div>
              <!-- <div class="text-grey">{{  }}</div> -->
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
                {{ user.balance / 100 }}{{ $t('Lari') }}
              </div>
            </v-list-item-title>
          </div>
        </v-card-text>
      </v-card>
    </v-col>
    <v-col cols="12" md="6">
      <v-card min-width="48%" class="pb-16 mt-md-0 mt-10">
        <v-card-title class="mt-5 d-sm-flex justify-space-between">
          <div>{{ $t('Elevator cards') }}</div>
          <v-btn v-if="role !== 'user'" @click="showElevator = true">
            {{ $t('Add card') }}
          </v-btn>
        </v-card-title>
        <div>
          <!--  დამატებითი კარტები  -->
          <v-card v-for="item in desserts" class="ma-4 pa-5" :key="item.name">
            <div>
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
                      <v-btn
                        style="width: 100%;"
                        @click="deleteCard(item.id)"
                        small
                      >
                        <v-icon size="small">
                          mdi-delete
                        </v-icon>
                        {{ $t('Delete') }}
                      </v-btn>
                    </v-card>
                  </v-menu>
                </div>
                <div>
                  {{ $t('Card Number') }}:
                  {{ item.card_number }}
                </div>
              </div>
              <div></div>
            </div>
          </v-card>
        </div>
      </v-card>
    </v-col>
  </v-card>
  <v-card class="pa-2">
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
    <v-card v-if="Manager" class="my-4">
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
        <v-row v-if="devices">
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
              </template>

              <template v-slot:text></template>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </v-card>
  </v-card>
  <v-dialog v-model="showModal" max-width="600">
    <v-card>
      <v-card-title class="headline">{{ $t('Edit profile') }}</v-card-title>
      <!--  უსერის დასააფდეითებელი ინფორმაცია  -->
      <v-card-text class="pl-3">
        <v-text-field
          v-model="user.name"
          :label="$t('Name')"
          required
        ></v-text-field>
        <v-text-field
          v-model="user.email"
          :label="$t('Email')"
          required
        ></v-text-field>
        <v-text-field
          v-model="user.phone"
          :rules="phoneRules"
          :label="$t('Phone')"
          required
        ></v-text-field>
        <v-text-field
          v-model="user.balance"
          :label="$t('Balance')"
          required
        ></v-text-field>
        <v-select
          v-model="user.role"
          :items="roles"
          label="როლი"
          required
        ></v-select>
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
  <v-dialog v-model="showElevator" max-width="600">
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
  <v-dialog v-model="dialog" max-width="500px">
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

import router from '@/router'
import Swal from 'sweetalert2'
import TransactionUserTable from '../components/TransactionUserTable.vue'

export default {
  components: { TransactionUserTable, SignalIcon, apexchart: VueApexCharts },
  data() {
    return {
      selectedRole: null,
      roles: ['user', 'member'],
      showElevator: false,
      showBalance: false,
      showCode: false,
      user: {},
      isAdmin: false,
      role: '',
      transaction: [],
      card: {
        name: '',
        card_number: '',
      },
      cardEdit: {
        name: '',
        id: '',
      },
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

      company: {},
      Manager: {},
      seriesB: [0, 0],

      chartOptionsC: {
        labels: ['ჩარიცხული ქეშბექი', 'ჩასარიცხი ქეშბექი'],
      },
      seriesC: [0, 0],
      series: [
        {
          name: 'გამომუშავება',
          data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        },
      ],
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
    }
  },

  async created() {
    this.getCards()
    this.getUserDevice()
    this.getTransactions()
    this.chackAdminEmail()
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
    realAmount() {
      // old logic for refernce
      // let result = this.amount / 98 * 100;
      // return Math.floor(result * 100) / 100;
      // თუ  amount არის ინტეჯერი უბრალოდ ვამატებთ 2% და ვაცილებთ ზედმეტ ფლოად ციფრებს
      let result = ((this.amount / 98) * 100).toFixed(2)
      if (!Number.isInteger(this.amount)) {
        result = (Math.ceil(result * 100) / 100).toFixed(2)
      }
      return result
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
    chartOptionsB() {
      return {
        labels: [this.$t('ჩარიცხული'), this.$t('ჩასარიცხი')],
      }
    },
  },
  methods: {
    chackAdminEmail() {
      const token = localStorage.getItem('vuex')
      let email = JSON.parse(token).auth.user.email
      this.isAdmin = email === 'info@eideas.io'
      this.role = JSON.parse(token).auth.user.role
    },
    addBalance() {
      // Your logic for adding balance
      axios
        .get(
          '/api/bank/transaction/create/' +
            this.realAmount +
            '/' +
            this.$route.params.id,
        )
        .then(({ data }) => {
          window.location = data
        })
    },
    ...mapActions({
      signIn: 'auth/login',
    }),
    getTransactions() {
      axios
        .get('/api/per/user/transactions/' + this.$route.params.id)
        .then(({ data }) => {
          this.transaction = data
          console.log(data)
        })
    },
    getCards() {
      axios.get('/api/user/cards/' + this.$route.params.id).then(({ data }) => {
        this.desserts = data
      })
    },
    async deleteCard(id) {
      let userDecision = window.confirm(
        this.$t('Are you sure you want to delete the card?'),
      )
      if (userDecision) {
        await axios.delete(`/api/cards/${id}`)
        this.getCards()
      }
    },
    async createCard() {
      await axios.post('/api/user/add/card', {
        ...this.card,
        user_id: this.$route.params.id,
      })
      this.getCards()
      this.card = {}
      this.showElevator = false
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
      // console.log(item)
      this.showCardEditModal = true
      this.cardEdit.name = item.name
      this.cardEdit.id = item.id
    },
    updatePhone() {
      axios.put(`/api/transaction/update-balance`, this.user).then(() => {
        this.user = { ...this.user }
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
        .then(({ data }) => (this.code = data))
      this.showCode = true
    },
    getUserDevice() {
      axios.get('/api/users/' + this.$route.params.id).then(({ data }) => {
        this.user = data
      })
      axios
        .get('/api/get/devices/user/' + this.$route.params.id)
        .then(({ data }) => {
          this.devices = data['device']
          if (this.devices) {
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
              Number(data['payedCashback']),
              Number(
                (
                  (this.totalMoney * this.Manager.cashback) / 100 -
                  data['payedCashback']
                ).toFixed(2),
              ),
            ]
          }
        })
    },
    detailDevice(id) {
      router.push({ name: `devicesDetail`, params: { id: id } })
    },
    onClick() {
      console.log(this.user.balance)
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
