<template>
  <div>
    <v-data-table
      v-model:expanded="expanded"
      :headers="headers"
      :items-length="totalItems"
      :items="serverItems"
      :search="search"
      class="elevation-1"
      item-value="name"
    >
      <template v-slot:top>
        <v-toolbar>
          <v-toolbar-title class="text-capitalize">
            {{ $t('users') }}
          </v-toolbar-title>
          <v-spacer v-if="$vuetify.display.smAndUp"></v-spacer>
          <v-text-field
            v-if="$vuetify.display.smAndUp"
            v-model="search"
            :label="$t('Search')"
            single-line
            hide-details
          ></v-text-field>

          <v-menu>
            <template v-slot:activator="{ props }">
              <v-icon
                class="ma-3"
                v-if="$store.state.auth.user.lvl >= 2"
                size="large"
                icon="mdi-dots-vertical"
                v-bind="props"
              ></v-icon>
            </template>
            <v-card width="250" class="pa-0 ma-0">
              <v-dialog v-model="dialogExisted" max-width="500px">
                <template v-slot:activator="{ props }">
                  <v-btn
                    style="width: 100%;"
                    dark
                    class="mb-2"
                    v-bind="props"
                    v-if="$store.state.auth.user.lvl >= 2"
                  >
                    {{ $t('Add user') }}
                  </v-btn>
                </template>
                <v-card>
                  <v-card-title>
                    <span class="text-h5">
                      {{ $t('Add user') }}
                    </span>
                  </v-card-title>

                  <v-card-text>
                    <v-container>
                      <v-row>
                        <v-col cols="12">
                          <v-text-field
                            v-model="existUser"
                            :label="$t('Email or phone')"
                            class="text-capitalize"
                            required
                          ></v-text-field>
                        </v-col>
                      </v-row>
                    </v-container>
                  </v-card-text>

                  <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn
                      color="blue-darken-1"
                      variant="text"
                      @click="dialogExisted = false"
                    >
                      {{ $t('Close') }}
                    </v-btn>
                    <v-btn color="blue-darken-1" variant="text" @click="save">
                      {{ $t('Save') }}
                    </v-btn>
                  </v-card-actions>
                </v-card>
              </v-dialog>
            </v-card>
          </v-menu>
        </v-toolbar>
        <v-text-field
          v-if="$vuetify.display.xs"
          v-model="search"
          :label="$t('Search')"
          single-line
          hide-details
        ></v-text-field>
      </template>
      <template v-slot:item.paid="{ item }">
        <!-- <h1 @click="test(item.raw)">TEST</h1> -->
        <v-chip
          v-if="new Date(item.raw.pivot.subscription) > new Date()"
          class="ma-2"
          color="green"
          text-color="white"
        >
          {{ $t('Yes') }}
        </v-chip>
        <v-chip v-else class="ma-2" color="red" text-color="white">
          {{ $t('No') }}
        </v-chip>
      </template>
      <template v-slot:item.name="{ item }">
        <RouterLink
          v-if="$store.state.auth.user.lvl >= 3"
          :to="`/user/${item.raw.id}`"
        >
          {{ item.raw.name }}
        </RouterLink>
        <span v-else>{{ item.raw.name }}</span>
      </template>
      <template v-slot:item.balance="{ item }">
        {{ item.raw.balance / 100 }}{{ $t('Lari') }}
      </template>
      <template v-slot:item.actions="{ item }">
        <v-icon
          v-if="$store.state.auth.user.lvl >= 2"
          size="small"
          color="red"
          @click="deleteItem(item.raw.id)"
        >
          mdi-delete
        </v-icon>
      </template>
    </v-data-table>
  </div>
</template>
<script>
import { VDataTable } from 'vuetify/labs/components'

export default {
  components: { VDataTable },
  props: ['serverItems', 'isFixed', 'deviceId'],
  data: () => ({
    select: null,
    dialogExisted: false,
    items: [],
    expanded: [],
    dialog: false,
    itemsPerPage: 1,

    search: '',
    loading: true,
    totalItems: 0,
    desserts: [],
    editedIndex: -1,
    existUser: '',
    editedItem: {
      name: '',
      phone: 0,
      email: 0,
      password: 0,
      companies: 0,
      comment: '',
    },
    defaultItem: {
      name: '',
      calories: 0,
      fat: 0,
      carbs: 0,
      protein: 0,
    },
    action: {},
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? 'New user' : 'Edit user'
    },
    headers() {
      let header = [
        {
          title: this.$t('Name'),
          align: 'start',
          sortable: false,
          key: 'name',
        },
        { title: this.$t('Email'), key: 'email', align: 'start' },
        { title: this.$t('Phone'), key: 'phone', align: 'start' },
        { title: this.$t('Balance'), key: 'balance', align: 'start' },
        {
          title: this.$t('Count of cards'),
          key: 'cards_count',
          align: 'start',
        },
        {
          title: 'როლი',
          key: 'role',
          align: 'start',
        },
      ]
      if (this.isFixed) {
        header.push({ title: this.$t('has paid'), key: 'paid', align: 'start' })
      }
      header.push({ title: '', key: 'actions' })
      return header
    },
  },

  methods: {
    test(test) {
      console.log(test)
    },
    deleteItem(id) {
      this.$swal
        .fire({
          title: 'ნამდვილად გსურთ მომხმარებლის ამოშლა ?',
          showCancelButton: false,
          showDenyButton: true,
          confirmButtonText: this.$t('Yes'),
          denyButtonText: this.$t('Close'),
        })
        .then((result) => {
          /* Read more about isConfirmed, isDenied below */
          if (result.isConfirmed) {
            axios
              .delete(`/api/userRemoveDevice/${id}/${this.deviceId}`)
              .then(() => {
                this.$swal.fire({
                  icon: 'success',
                  position: 'center',
                  allowOutsideClick: false,
                })
                this.$emit('loadDevice')
                this.dialogExisted = false
              })
          } else if (result.isDenied) {
          }
        })
    },
    save() {
      axios
        .get(`/api/userToDevice/${this.existUser}/${this.deviceId}`)
        .then(() => {
          this.$swal.fire({
            icon: 'success',
            position: 'center',
            allowOutsideClick: false,
          })
          this.$emit('loadDevice')
          this.dialogExisted = false
        })
    },
  },
}
</script>
