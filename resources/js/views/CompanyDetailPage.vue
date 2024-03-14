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
      <v-col v-if="totalMoney" style="min-height: 100%;" cols="12" md="6">
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
        v-if="seriesD[0] + seriesD[1]"
        style="min-height: 100%;"
        cols="12"
        md="6"
      >
        <v-card style="height: 100%;" class="overflow-auto pa-2">
          <h3>{{ $t('Cashback') }}</h3>
          <h4>{{ $t('Total Cashback') }}:{{ seriesD[0] + seriesD[1] }}</h4>
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
          <h4>{{ $t('Total service fee') }}:{{ seriesC[0] + seriesC[1] }}</h4>
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
          (data['companyFee'] / 100).toFixed(2) - data['payedCompanyFee']
        ).toFixed(2)
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
      totalMoney: 0,
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
    getCashBackData(data) {
      let deviceTariffCombined = data.managers
        .map((val) => val.deviceTariffAmounts.reduce((a, b) => a + b))
        .reduce((a, b) => a + b)
      console.log(data)
      let cashback = data.company.cashback
      let needToPay = Object.values(data.earnings)[0].earnings / 100
      console.log(needToPay)
      // this.totalMoney = 210
      let cashbackAmount = (needToPay * cashback) / 100

      if (cashbackAmount > needToPay - deviceTariffCombined) {
        console.log('1')

        return needToPay - deviceTariffCombined
      } else {
        return cashbackAmount
      }
    },
    loadItems() {
      axios.get('/api/companies/' + this.$route.params.id).then(({ data }) => {
        this.data = data

        Object.values(this.data.earnings).forEach((x) => {
          this.series[0].data[x.month - 1] = x.earnings / 100
          this.totalMoney += x.earnings / 100
        })
        this.seriesB = [
          this.data.deviceActivity.inactive,
          this.data.deviceActivity.active,
        ]
        this.seriesC = [
          this.getCashBackData(data),
          Number(this.data.payedCompanyFee),
        ]
        this.getCashBackData(data)
        this.seriesD = [
          Number(
            (
              Number(this.data['needToPay']) -
              Number(this.data['payedCashback'])
            ).toFixed(2),
          ),
          Number(this.data['payedCashback'].toFixed(2)),
        ]
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
