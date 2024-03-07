<template>
  <v-card>
    <v-card-title class="headline">შეიყვანეთ ახალი ნომერი</v-card-title>

    <v-card-text class="pl-3">
      <v-text-field
        v-model="newPhone"
        :rules="phoneRules"
        label="ტელეფონის ნომერი"
        required
      ></v-text-field>
    </v-card-text>

    <v-card-actions>
      <v-btn color="primary" @click="showModal = false">Close</v-btn>
      <v-btn color="green darken-1" @click="updatePhone">Save</v-btn>
      <v-spacer></v-spacer>

    </v-card-actions>
  </v-card>
</template>
<script>

export default {
  name: "UserChangePhone",
  data () {
    return {
      showModal: false,
      newPhone: '',
      phoneRules: [(v) => !!v || 'ტელეფონის ნომერი აუცილებელია'],
    }
  },
  methods: {
    updatePhone() {
      if (this.$refs.form.validate()) {
        this.user.phone = this.newPhone;
        this.showModal = false;
      }
    },
  }
}
</script>
<style scoped>

</style>
