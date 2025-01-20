<template>
  <v-container class="fill-height">
    <v-row style="height: 100vh" align="center" justify="center">
      <v-col cols="12" sm="8">
        <v-card>
          <v-card-title class="text-center">
            <h2>შესვლა</h2>
          </v-card-title>
          <v-card-text>
            <v-form @submit.prevent="login">
              <v-text-field v-if="!isIdNumber" v-model="email" label="ელ.ფოსტა" :hide-details="true" required
                type="email"></v-text-field>
              <v-text-field v-if="isIdNumber" v-model="id_number" label="Id ნომერი" :hide-details="true" required
                type="text"></v-text-field>
              <v-text-field v-model="password" label="პაროლი" required :hide-details="true"
                type="password"></v-text-field>
              <v-btn color="primary" class="mt-4" :block="true" type="submit">შესვლა</v-btn>
            </v-form>
            <v-row class="mt-3">
              <v-col cols="12" class="text-center">
                <v-btn @click="switchIdNumber()">{{ isIdNumber ? "შესვლა Email-ით" : "შესვლა ID ბარათით" }}</v-btn>
              </v-col>
              <v-col cols="12" class="text-center">
                <router-link to="/registration">რეგისტრაცია</router-link>
              </v-col>
            </v-row>

          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>
<script>
import { mapActions } from 'vuex'

export default {
  data() {
    return {
      email: '',
      password: '',
      id_number: "",
      isIdNumber: false

    };
  },
  methods: {
    switchIdNumber() {
      this.isIdNumber = !this.isIdNumber
    },
    ...mapActions({
      signIn: 'auth/login'
    }),
    async login() {
      this.processing = true
      if (!this.isIdNumber) {
        await axios.post('/api/login', { email: this.email, password: this.password }).then(({ data }) => {

          // Store JWT token, you can use VueX, localStorage or other methods.
          localStorage.setItem('token', data.token);

          // Set default auth header for all requests
          axios.defaults.headers.common['Authorization'] = 'Bearer ' + data.token;
          console.log(data.token)
          // Redirect to dashboard or other protected route
          this.signIn()
        }).catch(({ response }) => {
          if (response.status === 422) {
            this.validationErrors = response.data.errors
          } else {
            this.validationErrors = {}
            alert(response.data.message)
          }
        }).finally(() => {
          this.processing = false
        })
      } else {
        await axios.post('/api/login-with-id', { id_number: this.id_number, password: this.password }).then(({ data }) => {

          // Store JWT token, you can use VueX, localStorage or other methods.
          localStorage.setItem('token', data.token);

          // Set default auth header for all requests
          axios.defaults.headers.common['Authorization'] = 'Bearer ' + data.token;
          console.log(data.token)
          // Redirect to dashboard or other protected route
          this.signIn()
        }).catch(({ response }) => {
          if (response.status === 422) {
            this.validationErrors = response.data.errors
          } else {
            this.validationErrors = {}
            alert(response.data.message)
          }
        }).finally(() => {
          this.processing = false
        })
      }


    },
  }
};
</script>
<style scoped></style>
