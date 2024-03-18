<template>
  <v-card v-if="data.company" class="mb-3">
    <v-card-title>
      {{ data.company.company_name }}
    </v-card-title>
    <v-card-subtitle>
      {{ data.company.sk_code }}
    </v-card-subtitle>
    <v-card-text>
      <v-card>
        <v-card-title>
          {{ $t('Contact information') }}
        </v-card-title>
        <v-card-text>
          <div>
            <b>{{ $t('Name') }}:</b>
            {{ data.company.admin.name }}
          </div>
          <div>
            <b>{{ $t('Phone') }}:</b>
            {{ data.company.admin.phone }}
          </div>
          <div>
            <b>{{ $t('Email') }}:</b>
            {{ data.company.admin.email }}
          </div>
        </v-card-text>
      </v-card>
      <h4></h4>
    </v-card-text>
  </v-card>

  <v-card class="pa-2">
    <v-row class="justify-space-between">
      <v-col v-if="fullAmount" style="min-height: 100%;" cols="12" md="6">
        <v-card style="height: 100%;" class="overflow-auto pa-2">
          <h3>{{ $t('Amounts deposited in months') }}</h3>
          <apexchart
            width="400"
            type="bar"
            :options="chartOptions"
            :series="series"
          ></apexchart>
        </v-card>
      </v-col>
      <v-col v-if="seriesB[0] + seriesB[1]" cols="12" md="6">
        <v-card style="height: 100%;" class="overflow-auto pa-2">
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
      <v-col
        v-if="seriesD[0] + seriesD[1] > 0"
        style="min-height: 100%;"
        cols="12"
        md="6"
      >
        <v-card style="height: 100%;" class="overflow-auto pa-2">
          <h3>{{ $t('Cashback') }}</h3>
          <h4>
            {{ $t('Total Cashback') }}:{{ seriesD[0] <= 0 ? 0 : seriesD[0] }}
          </h4>
          <apexchart
            width="400"
            height="350"
            type="donut"
            :options="chartOptionsD"
            :series="seriesD"
          ></apexchart>
        </v-card>
      </v-col>
      <v-col v-if="seriesC[0] + seriesC[1]" cols="12" md="6">
        <v-card style="height: 100%;" class="overflow-auto pa-2">
          <h3>{{ $t('Service fee') }}</h3>
          <h4>{{ $t('Total service fee') }}:{{ seriesC[0] }}</h4>
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
  <v-card class="pa-4 mt-2"></v-card>
  <div class="mt-3">
    <ManagersTable
      :companyId="$route.params.id"
      @reload="loadItems"
      :server-items="data['managers']"
    ></ManagersTable>
  </div>
  <div class="mt-3">
    <CashbackTable
      :companyId="$route.params.id"
      @getCashback="loadItems"
      :max-cashback="
        (
          seriesD[0]
        ) 
      "
      :isCompanyPage="true"
      :server-items="data['companyTransaction']"
    ></CashbackTable>
  </div>
</template>
<script>
import VueApexCharts from 'vue3-apexcharts'
import Router from '@/router'
import { th } from 'vuetify/locale'
import ManagersTable from '../components/ManagersTable.vue'
import CashbackTable from '../components/CashbackTable.vue'

export default {
  components: {
    CashbackTable,
    apexchart: VueApexCharts,
    ManagersTable,
  },
  data: function () {
    return {
      data: {},
      companyFee: 0,
      seriesB: [0, 0],

      seriesC: [0, 0],
      chartOptionsD: {
        labels: ['ჩასარიცხი', 'ჩარიცხული'],
      },
      seriesD: [0, 0],
      series: [
        {
          name: 'გამომუშავება',
          data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        },
      ],
      deviceEarningByMonth: [],
      totalMoney: 0,
      fullAmount: 0,
    }
  },
  created() {
    this.loadItems()
  },
  computed: {
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
        labels: [this.$t('Inactive'), this.$t('Active')],
      }
    },
    chartOptionsC() {
      return {
        labels: [this.$t('ჩასარიცხი'), this.$t('ჩარიცხული')],
      }
    },
    chartOptionsD() {
      return {
        labels: [this.$t('ჩასარიცხი'), this.$t('ჩარიცხული')],
      }
    },
  },
  methods: {
    getCashBackData(data, deviceEarning) {
      let deviceTariffCombined = data.managers
        .map((val) => val.deviceTariffAmounts.reduce((a, b) => a + b))
        .reduce((a, b) => a + b)

      let cashback = data.company.cashback
      // let needToPay = Object.values(data.earnings)[0].earnings / 100
      let needToPay = deviceEarning

      let cashbackAmount = (needToPay * cashback) / 100

      if (cashbackAmount > needToPay - deviceTariffCombined) {
        // this.companyFee = deviceTariffCombined
        let result = needToPay - deviceTariffCombined

        return result
      } else {
        let result = cashbackAmount

        // this.companyFee = needToPay - cashbackAmount
        return result
      }
    },
    getCompanyFee(data) {
      let deviceTariffCombined = data.managers
        ?.map((val) => val.deviceTariffAmounts.reduce((a, b) => a + b))
        .reduce((a, b) => a + b)
      console.log(data.managers)
      let cashback = data.company.cashback
      let needToPay = Object.values(data.earnings)[0].earnings / 100

      let cashbackAmount = (needToPay * cashback) / 100

      if (cashbackAmount > needToPay - deviceTariffCombined) {
        this.companyFee = deviceTariffCombined
      } else {
        this.companyFee = needToPay - cashbackAmount
      }
    },

    loadItems() {
      axios.get('/api/companies/' + this.$route.params.id).then(({ data }) => {
        this.data = data
        this.getCompanyFee(data)
        let lastEarning

        const sortedEarnings = [...Object.values(this.data.earnings)].sort(
          (a, b) => new Date(a.fullTime) - new Date(b.fullTime),
        )

        sortedEarnings.forEach((x) => {
          this.fullAmount += x.earnings / 100
          const earningsIndex = new Date(x.fullTime).getMonth()
          if (this.series[0].data[earningsIndex] === undefined) {
            this.series[0].data[earningsIndex] = 0
          }
          this.series[0].data[earningsIndex] += x.earnings / 100
        })

        Object.values(this.data.earnings)[0].earnings / 100
        this.deviceEarningByMonth = Array.from(
          Object.values(this.data.earnings),
        )
          .map((x) => {
            if (this.getCashBackData(data, x.earnings / 100) < 0) {
              return 0
            } else {
              return this.getCashBackData(data, x.earnings / 100)
            }
          })
          .reduce((a, b) => a + b)

        let amountAlreadyPayed =
          data['companyTransaction'].length <= 0
            ? [{ amount: '0' }, { amount: '0' }]
            : data['companyTransaction']
        let amountAlreadyPayedNumber = amountAlreadyPayed
          ?.map((val) => Number(val.amount))
          .reduce((a, b) => a + b)
        let finalResultOfDisplayAmount =
          this.deviceEarningByMonth - amountAlreadyPayedNumber

        Object.values(this.data.earnings).forEach((x) => {
          if (
            !lastEarning ||
            new Date(x.fullTime) > new Date(lastEarning.fullTime)
          ) {
            lastEarning = x
            this.totalMoney = lastEarning.earnings / 100
          }
        })

        this.seriesB = [
          this.data.deviceActivity.inactive,
          this.data.deviceActivity.active,
        ]

        this.seriesD = [
          finalResultOfDisplayAmount,
          Number(this.data.payedCompanyFee),
        ]

        this.seriesC = [this.companyFee, 0]
      })
    },
    generateData(baseval, count, yrange) {
      var i = 0
      var series = []
      while (i < count) {
        var x = Math.floor(Math.random() * (750 - 1 + 1)) + 1
        var y =
          Math.floor(Math.random() * (yrange.max - yrange.min + 1)) + yrange.min
        var z = Math.floor(Math.random() * (75 - 15 + 1)) + 15

        series.push([x, y, z])
        baseval += 86400000
        i++
      }
      return series
    },
  },
}
</script>
<style scoped></style>
