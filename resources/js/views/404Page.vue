<template>
  <v-container>
    <v-row>
      <v-col cols="12">
        <h1>404</h1>
        <p>Sorry, the page you are looking for could not be found.</p>
        <v-btn color="primary" to="/">Go Home</v-btn>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
export default {
  name: 'PageNotFound'
}
</script>

<style scoped>
h1 {
  font-size: 2em;
}
</style>
