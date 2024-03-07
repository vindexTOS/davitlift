<template>
  <v-row class="justify-space-between">
    <v-col cols="12" md="5">
      <v-card width="fit-content" class="pa-2">
      <h3>ჩარიცხული თანხები თვეების მიედვით </h3>
      <apexchart
        width="500"
        type="bar"
        :options="chartOptions"
        :series="series"
      ></apexchart>
      </v-card>
    </v-col>
  <v-col cols="12" md="5">
    <v-card class="pa-2">

    <h3>ლიფტების მდგომარეობა </h3>

    <apexchart width="500" height="350" type="donut" :options="chartOptionsB" :series="seriesB"></apexchart>
    </v-card>

  </v-col>
  </v-row>
</template>
<script>
import VueApexCharts from "vue3-apexcharts";
export default {
  components: {
    apexchart: VueApexCharts,
  },
  data: function() {
    return {
      chartOptionsB: {
        labels: ["არაა აქტიური", "აქტიურია",],
      },
      seriesB: [1, 39],
      series: [
        {
          name: "series-1",
          data: [300, 440, 350, 500, 490, 600, 700, 901,320,110],
        },
      ],
    };
  },
  computed: {
    chartOptionsB() {
      return {
        labels: [this.$t('Active'),this.$t('Inactive')],
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
  },
  methods: {

    generateData(baseval, count, yrange) {
      var i = 0;
      var series = [];
      while (i < count) {
        var x = Math.floor(Math.random() * (750 - 1 + 1)) + 1;
        var y = Math.floor(Math.random() * (yrange.max - yrange.min + 1)) + yrange.min;
        var z = Math.floor(Math.random() * (75 - 15 + 1)) + 15;

        series.push([x, y, z]);
        baseval += 86400000;
        i++;
      }
      return series;
    }
  }
};
</script>
<style scoped>

</style>
