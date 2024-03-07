<template>
  <v-container   class="fill-height">
    <v-row  style="height: 100vh" align="center" justify="center">
      <v-col cols="12" sm="8" >
        <v-card>
          <v-card-title class="text-center">
            <h2>პაროლის განახლება</h2>
          </v-card-title>
          <v-card-text>
            <v-form @submit.prevent="login">
              <v-text-field
                v-model="email"
                label="ელ.ფოსტა"
                :hide-details="true"
                required
                type="email"
              ></v-text-field>
              <v-btn color="primary" class="mt-4" :block="true" type="submit">გაანახლეთ პაროლი</v-btn>
            </v-form>
            <v-row class="mt-3">
              <v-col cols="12" class="text-center">
                <router-link to="/forgot-password">დაგავიწყდათ პაროლი?</router-link>
              </v-col>
              <v-col cols="12" class="text-center">
                <router-link to="/login">შესვლა</router-link>
              </v-col>
            </v-row>

          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>
<script>
import axios from 'axios';

axios.defaults.headers.common['Access-Control-Allow-Origin'] = '*';
axios.defaults.headers.common['Access-Control-Allow-Methods'] = 'POST, GET, PUT, DELETE, OPTIONS';

export default {
  data() {
    return {
      email: '',
      password: '',
      phone: '',
      name: '',
      rpassword: '',
    };
  },
  methods: {
    async login() {
      const response = await axios.get('/api/login', {
        params : {
          email: this.email,
          password: this.password,
        }
      }, );
      // Save the token in local storage or in Vuex state
      localStorage.setItem('auth_token', response.data.token);
      localStorage.setItem('email',  this.email);

      this.$router.push('/');
    },
  },
};
</script>
<style scoped>

</style>
