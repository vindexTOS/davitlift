<template>
  <div class="custom-container">
    <div class="form-container">
      <input
        type="text"
        placeholder="Device Name"
        v-model="deviceId"
        class="input-field"
      />
      <input
        type="text"
        placeholder="Message"
        v-model="message"
        class="input-field"
      />
      <button class="submit-button" @click="sendTestMessage">Submit</button>
    </div>
  </div>
</template>

<script>
import VueApexCharts from 'vue3-apexcharts'
import axios from 'axios'

export default {
  name: 'PageTest',
  components: {
    apexchart: VueApexCharts,
  },
  data() {
    return {
      deviceId: '', // Binds to Device Name input
      message: '', // Binds to Message input
    }
  },
  methods: {
    async sendTestMessage() {
      try {
        const response = await axios.get(
          `api/testDevice/${this.deviceId}/${this.message}`
        )
        console.log('Message sent successfully:', response.data)
      } catch (error) {
        console.error('Error sending message:', error)
      }
    },
  },
}
</script>

<style>
.custom-container {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
  background-color: #f4f7f9;
}

.form-container {
  display: flex;
  gap: 15px;
  align-items: center;
  background-color: #ffffff;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
}

.input-field {
  padding: 10px 15px;
  font-size: 16px;
  border: 1px solid #d1d9e6;
  border-radius: 5px;
  width: 200px;
  transition: border-color 0.3s ease;
}

.input-field:focus {
  outline: none;
  border-color: #4a90e2;
}

.submit-button {
  padding: 10px 20px;
  font-size: 16px;
  color: #fff;
  background-color: #4a90e2;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.submit-button:hover {
  background-color: #357ab8;
}
</style>
