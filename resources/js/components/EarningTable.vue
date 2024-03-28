<template>
  <div>
    <table style="border-collapse: collapse; width: 100%;">
      <!-- Table headers -->
      <thead>
        <tr style="background-color: #f2f2f2;">
          <th style="padding: 8px; border: 1px solid #ddd;">
            {{ $t('Year') }}
          </th>
          <th style="padding: 8px; border: 1px solid #ddd;">{{ $t('თვე') }}</th>
          <th style="padding: 8px; border: 1px solid #ddd;">
            {{ $t('Amount') }}
          </th>
          <th style="padding: 8px; border: 1px solid #ddd;">ტარიფი</th>
          <th style="padding: 8px; border: 1px solid #ddd;">ქეშბექი</th>
          <th style="padding: 8px; border: 1px solid #ddd;">
            რედაქტ
          </th>
        </tr>
      </thead>
      <!-- Table body -->
      <tbody>
        <tr
          v-if="paginatedItems.length > 0"
          v-for="(item, index) in paginatedItems"
          :key="item.id"
          style="background-color: #ffffff;"
        >
          <td style="padding: 8px; border: 1px solid #ddd;">{{ item.year }}</td>
          <td style="padding: 8px; border: 1px solid #ddd;">
            {{ item.month }}
          </td>
          <td style="padding: 8px; border: 1px solid #ddd;">
            {{ item.earnings / 100 }}₾
          </td>
          <td style="padding: 8px; border: 1px solid #ddd;">
            <p v-if="!boolMapper[index]">{{ item.deviceTariff }}</p>
            <input
              style="width: 50px; background-color: greenyellow;"
              v-if="boolMapper[index]"
              :value="item.deviceTariff"
            />
          </td>
          <td style="padding: 8px; border: 1px solid #ddd;">
            <p v-if="!boolMapper[index]">{{ item.cashback }}</p>
            <input
              style="width: 50px; background-color: greenyellow;"
              v-if="boolMapper[index]"
              :value="item.cashback"
            />
          </td>
          <td v-if="isFixed" style="padding: 8px; border: 1px solid #ddd;"></td>
          <td
            style="
              padding: 8px;
              border: 1px solid #ddd;
              display: flex;
              justify-content: center;
              cursor: pointer;
            "
          >
            <p @click="toogleEedit(index)" v-if="boolMapper[index]">
              ❌
            </p>
            <p @click="toogleEedit(index)" v-if="boolMapper[index]">
              ✔️
            </p>
            <p @click="updateEarning(item)" v-if="!boolMapper[index]">✎</p>
          </td>
        </tr>
      </tbody>
    </table>
    <!-- Pagination controls -->
    <div style="margin-top: 10px; display: flex; align-items: center;">
      <span style="margin-right: 10px;">Items per page:</span>
      <select
        v-model="itemsPerPage"
        @change="updatePage"
        style="padding: 5px; border-radius: 5px;"
      >
        <option value="5">5</option>
        <option value="10">10</option>
        <option value="20">20</option>
      </select>
      <div style="margin-left: auto; display: flex; align-items: center;">
        <button
          @click="prevPage"
          :disabled="currentPage === 1"
          style="
            padding: 8px 12px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
          "
        >
          Prev
        </button>
        <span style="margin: 0 10px;">
          Page {{ currentPage }} of {{ totalPages }}
        </span>
        <button
          @click="nextPage"
          :disabled="currentPage === totalPages"
          style="
            padding: 8px 12px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
          "
        >
          Next
        </button>
      </div>
    </div>
  </div>
</template>
<script>
import { VDataTable } from 'vuetify/labs/components'

export default {
  components: { VDataTable },
  props: ['serverItems', 'isFixed'],
  data: () => ({
    select: null,
    dialogExisted: false,
    items: [],
    expanded: [],
    boolMapper: [],
    dialog: false,
    dialogDelete: false,
    itemsPerPage: 5,
    currentPage: 1,
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
    formTitle() {
      return this.editedIndex === -1 ? 'New user' : 'Edit user'
    },
    headers() {
      let header = [
        {
          title: this.$t('Year'),
          align: 'start',
          sortable: false,
          key: 'year',
        },
        { title: this.$t('თვე'), key: 'month', align: 'start' },
        { title: this.$t('Amount'), key: 'earnings', align: 'start' },
        { title: 'ტარიფი', key: 'deviceTariff', align: 'start' },
        { title: 'ქეშბექი', key: 'cashback', align: 'start' },
      ]
      if (this.isFixed) {
        header.push({ title: this.$t('has paid'), key: 'paid', align: 'start' })
      }
      header.push({ title: '', key: 'action' })
      this.boolMapper = new Array(this.serverItems.length).fill(false)
      console.log(this.serverItems)

      return header
    },
    paginatedItems() {
      const startIndex = (this.currentPage - 1) * this.itemsPerPage
      return this.serverItems.slice(startIndex, startIndex + this.itemsPerPage)
    },
    totalPages() {
      return Math.ceil(this.serverItems.length / this.itemsPerPage)
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
    updateEarning(id) {
      console.log(id)
    },
    toogleEedit(index) {
      let newArr = [...this.boolMapper]
      newArr[index] = !newArr[index]
      this.boolMapper = newArr
    },
    prevPage() {
      if (this.currentPage > 1) {
        this.currentPage -= 1
      }
    },
    nextPage() {
      if (this.currentPage <= this.totalPages) {
        this.currentPage += 1
      }
    },
    updatePage() {
      this.currentPage = 1
    },
  },
}
</script>
