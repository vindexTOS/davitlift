<template>
  <div>
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
          <v-toolbar-title class="text-capitalize">
            ჩარიცხვების ცხრილი
          </v-toolbar-title>
          <v-spacer></v-spacer>
        </v-toolbar>
      </template>
      <template v-slot:item.updated_at="{ item }">
        {{ formatMyDate(item.raw.updated_at) }}
      </template>
      <template v-slot:item.amountTax="{ item }">
        {{
          item.raw.type == 'TBC' || "LB" || "BO"
            ? item.raw.amount  
            : getUntaxedAmount(item.raw.amount)
        }}
        ლარი
      </template>
      <template v-slot:item.type="{ item }">
        {{ getItemType(item.raw.type) }}
      </template>
    </v-data-table>
  </div>
</template>
<script>
import { VDataTable } from 'vuetify/labs/components'

export default {
  components: { VDataTable },
  props: ['serverItems'],
  data: () => ({
    select: null,
    dialogExisted: false,
    items: [],
    expanded: [],
    dialog: false,
    dialogDelete: false,
    itemsPerPage: 5,

    search: '',
    loading: true,
    totalItems: 0,
    desserts: [],
    editedIndex: -1,
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
    headers() {
      return [
        {
          title: 'თარიღი',
          align: 'updated_at',
          sortable: true,
          key: 'updated_at',
        },
        {
          title: 'ჩარიცხული თანხა',
          key: 'amountTax',
        },
        {
          title: 'ჩარიცხვის ტიპი',
          key: 'type',
        },
      ]
    },
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
    deleteItem() {},
    formatMyDate(date) {
      const d = new Date(date)
      const month = (d.getMonth() + 1).toString().padStart(2, '0') // Months are zero-based
      const day = d.getDate().toString().padStart(2, '0')
      const hours = d.getHours().toString().padStart(2, '0')
      const minutes = d.getMinutes().toString().padStart(2, '0')

      return `${day}/${month}/${d.getFullYear()} ${hours}:${minutes}`
    },
    getUntaxedAmount(amount) {
      const tax = amount * 0.02

      return (amount - tax).toFixed(2)
    },

    getItemType(type) {
      let res = ''
      if(type == "LB"){
        res = "Liberty ჩასარიცხი აპარატი"
      }else if( type == "TBC"){
        res = 'TBC ჩასარიცხი აპარატი'
      } else if (type == "BO"){
        res = "BO ჩასარიცხი აპარატი"
      }
      else{ 
        res = type
      }
      return res
    },
  },
}
</script>
