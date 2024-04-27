<template>
  <div class="custom-container">
    <!-- Line and Area Charts -->
    <!-- <v-row>
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
    </v-row> -->

    <!-- Bar and Column Charts -->
    <!-- <v-row>
    
      <v-col cols="12" md="6">
        <apexchart
          type="column"
          :options="columnChartOptions"
          :series="columnChartSeries"
        ></apexchart>
      </v-col>
    </v-row> -->

    <!-- Pie and Donut Charts -->
    <!-- <v-row>
      <v-col cols="12" md="6">
        <apexchart
          type="donut"
          :options="donutChartOptions"
          :series="donutChartSeries"
        ></apexchart>
      </v-col>
    </v-row> -->

    <!-- Radar and Polar Area Charts -->
    <!-- <v-row>
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
    </v-row> -->

    <!-- Scatter and Bubble Charts -->
    <!-- <v-row>
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
    </v-row> -->

    <section>
      <div class="row-gap">
        <div class="custom-col">
          <h1 style="margin-left: 30px;">შლაგბაუმი</h1>
          <div class="chart-container">
            <apexchart
              type="treemap"
              :options="treemapChartOptions"
              :series="treemapChartSeries"
            ></apexchart>
            <button @click="btnClick()" class="custom-button">
              გახსენი შლაგბაუმი
            </button>
          </div>
        </div>
        <div class="custom-col">
          <h1 style="margin-left: 30px;">დომოფონი</h1>
          <div class="chart-container">
            <apexchart
              type="treemap"
              :options="treemapChartOptionsTwo"
              :series="treemapChartSeriesTwo"
            ></apexchart>
            <button @click="btnClick()" class="custom-button">
              გახსენი კარი
            </button>
          </div>
        </div>
      </div>

      <!-- donay -->

      <!-- lll -->
      <div class="row-gap-card-system">
        <h1 style="padding-bottom: 20px;">ლიფტის საბარათე სისტემა</h1>

        <div class="row-gap">
          <div class="custom-col">
            <h1 style="margin-left: 30px;">ხარჯები</h1>

            <div class="pie-chart">
              <!-- Adjust width and height as needed -->
              <apexchart
                type="pie"
                :options="pieChartOptions"
                :series="pieChartSeries"
              ></apexchart>
            </div>
          </div>
          <button @click="btnClick()" class="call-lift-btn">
            გამოიძახე ლიფტი
          </button>
          <div class="custom-col">
            <h1 style="margin-left: 30px;">შემოსავლები</h1>

            <apexchart
              type="bar"
              :options="barChartOptions"
              :series="barChartSeries"
            ></apexchart>
          </div>
        </div>
      </div>
    </section>
  </div>
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
      // Treemap Chart////////////
      pieChartOptions: {
        chart: {
          type: 'pie',
          width: '100%', // Set the width to 100% to occupy the entire container
          height: '100%', // Set the height to 100% to occupy the entire container
        },
        labels: [
          'ტექნიკური მომსახურებ',
          'დასუფთავება',
          'განათება',
          'ეზოს კეთილმოწყობა',
          'სხვა',
        ], // Add your custom labels
      },
      pieChartSeries: [44, 55, 13, 43, 22],
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
        plotOptions: {
          bar: {
            distributed: true,
            dataLabels: {
              enabled: true,
            },
          },
        },
      },
      barChartSeries: [
        {
          name: 'Series 1',
          data: [1349, 1559, 1604, 1819, 2123, 1923, 2420, 2493, 2470],
          color: (data) => {
            // Define colors for different seasons
            const winterColors = ['#4292c6', '#3282bf', '#2b73b9']
            const springColors = ['#6aa84f', '#61a043', '#58983b']
            const summerColors = ['#ff9933', '#ff8c1a', '#ff8000']
            const fallColors = ['#e67e22', '#e57c1e', '#e4791a']

            const month = data.x // Get the month category

            // Assign colors based on the season
            if (month === 'Dec' || month === 'Jan' || month === 'Feb') {
              return winterColors[data.dataPointIndex % winterColors.length]
            } else if (month === 'Mar' || month === 'Apr' || month === 'May') {
              return springColors[data.dataPointIndex % springColors.length]
            } else if (month === 'Jun' || month === 'Jul' || month === 'Aug') {
              return summerColors[data.dataPointIndex % summerColors.length]
            } else {
              return fallColors[data.dataPointIndex % fallColors.length]
            }
          },
        },
      ],
      treemapChartOptions: {
        chart: {
          type: 'treemap',
        },
        tooltip: {
          style: {
            fontSize: '16px', // Font size for tooltip
          },
        },
        plotOptions: {
          treemap: {
            dataLabels: {
              enabled: true,
              style: {
                fontFamily: 'Arial, sans-serif', // Font family for treemap text
                fontSize: '44px', // Font size for treemap text
                color: 'black', // Text color for treemap text
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
            // {
            //   x: 'გახსენი შლაგბაუმი',
            //   y: 300,
            //   fillColor: '#6bac8e', // Custom color for the second circle
            // },
            {
              x: 'შლაგბაუმი დაკეტილია',
              y: 500,
              fillColor: '#ff1b1e', // Custom color for the third circle
            },
            {
              x: 'შევსებული პატკირება:  20',
              y: 400,
              fillColor: '#FAD5A5', // Custom color for the fourth circle
            },
          ],
        },
      ],
      // Treemap Chart/////////////////////////////////////////////////////////////////////////////////////
      treemapChartOptionsTwo: {
        chart: {
          type: 'treemap',
        },
      },
      treemapChartSeriesTwo: [
        {
          data: [
            {
              x: 'კარი დაკეტილია',
              y: 500,
              fillColor: '#e33b26', // Custom color for the first circle
            },
            // {
            //   x: 'გააღე კარი',
            //   y: 500,
            //   fillColor: '#31c312',
            // },
          ],
        },
      ],
      //   Line Chart///////////////////////////////////////////////////////////////////////////////////////
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
    }
  },
  methods: {
    btnClick() {
      this.$swal.fire({
        icon: 'success',
        position: 'center',
        allowOutsideClick: false,
      })
    },
  },
}
</script>

<style scoped>
h1 {
  font-size: 1.2rem;
  color: rgb(150, 150, 150);
}
.custom-container {
  display: flex;
  width: 100vw;
  height: 100vh;
}
.pie-chart-container {
  width: 100%;
  height: 600px; /* Set the desired height */
}
.custom-col {
  width: 450px;
  height: 330px;
  background-color: rgb(249, 249, 249);
  border-radius: 10px; /* Adjust the radius as needed */
  box-shadow: 0 4px 4px rgba(0, 0, 0, 0.1); /* Adjust the shadow as needed */
  padding: 2px; /* Adjust the padding as needed */
}
section {
  width: 100%;
  height: 100%;
  display: flex;
  flex-wrap: wrap;
  padding: 20px;
  gap: 20px;
}
.row-gap-card-system {
  display: flex;
  align-items: center;

  flex-wrap: wrap;
  justify-content: center;
  flex-direction: column;
  background-color: white;
  border-radius: 10px; /* Adjust the radius as needed */
  box-shadow: 0 4px 4px rgba(0, 0, 0, 0.1); /* Adjust the shadow as needed */
  padding: 2px; /* Adjust the padding as needed */
}
.card-system-wrapper {
  display: flex;
  flex-wrap: wrap;
}
.custom-col-card {
  width: 450px;

  padding: 2px; /* Adjust the padding as needed */
}

.row-gap {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
}
.pie-chart {
  width: 450px; /* Default width */
}
@media (min-width: 600px) {
  .call-lift-btn {
    display: none;
  }
}
@media (max-width: 600px) {
  .pie-chart {
    width: 380px; /* Adjusted width for phone screens */
  }
  .custom-col {
    width: 380px;
    height: 330px;
    margin-right: 20px;
    background-color: rgb(249, 249, 249);
    border-radius: 10px; /* Adjust the radius as needed */
    box-shadow: 0 4px 4px rgba(0, 0, 0, 0.1); /* Adjust the shadow as needed */
    padding: 2px; /* Adjust the padding as needed */
  }
  .row-gap-card-system {
    margin-right: 50px;
    background-color: none;
    background: none;
    box-shadow: none;
  }
}
/* btn */
.chart-container {
  position: relative;
}

.custom-button {
  position: absolute;
  bottom: 0px;
  left: 50%;
  transform: translateX(-50%);
  background-color: #4caf50; /* Green color */
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 16px;
  transition: background-color 0.3s ease;
}
.call-lift-btn {
  width: 95%;
  background-color: #4caf50; /* Green color */
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 16px;
  transition: background-color 0.3s ease;
}
.custom-button:hover {
  background-color: #45a049; /* Darker green color on hover */
}
</style>
