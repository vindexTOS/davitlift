<style>
.main-div {
  widows: 100%;
  border-collapse: collapse;
  border: 1px solid #ccc;

  overflow-x: scroll;
}

.v-data-table {
  width: 100%;
}
thead {
  overflow-x: scroll;
}
.v-data-table th,
.v-data-table td {
  padding: 10px;
  text-align: left;
  border-bottom: 1px solid #ccc;
}

.v-data-table th {
  background-color: #f2f2f2;
  font-weight: bold;
}

.paid-chip {
  background-color: #4caf50;
  color: #fff;
  padding: 5px 10px;
  border-radius: 5px;
}

.unpaid-chip {
  background-color: #f44336;
  color: #fff;
  padding: 5px 10px;
  border-radius: 5px;
}

.delete-icon {
  cursor: pointer;
}

.delete-icon:hover {
  color: red;
}
/* paginaiton */
.pagination {
  margin-top: 20px;
  text-align: center;
}

.pagination button {
  border: none;
  background-color: #f2f2f2;
  color: #333;
  padding: 8px 16px;
  cursor: pointer;
}

.pagination button:hover {
  background-color: #ddd;
}

.pagination span {
  border: 1px solid #ccc;
  background-color: #fff;
  color: #333;
  padding: 8px 16px;
  cursor: pointer;
}

.pagination span.active {
  background-color: #007bff;
  color: #fff;
}

.pagination button:disabled,
.pagination span:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

tbody input {
  background-color: #4caf4f8e;
  padding-top: 4px;
  padding-bottom: 4px;
  width: 120px;
}
tr {
  cursor: pointer;
}
.display-none {
  display: none;
}
.loading-circle {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  border: 4px solid #f3f3f3; /* Light grey */
  border-top: 4px solid #3498db; /* Blue */
  border-radius: 50%;
  width: 50px;
  height: 50px;
  animation: spin 1s linear infinite; /* Spin animation */
}

@keyframes spin {
  0% {
    transform: translate(-50%, -50%) rotate(0deg);
  }
  100% {
    transform: translate(-50%, -50%) rotate(360deg);
  }
}

.input-container {
  position: relative;
  margin: 20px;
}
.input-container input {
  width: 300px;
  padding: 10px;
  border: 1px solid #bdbdbd;
  border-radius: 4px;
  outline: none;
  font-size: 16px;
}
.input-container input:focus {
  border-color: #1976d2;
}
.input-container label {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  left: 10px;
  color: #757575;
  transition: all 0.3s ease;
  pointer-events: none;
}
.input-container input:valid + label,
.input-container input:focus + label {
  top: 5px;
  font-size: 12px;
  color: #1976d2;
}
.search-and-user-add-wrapper {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
}
</style>

<template>
  <div class="main-div">
    <div class="search-and-user-add-wrapper">
      <div class="input-container">
        <input v-model="search" type="text" id="search" required />
        <label for="search">Search...</label>
      </div>
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
    </div>
    <table class="v-data-table">
      <thead>
        <tr>
          <th>{{ $t('Name') }}</th>
          <th>{{ $t('Email') }}</th>
          <th>{{ $t('Phone') }}</th>
          <th v-if="!isEditOpen">{{ $t('Balance') }}</th>
          <th v-if="!isEditOpen">{{ $t('Count of cards') }}</th>
          <th v-if="isFixed">{{ $t('has paid') }}</th>
          <th>როლი</th>
          <th v-if="isAdmin">ფრიზ ბალანსი</th>
          <th v-if="isAdmin">ბალანსი</th>
          <th v-if="isAdmin">საბსქრიბშენ თარიღი</th>
          <th v-if="isAdmin">რედაქტირება</th>
          <th v-if="isAdmin || role == 'company' || role == 'manager'">
            ❌წაშლა❌
          </th>
        </tr>
      </thead>
      <tbody>
        <tr
          title="რედაქტირება ✏️"
          v-for="(item, index) in filteredUserData.slice(prevPage, page)"
          :key="item.id"
        >
          <td>
            <p v-if="!boolMirror[index]">
              <RouterLink :to="`/user/${item.id}`">
                {{ item.name }}
              </RouterLink>
            </p>
            <input v-model="item.name" v-if="boolMirror[index]" />
          </td>

          <td>
            <p v-if="!boolMirror[index]">{{ item.email }}</p>
            <input v-model="item.email" v-if="boolMirror[index]" />
          </td>

          <td>
            <p v-if="!boolMirror[index]">{{ item.phone }}</p>
            <input v-model="item.phone" v-if="boolMirror[index]" />
          </td>

          <td v-if="!isEditOpen">{{ item.balance / 100 }} {{ $t('Lari') }}</td>

          <td v-if="!isEditOpen">{{ item.cards_count }}</td>
          <td v-if="isFixed">
            <span
              v-if="new Date(item.subscription) > new Date()"
              class="paid-chip"
            >
              {{ $t('Yes') }}
            </span>
            <span v-else class="unpaid-chip">{{ $t('No') }}</span>
          </td>
          <td>
            <p v-if="!boolMirror[index]">{{ item.role }}</p>
            <input v-model="item.role" v-if="boolMirror[index]" />
          </td>

          <td v-if="isAdmin">
            <p v-if="!boolMirror[index]">{{ item.freezed_balance }}</p>
            <input v-model="item.freezed_balance" v-if="boolMirror[index]" />
          </td>

          <td v-if="isAdmin">
            <p v-if="!boolMirror[index]">{{ item.balance }}</p>
            <input v-model="item.balance" v-if="boolMirror[index]" />
          </td>

          <td v-if="isAdmin">
            <p v-if="!boolMirror[index]">{{ item.subscription }}</p>
            <input v-model="item.subscription" v-if="boolMirror[index]" />
          </td>
          <td v-if="isAdmin">
            <div style="display: flex; flex-direction: row;">
              <p @click="openEdit(index)" v-if="!boolMirror[index]">✏️</p>
              <p @click="openEdit(index)" v-if="!boolMirror[index]">
                რედაქტირება
              </p>
              <p @click="updateUser(item, index)" v-if="boolMirror[index]">
                ✔️
              </p>
              <p v-if="boolMirror[index]" @click="openEdit(index)">❌</p>
            </div>
          </td>
          <td v-if="isAdmin || role == 'company' || role == 'manager'">
            <div class="delete-icon" @click="deleteItem(item.id)">
              <i class="mdi mdi-delete"></i>
              წაშლა
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
  <!-- pagination -->
  <div
    style="
      margin-left: auto;
      display: flex;
      align-items: center;
      width: 100%;
      justify-content: end;
      padding-top: 10px;
    "
  >
    <button
      @click="handlePagination('prev')"
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
    <span style="margin: 0 10px;">Page {{ page / 10 }}</span>
    <button
      @click="handlePagination('next')"
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
  <!-- <div class="pagination">
    <button @click="handlePagination('prev')">
      წინა
    </button>

    <button @click="handlePagination('next')">
      შემდეგი
    </button>
  </div> -->
  <div :class="isEditLoading ? 'loading-circle' : 'display-none'"></div>
</template>

<script>
import { VDataTable } from 'vuetify/labs/components'

export default {
  components: { VDataTable },
  props: ['serverItems', 'isFixed', 'deviceId'],
  data: () => ({
    isAdmin: false,
    isEditOpen: false,
    isEditLoading: false,
    role: '',
    page: 10,
    prevPage: 0,
    select: null,
    dialogExisted: false,
    items: [],
    expanded: [],
    dialog: false,
    itemsPerPage: 1,
    boolMirror: new Array(10).fill(false),
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
    userData: {},
  }),

  async created() {
    this.chackAdminEmail()
    this.userData = this.serverItems
  },
  computed: {
    filteredUserData() {
      if (this.search === '') {
        return this.serverItems.userData.slice() // Return original data if search term is empty
      } else {
        return this.serverItems.userData.filter((val) => {
          return val.name.toLowerCase().includes(this.search.toLowerCase())
        })
      }
    },
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
    chackAdminEmail() {
      const token = localStorage.getItem('vuex')
      let email = JSON.parse(token).auth.user.email
      this.isAdmin = email === 'info@eideas.io'
      this.role = JSON.parse(token).auth.user.role
    },
    handleInputChange() {
      if (this.search === '') {
        this.userData.userData = this.serverItems.userData.slice() // Copy original data
      } else {
        this.userData.userData = this.serverItems.userData.filter((val) => {
          return val.name.toLowerCase().includes(this.search.toLowerCase())
        })
      }
    },
    updateUser(obj, index) {
      // /update/user/subscription
      obj.freezed_balance = Number(obj.freezed_balance)
      obj.balance = Number(obj.balance)
      console.log(obj)
      this.isEditLoading = true
      axios
        .put(`/api/update/user/subscription`, obj)
        .then((res) => {
          this.openEdit(index)
          this.isEditLoading = false

          this.dialogExisted = false
          console.log(res)

          if (!this.isEditOpen) {
            this.$emit('loadDevice')
          }
        })
        .catch((err) => {
          this.isEditLoading = false

          console.log(err)
          this.$swal.fire({
            icon: 'error',

            position: 'center',
            allowOutsideClick: true,
          })
        })
    },
    test(test) {
      console.log(test)
    },
    handlePagination(type) {
      this.boolMirror = new Array(10).fill(false)

      if (type === 'next') {
        if (this.page < this.serverItems.pagination * 10) {
          this.page += 10
          this.prevPage += 10
        } else {
          this.page = 10
          this.prevPage = 0
        }
      } else if (type === 'prev') {
        if (this.page >= 0) {
          this.page -= 10
          this.prevPage -= 10
        }
        if (this.page === 0) {
          this.page = 10
          this.prevPage = 0
        }
      }
    },
    openEdit(index) {
      let newMirror = [...this.boolMirror]
      newMirror[index] = !newMirror[index]

      this.boolMirror = newMirror

      if (this.boolMirror.includes(true)) {
        this.isEditOpen = true
      } else {
        this.isEditOpen = false
      }
    },
    saveChanges(index) {
      this.$set(this.serverItems.userData, index, { ...this.editedData[index] })
      this.$set(this.boolMirror, index, false)
    },
    cancelEdit(index) {
      this.$set(this.boolMirror, index, false)
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
