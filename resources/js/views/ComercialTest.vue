<template>
  <v-container>
    <!-- Line and Area Charts -->
    <v-row>
      <v-col cols="12" md="6">
        <apexchart
          type="line"
          :options="lineChartOptions"
          :series="lineChartSeries"
        ></apexchart>
      </v-col>
      <v-col cols="12" md="6">
        <apexchart
          type="area"
          :options="areaChartOptions"
          :series="areaChartSeries"
        ></apexchart>
      </v-col>
    </v-row>

    <!-- Bar and Column Charts -->
    <v-row>
      <v-col cols="12" md="6">
        <apexchart
          type="bar"
          :options="barChartOptions"
          :series="barChartSeries"
        ></apexchart>
      </v-col>
      <v-col cols="12" md="6">
        <apexchart
          type="column"
          :options="columnChartOptions"
          :series="columnChartSeries"
        ></apexchart>
      </v-col>
    </v-row>

    <!-- Pie and Donut Charts -->
    <v-row>
      <v-col cols="12" md="6">
        <apexchart
          type="pie"
          :options="pieChartOptions"
          :series="pieChartSeries"
        ></apexchart>
      </v-col>
      <v-col cols="12" md="6">
        <apexchart
          type="donut"
          :options="donutChartOptions"
          :series="donutChartSeries"
        ></apexchart>
      </v-col>
    </v-row>

    <!-- Radar and Polar Area Charts -->
    <v-row>
      <v-col cols="12" md="6">
        <apexchart
          type="radar"
          :options="radarChartOptions"
          :series="radarChartSeries"
        ></apexchart>
      </v-col>
      <v-col cols="12" md="6">
        <apexchart
          type="polarArea"
          :options="polarAreaChartOptions"
          :series="polarAreaChartSeries"
        ></apexchart>
      </v-col>
    </v-row>

    <!-- Scatter and Bubble Charts -->
    <v-row>
      <v-col cols="12" md="6">
        <apexchart
          type="scatter"
          :options="scatterChartOptions"
          :series="scatterChartSeries"
        ></apexchart>
      </v-col>
      <v-col cols="12" md="6">
        <apexchart
          type="bubble"
          :options="bubbleChartOptions"
          :series="bubbleChartSeries"
        ></apexchart>
      </v-col>
    </v-row>

    <!-- Heatmap and Treemap Charts -->
    <v-row>
      <v-col cols="12" md="6">
        <apexchart
          type="heatmap"
          :options="heatmapChartOptions"
          :series="heatmapChartSeries"
        ></apexchart>
      </v-col>
      <v-col cols="12" md="6">
        <apexchart
          type="treemap"
          :options="treemapChartOptions"
          :series="treemapChartSeries"
        ></apexchart>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import VueApexCharts from 'vue3-apexcharts'

export default {
  name: 'PageTest',
  components: {
    apexchart: VueApexCharts,
  },
  data() {
    return {
      // Line Chart
      lineChartOptions: {
        chart: {
          type: 'line',
        },
        xaxis: {
          categories: [
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'May',
            'Jun',
            'Jul',
            'Aug',
            'Sep',
          ],
        },
      },
      lineChartSeries: [
        {
          name: 'Series 1',
          data: [30, 40, 35, 50, 49, 60, 70, 91, 125],
        },
      ],
      // Area Chart
      areaChartOptions: {
        chart: {
          type: 'area',
        },
        xaxis: {
          categories: [
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'May',
            'Jun',
            'Jul',
            'Aug',
            'Sep',
          ],
        },
      },
      areaChartSeries: [
        {
          name: 'Series 1',
          data: [30, 40, 35, 50, 49, 60, 70, 91, 125],
        },
      ],
      // Bar Chart
      barChartOptions: {
        chart: {
          type: 'bar',
        },
        xaxis: {
          categories: [
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'May',
            'Jun',
            'Jul',
            'Aug',
            'Sep',
          ],
        },
      },
      barChartSeries: [
        {
          name: 'Series 1',
          data: [30, 40, 35, 50, 49, 60, 70, 91, 125],
        },
      ],
      // Column Chart
      columnChartOptions: {
        chart: {
          type: 'column',
        },
        xaxis: {
          categories: [
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'May',
            'Jun',
            'Jul',
            'Aug',
            'Sep',
          ],
        },
      },
      columnChartSeries: [
        {
          name: 'Series 1',
          data: [30, 40, 35, 50, 49, 60, 70, 91, 125],
        },
      ],
      // Pie Chart
      pieChartOptions: {
        chart: {
          type: 'pie',
        },
      },
      pieChartSeries: [44, 55, 13, 43, 22],
      // Donut Chart
      donutChartOptions: {
        chart: {
          type: 'donut',
        },
      },
      donutChartSeries: [44, 55, 13, 43, 22],
      // Radar Chart
      radarChartOptions: {
        chart: {
          type: 'radar',
        },
      },
      radarChartSeries: [
        {
          name: 'Series 1',
          data: [80, 50, 30, 40, 100],
        },
      ],
      // Polar Area Chart
      polarAreaChartOptions: {
        chart: {
          type: 'polarArea',
        },
      },
      polarAreaChartSeries: [30, 40, 45, 50, 49],
      // Scatter Chart
      scatterChartOptions: {
        chart: {
          type: 'scatter',
        },
      },
      scatterChartSeries: [
        {
          name: 'Series 1',
          data: [
            [10, 20],
            [15, 25],
            [20, 30],
            [25, 35],
            [30, 40],
          ],
        },
      ],
      // Bubble Chart
      bubbleChartOptions: {
        chart: {
          type: 'bubble',
        },
      },
      bubbleChartSeries: [
        {
          name: 'Series 1',
          data: [
            {
              x: 10,
              y: 20,
              z: 30,
            },
            {
              x: 15,
              y: 25,
              z: 35,
            },
            {
              x: 20,
              y: 30,
              z: 40,
            },
            {
              x: 25,
              y: 35,
              z: 45,
            },
            {
              x: 30,
              y: 40,
              z: 50,
            },
          ],
        },
      ],
      // Heatmap Chart
      heatmapChartOptions: {
        chart: {
          type: 'heatmap',
        },
      },
      heatmapChartSeries: [
        {
          name: 'Series 1',
          data: [
            {
              x: 'Monday',
              y: 0,
              value: 4,
            },
            {
              x: 'Tuesday',
              y: 1,
              value: 2,
            },
            {
              x: 'Wednesday',
              y: 2,
              value: 6,
            },
            {
              x: 'Thursday',
              y: 3,
              value: 8,
            },
            {
              x: 'Friday',
              y: 4,
              value: 10,
            },
            {
              x: 'Saturday',
              y: 5,
              value: 12,
            },
            {
              x: 'Sunday',
              y: 6,
              value: 14,
            },
          ],
        },
      ],
      // Treemap Chart
      treemapChartOptions: {
        chart: {
          type: 'treemap',
        },
        tooltip: {
          style: {
            fontSize: '16px', // Increase font size for tooltip
          },
        },
        plotOptions: {
          treemap: {
            style: {
              fontSize: '24px', // Increase font size for treemap elements
            },
            dataLabels: {
              style: {
                fontSize: '28px', // Set font size for text inside treemap boxes
              },
            },
          },
        },
      },
      treemapChartSeries: [
        {
          data: [
            {
              x: 'თავისუფალი პარკირება: 8',
              y: 500,
              fillColor: '#96d1f8', // Custom color for the first circle
            },
            {
              x: 'გახსენი შლაგბაუმი',
              y: 300,
              fillColor: '#6bac8e', // Custom color for the second circle
            },
            {
              x: 'შლაგბაუმი დაკეტილია',
              y: 400,
              fillColor: '#ff1b1e', // Custom color for the third circle
            },
            {
              x: 'შევსებული პატკირება:  20',
              y: 400,
              fillColor: '#F7EE14', // Custom color for the fourth circle
            },
          ],
        },
      ],
    }
  },
}
</script>

<style scoped>
h1 {
  font-size: 2em;
}
</style>
