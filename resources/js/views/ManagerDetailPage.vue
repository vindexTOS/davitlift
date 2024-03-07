<template>
  <v-card v-if=" data.manager" class="mb-4">
    <v-card-title>
      <v-btn icon="mdi-arrow-left" size="small" @click="goBack"></v-btn>
      {{ data.manager.name }}
    </v-card-title>
    <v-card-text>
      <div>
      </div>
      <div>
        <b>{{ $t("Phone") }}:</b> {{ data.manager.phone }}
      </div>
      <div>
        <b>{{ $t("Email") }}:</b> {{ data.manager.email }}
      </div>
      <div>
        <b>{{ $t("Cashback") }}:</b> {{ data.manager.cashback }}%
      </div>
    </v-card-text>
  </v-card>

  <v-card class="pa-2">
    <v-row class="justify-space-between">
      <v-col style="min-height: 100%" cols="12" md="6">
        <v-card style="height: 100%" class="overflow-auto pa-2">
          <h3>{{ $t('Amounts deposited in months') }}</h3>
          <h4>{{ $t('Total amount earned') }}: {{ totalMoney }}{{$t('Lari')}}</h4>
          <apexchart
              width="400"
              type="bar"
              :options="chartOptions"
              :series="series"
          ></apexchart>
        </v-card>
      </v-col>
      <v-col cols="12" md="6">
        <v-card style="height: 100%" class="overflow-auto pa-2">

          <h3>{{ $t('Condition of elevators') }} </h3>
          <h4>{{ $t('Total number of elevators') }}:{{ seriesB[0] + seriesB[1] }}</h4>
          <apexchart width="400" height="350" type="donut" :options="chartOptionsB" :series="seriesB"></apexchart>
        </v-card>

      </v-col>
    </v-row>
    <v-row class="justify-space-between">
      <v-col style="min-height: 100%" cols="12" md="6">

      </v-col>
      <v-col v-if="seriesC" cols="12" md="6">
        <v-card v-if=" seriesC[0] + seriesC[1]" style="height: 100%" class="overflow-auto pa-2">

          <h3>{{ $t('Cashback') }} </h3>
          <h4>{{ $t('Total Cashback') }}:{{ seriesC[0] + seriesC[1] }}</h4>
          <apexchart width="400" height="350" type="donut" :options="chartOptionsC" :series="seriesC"></apexchart>
        </v-card>

      </v-col>
    </v-row>

  </v-card>
  <div class="mt-3">
    <v-card>
      <v-container>

        <v-row>


          <v-checkbox :label="$t('Active')" style="min-width: fit-content" v-model="isActive"></v-checkbox>

          <v-checkbox :label="$t('Inactive')" style="min-width:  fit-content" v-model="notActive"></v-checkbox>
          <v-checkbox :label="$t('With problems')" style="min-width: fit-content" v-model="hasError"></v-checkbox>
          <v-checkbox :label="$t('Deleted')" style="min-width: fit-content" v-model="deleted"></v-checkbox>
        </v-row>
      </v-container>
      <v-container>

        <v-row v-if="filtredDevices">

          <v-col
              style="min-width: fit-content;"
              v-for="item in filtredDevices "
              cols="12"
              sm="6"
              md="4"
              lg="3"
              :key="item.id"
          >
            <v-card
                @click="detailDevice(item.id)"
            >
              <template v-slot:title>
                <div class="d-flex justify-space-between">
                            <span>

                                <v-icon
                                    v-if="item.errors.length"
                                    size="xs"
                                    color="red"
                                >
                                    mdi-alert
                                </v-icon>
                            {{ item.name }}
                            <SignalIcon :signal="Math.ceil(item.signal/20)"/>
                            </span>

                </div>
              </template>

              <template v-slot:subtitle>

                <div class="d-flex justify-space-between">
                  <v-chip
                      v-if="new Date(item.lastBeat).getTime() > new Date().getTime()"
                      class=""
                      color="green"
                      text-color="white"
                  >
                    {{$t('Active')}}
                  </v-chip>
                  <v-chip
                      v-else
                      class=""
                      color="red"
                      text-color="white"
                  >
                    {{$t('Inactive')}}

                  </v-chip>
                </div>
                <b>{{$t('Count of cards')}}: {{ item.limit }}</b>
                <hr>
                <b>{{ item.dev_id }}</b>
                <hr>
                <b>{{ item.network == 1 ? 'Ethernet' : item.network == 2 ? 'Cellular' : 'Wifi' }}</b>
                  <hr>
                  <b>{{ item.op_mode == 0? 'გადახდის რიცხვი: ' + item.pay_day:'' }}</b>

              </template>

              <template v-slot:text>

              </template>
            </v-card>
          </v-col>
        </v-row>
      </v-container>

    </v-card>
    <div class="mt-3">
      <CashbackTable @getCashback="getCashback" :serverItems="cashbackData['transaction']"
                     :availableCashback="cashbackData['total'] - cashbackData['totalWithdrow']"
                     :maxCashback="maxCashback"></CashbackTable>
    </div>
  </div>
</template>
<script>
import VueApexCharts from "vue3-apexcharts";
import router from "@/router";
import {th} from "vuetify/locale";
import ManagersTable from "../components/ManagersTable.vue";
import SignalIcon from "@/components/icon/SignalIcon.vue";
import CashbackTable from "../components/CashbackTable.vue";

export default {
  components: {
    SignalIcon,
    apexchart: VueApexCharts, ManagersTable, CashbackTable
  },
  data: function () {
    return {
      data: {},
      hasError: true,
      isActive: true,
      notActive: true,
      deleted: false,
      seriesB: [0, 0],
      seriesC: [0, 0],
      series: [
        {
          name: "",
          data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        },
      ],
      totalMoney: 0,
      cashbackData: {}
    };
  },
  created() {
    this.loadItems();
    this.getCashback();
  },
  computed: {
      chartOptionsB() {
          return {
              labels: [this.$t('Inactive'),this.$t('Active'),],
          }
      },
      chartOptions() {
          return  {
              chart: {
                  id: "vuechart-exampl2e",
              }
              ,
              xaxis: {
                  categories: [this.$t('იან'), this.$t('თებ'), this.$t('მარტი'), this.$t('აპრ'), this.$t('მაისი'),
                      this.$t('ივნ'), this.$t('ივლ'), this.$t('აგვ'),
                      this.$t('სექ'), this.$t('ოქტ'), this.$t('ნოემბ'),
                      this.$t('დეკ')],
              }
              ,
          }
      },
    filtredDevices() {
      return this.data.device ? this.data.device.filter(x => {
        const active = new Date(x.lastBeat).getTime() > new Date().getTime()
        if (!x.deleted_at) {
          if (this.isActive && active) {
            return x;
          }
          if (this.notActive && !active) {
            return x;
          }
          if (this.hasError && x.errors.length) {
            return x
          }
        }
        if (this.deleted && x.deleted_at) {
          return x
        }
      }) : [];
    },
      chartOptionsC() {
          return {
              labels: [this.$t('ჩასარიცხი'),this.$t('ჩარიცხული'),],
          }
      },
    maxCashback() {
      if (this.data.manager) {
        return (((this.totalMoney * this.data.manager.cashback) / 100) - this.cashbackData.total).toFixed(2)
      }
    },
    seriesC() {
      if (this.cashbackData.total > -1) {
        return [Number(this.maxCashback), Number(this.cashbackData.total)]
      }
      return  [0,0]
    },
  },
  methods: {
    getCashback() {
      axios.get(`/api/get/pay/cashback/${this.$route.params.companyId}/${this.$route.params.id}`)
          .then(({data}) => {
            this.cashbackData = data

          })
    },
    loadItems() {
      axios.get('/api/companies/manager/' + this.$route.params.id).then(({data}) => {
        this.data = data;
        Object.values(this.data.earnings).forEach(x => {
          this.series[0].data[x.month - 1] = x.earnings / 100
          this.totalMoney += x.earnings / 100
        })
        this.seriesB = [this.data.deviceActivity.inactive, this.data.deviceActivity.active];

      })
    },
    detailDevice(id) {
      router.push({name: `devicesDetail`, params: {id: id}})
    },
    goBack() {
      router.go(-1)
    }
  }
};
</script>
<style scoped>

</style>
