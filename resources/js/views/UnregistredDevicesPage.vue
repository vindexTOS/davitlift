<template>
  <div>
      <v-card>
          <v-card-text>
              <v-text-field
                  v-model="search"
                  :label="$t('Search')"
                  single-line
                  hide-details
              ></v-text-field>
          </v-card-text>
      </v-card>

    <v-data-table
      :headers="headers"
      :items-length="totalItems"
      :items="serverItems"
      :search="search"
      class="elevation-1"
      item-value="name"
    >
      <template v-slot:top>
        <v-toolbar>
          <v-toolbar-title>{{$t('Unregistered lifts')}}</v-toolbar-title>
        </v-toolbar>
      </template>
        <template v-slot:item.created_at="{item}">
            {{new Date(item.raw.created_at)}}
        </template>
        <template v-slot:item.actions="{item}">
          <v-icon
              size="small"
              color="red"
              @click="deleteItem(item.raw)"
          >
            mdi-delete
          </v-icon>
        </template>
    </v-data-table>

  </div>
</template>
<script>
import {VDataTable} from "vuetify/labs/components";


export default {
  components: {VDataTable},
  data: () => ({
    items: [],
    expanded: [],
    itemsPerPage: 5,

    search: '',
    serverItems: [],
    loading: true,
    totalItems: 0,
  }),
    computed : {
      headers() {
        return [
          {title: this.$t('UUID'), key: 'dev_id', align: 'center'},
          {title: this.$t('Software version'), key: 'soft_version', align: 'center'},
          {title: this.$t('Hardware version'), key: 'hardware_version', align: 'center'},
          {title: this.$t("Date"), key: 'created_at',},
          {title: '', key: 'actions'},
        ]
      }
    },
    created() {
        this.loadItems();
    },

    watch: {
    dialog(val) {
      val || this.close()
    },
    dialogDelete(val) {
      val || this.closeDelete()
    },
  },

  methods: {
    loadItems() {
        axios.get('api/unregistered_device').then(({data}) => {
            this.serverItems = data
        })
    },

    deleteItem(item) {
        this.$swal.fire({
            title: "Do you really want to delete?",
            showCancelButton: false,
            showDenyButton: true,
            confirmButtonText: this.$t('Yes'),
            denyButtonText: this.$t('Close'),
        }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                this.$swal.fire({
                    icon: 'success',
                    position:'center',
                    allowOutsideClick: false,
                })
                this.loadItems();
            } else if (result.isDenied) {
            }
        })

    },

  },
}
</script>
<style scoped>

</style>
