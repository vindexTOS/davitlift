<template>
  <div>
    <v-card>
      <v-card-title class="d-flex align-center justify-space-between">
        <h2>{{ $t('Lifts') }}</h2>
        <v-menu>
          <template v-slot:activator="{ props }">
            <v-icon size="small" icon="mdi-dots-vertical" v-bind="props"></v-icon>
          </template>
          <v-card width="250" class="pa-0 ma-0">
            <v-btn style="width: 100%;" @click="dialog = true">
              {{ $t('Add lift') }}
            </v-btn>
          </v-card>
        </v-menu>
      </v-card-title>
      <v-card-text>
        <v-text-field v-model="search" :label="$t('Search')" single-line hide-details></v-text-field>
        <v-row>
          <v-checkbox :label="$t('Active')" style="min-width: fit-content;" v-model="isActive"></v-checkbox>

          <v-checkbox :label="$t('Inactive')" style="min-width: fit-content;" v-model="notActive"></v-checkbox>
          <v-checkbox :label="$t('With problems')" style="min-width: fit-content;" v-model="hasError"></v-checkbox>
          <v-checkbox :label="$t('Deleted')" style="min-width: fit-content;" v-model="deleted"></v-checkbox>
        </v-row>
      </v-card-text>
      <v-dialog v-model="dialog" max-width="500px">
        <v-card>
          <v-card-title>
            <span class="text-h5">{{ $t('Add lift') }}</span>
          </v-card-title>

          <v-card-text>
            <v-container>
              <v-row>
                <v-col cols="12">
                  <v-autocomplete v-model="editedItem.company_id" :item-title="'company_name'" :item-value="'id'"
                    label="კომპანია" :items="items"></v-autocomplete>
                </v-col>

                <v-col v-if="isAdmin" cols="12">
                  <v-autocomplete v-model="editedItem.dev_id" :label="$t('UUID')"
                    :items="unregistered_device"></v-autocomplete>
                </v-col>
                <v-col v-if="dialogBussines" cols="12">
                  <v-text-field v-model="editedItem.name" class="text-capitalize" :label="$t('Name')"
                    required></v-text-field>
                </v-col>

                <v-col cols="12">
                  <v-text-field v-model.number="editedItem.pay_day" class="text-capitalize"
                    :label="$t('Payment number (from 1 to -28)')" type="number" required></v-text-field>
                </v-col>
                <v-col v-if="dialogBussines" cols="12">
                  <v-radio-group :label="$t('Mode of payment')" v-model="editedItem.op_mode" :inline="true">
                    <v-radio :label="$t('Tariff')" :value="1"></v-radio>
                    <v-radio :label="$t('Fixed')" :value="0"></v-radio>
                  </v-radio-group>
                </v-col>
                <v-col v-if="dialogBussines" cols="12">
                  <v-text-field v-model.number="editedItem.tariff_amount" class="text-capitalize"
                    :label="$t('Charge (in Tetri)')" required></v-text-field>
                </v-col>

                <v-col v-if="dialogBussines" cols="12">
                  <v-text-field v-model="editedItem.admin_email" class="text-capitalize" :label="$t('Chairman mail')"
                    required></v-text-field>
                </v-col>

                <v-col v-if="dialogBussines" cols="12">
                  <v-text-field v-model="editedItem.sim_card_number" class="text-capitalize"
                    :label="$t('sim_card_number')" required></v-text-field>
                </v-col>

                <v-col v-if="dialogBussines" cols="11">
                  <span>
                    {{ $t('Number of cards per user') }}: {{ editedItem.limit }}
                  </span>
                  <v-slider v-model="editedItem.limit" :min="1" :max="100" :step="1"></v-slider>
                </v-col>

                <v-col v-if="dialogBussines" cols="12">
                  <v-textarea v-model="editedItem.comment" class="text-capitalize" :label="$t('Comment')"
                    required></v-textarea>
                </v-col>
              </v-row>
            </v-container>
          </v-card-text>

          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="blue-darken-1" variant="text" @click="close">
              {{ $t('Close') }}
            </v-btn>
            <v-btn color="blue-darken-1" variant="text" @click="save">
              {{ $t('Save') }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-card>
    <v-card>
      <v-container>
        <v-row v-if="serverItemsFilter">
          <v-col style="min-width: fit-content;" v-for="(item, index) in serverItemsFilter" cols="12" sm="6" md="4"
            lg="3" :key="item.id">
            <v-card>
              <template v-slot:title>
                <div class="d-flex justify-space-between">
                  <span>
                    <v-icon v-if="isAdmin && item.errors.length" size="xs" color="red">
                      mdi-alert
                    </v-icon>
                    {{ item.name }}

                    <SignalIcon :signal="Math.ceil(item.signal / 20)" />
                  </span>
                </div>
              </template>

              <template v-slot:subtitle>
                <div class="d-flex justify-space-between">
                  <v-chip v-if="
                    new Date(item.lastBeat).getTime() > new Date().getTime()
                  " class="" color="green" text-color="white">
                    {{ $t('Active') }}
                  </v-chip>
                  <v-chip v-else class="" color="red" text-color="white">
                    {{ $t('Inactive') }}
                  </v-chip>
                </div>
                <b>{{ $t('Number of cards per user') }}: {{ item.limit }}</b>
                <hr />
                <b>{{ item.dev_id }}</b>
                <hr />
                <b>
                  {{
                    item.network == 1
                      ? 'Ethernet'
                      : item.network == 2
                        ? 'Cellular'
                        : 'Wifi'
                  }}
                </b>
                <hr />
                <b>
                  {{
                    item.op_mode == 0 ? 'გადახდის რიცხვი: ' + item.pay_day : ''
                  }}
                </b>
                <!-- <hr />

                <b>
                  {{
                    item.deviceTariffAmount
                      ? 'ტარიფი :' + item.deviceTariffAmount + ' ლარი'
                      : ''
                  }}
                </b> -->
                <hr />
                <div>
                  <b>
                    ბარათის ტარიფი(თეთრებში)
                    {{ item.fixed_card_amount }} ₾
                  </b>
                  <v-btn style="width: 30px; height: 30px; padding:5px"
                    @click="openFixedCardDialog(item.fixed_card_amount, item.id)" icon>
                    <v-icon size="12px" color="gray">mdi-pencil</v-icon>
                  </v-btn>
                </div>
                <hr />
                <div>
                  <b>
                    ტელეფონის ნომრის ტარიფი(თეთრებში)
                    {{ item.fixed_phone_amount }} ₾
                  </b>
                  <v-btn style="width: 30px; height: 30px; padding:5px"
                    @click="openDevicePhoneTarrifDialog(item.fixed_phone_amount, item.id)" icon>
                    <v-icon size="12px" color="gray">mdi-pencil</v-icon>
                  </v-btn>
                </div>
                <!--  -->


                <!-- Fixed Card Amount Dialog -->
                <v-dialog v-model="dialogFixedCard" persistent :style="{ background: 'transparent' }"
                  class="transparent-dialog">
                  <v-card style="background-color: rgba(255, 255, 255, 0.8);">
                    <v-card-title> Edit Fixed Card Amount </v-card-title>
                    <v-card-text>
                      <v-text-field v-model="editedFixedCardAmount" label="ბარათის ტარიფი" type="number"
                        required></v-text-field>
                    </v-card-text>
                    <v-card-actions>
                      <v-spacer></v-spacer>
                      <v-btn @click="dialogFixedCard = false">{{ $t('Close') }}</v-btn>
                      <v-btn @click="saveFixedCardAmount()">{{ $t('Save') }}</v-btn>
                    </v-card-actions>
                  </v-card>
                </v-dialog>
                <!-- Fixed Phone Amoutn Dialog  -->
                <v-dialog v-model="dialogFixedPhoneNumber" persistent :style="{ background: 'transparent' }"
                  class="transparent-dialog">
                  <v-card style="background-color: rgba(255, 255, 255, 0.8);">
                    <v-card-title> Edit Fixed Phone Amount </v-card-title>
                    <v-card-text>
                      <v-text-field v-model="editedPhoneNumberTarrif" label="ტელეფონის ტარიფი ტარიფი" type="number"
                        required></v-text-field>
                    </v-card-text>

                    <v-card-actions>
                      <v-spacer></v-spacer>
                      <v-btn @click="dialogFixedPhoneNumber = false">{{ $t('Close') }}</v-btn>
                      <v-btn @click="savePhoneNumberAmount()">{{ $t('Save') }}</v-btn>
                    </v-card-actions>
                  </v-card>
                </v-dialog>
                <!--  -->
                <div v-if="isAdmin">
                  <div>
                    <b>
                      მომსახურების მინიმალური საფასური
                      {{ item.deviceTariffAmount }} ₾
                    </b>
                    <v-btn style="width: 30px; height: 30px; padding:5px"
                      @click="openDeviceTariffDialog(item.deviceTariffAmount, item.id)" icon>
                      <v-icon size="12px" color="gray">mdi-pencil</v-icon>
                    </v-btn>




                    <!--  device tarrif dialog-->
                    <v-dialog v-model="dialogFixedDeviceTarrif">
                      <v-card>
                        <v-card-title> Edit Device Tariff Amount </v-card-title>
                        <v-card-text>
                          <v-text-field v-model="editedDeviceTariffAmount" label="მომსახურეობის მინიმალური ტარიფი"
                            type="number" required></v-text-field>
                        </v-card-text>
                        <v-card-actions>
                          <v-spacer></v-spacer>
                          <v-btn @click="dialogFixedDeviceTarrif = false">{{ $t('Close') }}</v-btn>
                          <v-btn @click="changeSingleLiftAmount(item.id, index)">{{ $t('Save') }}</v-btn>
                        </v-card-actions>
                      </v-card>
                    </v-dialog>
                    <!--  -->


 <!--მომხამრებელბის დამატება ექსელი -->
 <v-dialog v-model="isAddingUserExcel" max-width="600px">
    <v-card>
      <v-card-title>მომხმარებლებლის ატვირთვა</v-card-title>
      <v-card-text>
        <div class="file-input-container">
          <!-- Drop Zone -->
          <div
            class="drop-zone"
            @drop.prevent="handleDrop"
            @dragover.prevent
          >
            <p v-if="!excelFile">ჩააგდე ფაილი აქ</p>
            <p v-else>ატვირთული ფაილი: {{ excelFile.name }}</p>
          </div>

          <!-- Button to open file dialog -->
          <div class="file-input-wrapper">
            <label for="fileInput" class="file-input-label">
              აირჩიე ფაილი
            </label>
            <input
              id="fileInput"
              type="file"
              accept=".xlsx, .xls"
              @change="handleFileInput"
            />
          </div>

          <!-- Remove Button -->
          <v-btn
            v-if="excelFile"
            @click="removeUploadedFile"
            class="my-styled-btn"
            color="red"
          >
            ფაილის წაშლა
          </v-btn>
        </div>
      </v-card-text>

      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn @click="closeExelDialog">დახურვა</v-btn>
        <v-btn :disabled="!excelFile" @click="uploadExel">ატვირთვა</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

 <!--მომხამრებელბის დამატება ექსელი -->
                  </div>
                  <!-- ლიფტის ტარიფის შეცვლა -->

                  <div v-if="singleLiftMapBool[index]" style="
                      display: flex;
                      align-items: center;
                      width: 200px;
                      margin-top: 10px;
                    ">
                    <v-text-field v-model="liftTariffValue" @input="handleInput" :value="liftTariffValue" type="number"
                      outlined dense :style="{
                        width: '100px',
                        'margin-top': '5px',
                      }"></v-text-field>
                    <v-icon @click="changeSingleLiftAmount(item.id, index)" size="40px" color="green">
                      mdi-check-circle
                    </v-icon>
                    <v-icon @click="openSingleDeviceTariffAmount(index)" size="40px" color="red">
                      mdi-close-circle-outline
                    </v-icon>
                  </div>
                </div>
                <div  @click="openAddUsersExel(item.id)">
                  <v-btn class="my-styled-btn">მომხმარებლების დამატება</v-btn>
                </div>
                <div  @click="detailDevice(item.id)">
                  <v-btn class="my-styled-btn">ლიფტის ინფომრაცია ვრცლად</v-btn>
                </div>
              </template>

              <template v-slot:text></template>
            </v-card>
          </v-col>
        </v-row>



      </v-container>
    </v-card>
  </div>
</template>
<script>
import { VDataTable } from 'vuetify/labs/components'
import { th } from 'vuetify/locale'
import SignalIcon from '../components/icon/SignalIcon.vue'
 import Swal from 'sweetalert2'
import router from '@/router'
import * as XLSX from "xlsx";

export default {
  name: 'devoce',
  components: { VDataTable, SignalIcon },

  data: () => ({
    userAmount: 0,
    totalAmount: 0,
    dialogBussines: true,
    allLiftTariff: false,
    isAdmin: false,
    isAddingUserExcel: false,
      excelFile: null,  
      parsedData: [],  
    singleLiftMapBool: [],
    eachLiftTariffAmount: 0,
    editedFixedCardAmount: 0,
    editedDeviceTariffAmount: 0,
    editedPhoneNumberTarrif: 0
    , deviceID: 0,
    items: [],
    expanded: [],
    fota: {},
    dialogFixedCard: false,
    dialogFixedDeviceTarrif: false,
    dialogFixedPhoneNumber: false,
    dialogFota: false,
    dialog: false,
    dialogDelete: false,
    dialogPassword: false,
    itemsPerPage: 5,
    headers: [
      {
        title: 'Name',
        align: 'start',
        sortable: false,
        key: 'name',
      },
      { title: 'limit of cards', key: 'limit' },
      { title: 'Mode', key: 'op_mode' },
      { title: 'Relay', key: 'relay1_node' },
      { title: 'UUID', key: 'dev_id', align: 'start' },
      { title: 'status', key: 'status', align: 'center' },

      { title: 'actions', key: 'actions' },
      { title: '', key: 'data-table-expand' },
    ],
    hasError: true,
    isActive: true,
    notActive: true,
    deleted: false,
    search: '',
    serverItems: [],
    loading: true,
    totalItems: 0,
    desserts: [],
    editedIndex: -1,
    editedItem: {
      company_id: null,
      name: '',
      dev_name: 'lifti:',
      dev_id: '',
      op_mode: 0,
      tariff_amount: 10,
      fixed_amount: 0,
      admin_email: '',
      sim_card_number: null,

      guest_msg_L1: 'daaweqi Rilaks',
      guest_msg_L2: 'stumris reJimi',
      guest_msg_L3: 'aqtiuria',
      validity_msg_L1: 'daaweqi Rilaks',
      validity_msg_L2: 'gadaxdilia',
      lcd_brightness: 50,
      msg_appear_time: 5,
      relay_pulse_time: 5,
      card_read_delay: 5,
      storage_disable: false,
      can_search: false,
      relay1_node: 0,
    },
    defaultItem: {
      company_id: null,
      name: '',
      sim_card_number: null,
      dev_name: 'lifti:',
      dev_id: '',
      op_mode: 0,
      tariff_amount: 10,
      fixed_amount: 0,
      admin_email: '',
      guest_msg_L1: 'daaweqi Rilaks',
      guest_msg_L2: 'stumris reJimi',
      guest_msg_L3: 'aqtiuria',
      validity_msg_L1: 'daaweqi Rilaks',
      validity_msg_L2: 'gadaxdilia',
      lcd_brightness: 50,
      led_brightness: 50,
      msg_appear_time: 5,
      relay_pulse_time: 5,
      card_read_delay: 5,
      storage_disable: false,
      can_search: false,
      relay1_node: 0,
    },
    resetItem: {
      guest_msg_L1: 'daaweqi Rilaks',
      guest_msg_L2: 'stumris reJimi',
      guest_msg_L3: 'aqtiuria',
      validity_msg_L1: 'daaweqi Rilaks',
      validity_msg_L2: 'gadaxdilia',
      lcd_brightness: 50,
      msg_appear_time: 5,
      relay_pulse_time: 5,
      card_read_delay: 5,
      op_mode: 0,
      tariff_amount: 10,
      storage_disable: false,
      can_search: false,
      relay1_node: 0,
    },
    companies: [],
    unregistered_device: [],
    transactionDialog: false,
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? 'New Device' : 'Edit Device'
    },
    serverItemsFilter() {


       
      const res = this.serverItems?.filter((x) => {

        if (
          x.name.includes(this.search) ||
          x.user.email.includes(this.search) ||
          x.dev_id.includes(this.search)
        ) {
          if (
            !this.isActive &&
            !this.notActive &&
            !this.hasError &&
            !this.deleted
          )
            return x

          const active = new Date(x.lastBeat).getTime() > new Date().getTime()
          if (!x.deleted_at) {
            if (this.isActive && active) {
              return x
            }
            if (this.notActive && !active) {
              return x
            }
            if (this.hasError && x.errors.length) {
              return x
            }
          }
          if (this.deleted && x.deleted_at) {
            return x
          }
        }
      })
 
      return res
    },
  },
  created() {
    this.chackAdminEmail()
// shigit st
    if (this.isAdmin) {
      axios.get('api/companies').then(({ data }) => {
        this.items = data['companies']
      })
      axios.get('api/unregistered_device').then(({ data }) => {
        this.unregistered_device = data.map((x) => x.dev_id)
      })
    }


    this.loadItems()
  },

  watch: {
    dialog(val) {
      val || this.close()
    },
    dialogDelete(val) {
      val || this.closeDelete()
    },
    userAmount(val) {
      if (val) {
      }
    },
  },

  methods: {
    chackAdminEmail() {
      const token = localStorage.getItem('vuex')
      let email = JSON.parse(token).auth.user.email
      this.isAdmin = email === 'info@eideas.io'
    },
    handleLiftAmountTariffInput(event) {
      this.liftTariffValue = event.target.value
    },
    toggleLiftTariff() {
      this.allLiftTariff = !this.allLiftTariff
    },
    openSingleDeviceTariffAmount(index) {
      let newBoolArr = [...this.singleLiftMapBool]
      newBoolArr[index] = !newBoolArr[index]
      this.singleLiftMapBool = newBoolArr
    },
    openFixedCardDialog(item, id) {
      this.deviceID = id
      this.editedFixedCardAmount = item
      this.dialogFixedCard = true;
      console.log(this.dialogFixedCard)
    },
    // 
    openDeviceTariffDialog(item, id) {
      this.deviceID = id

      this.editedDeviceTariffAmount = item;
      this.dialogFixedDeviceTarrif = true;
    },
    openDevicePhoneTarrifDialog(item, id) {
      this.deviceID = id
      this.editedPhoneNumberTarrif = item;
      this.dialogFixedPhoneNumber = true
    },


    saveFixedCardAmount() {


      axios
        .put("/api/update-fixed-card-amount", {
          device_id: this.deviceID,
          amount: this.editedFixedCardAmount,
        })
        .then((res) => {
          console.log(res);
          this.dialogFixedCard = false;
          this.$swal.fire({
            icon: 'success',
            position: 'center',
            allowOutsideClick: false,
          })
          this.loadItems()
        })
        .catch((err) => {
          console.log(err);
          this.$swal.fire({
            icon: 'error',
            position: 'center',
            allowOutsideClick: false,
            message: err
          })
          this.dialogFixedCard = false;
        });

    },
    savePhoneNumberAmount() {
      // /update-fixed-phone-amount
      axios
        .put("/api/update-fixed-phone-amount", {
          device_id: this.deviceID,
          amount: this.editedPhoneNumberTarrif,
        })
        .then((res) => {
          console.log(res);
          this.dialogFixedPhoneNumber = false;
          this.$swal.fire({
            icon: 'success',
            position: 'center',
            allowOutsideClick: false,
          })
          this.loadItems()
        })
        .catch((err) => {
          console.log(err);
          this.$swal.fire({
            icon: 'error',
            position: 'center',
            allowOutsideClick: false,
            message: err
          })
          this.dialogFixedPhoneNumber = false;
        });

    },
    // 
    changeSingleLiftAmount(id, index) {
      axios
        .put(`/api/device/tariff/${this.deviceID}`, {
          amount: Number(this.editedDeviceTariffAmount),
        })
        .then((res) => {
          console.log(res)
          this.$swal.fire({
            icon: 'success',
            position: 'center',
            allowOutsideClick: false,
          }).catch(err => {
            this.$swal.fire({
              icon: 'error',
              position: 'center',
              allowOutsideClick: false,
              message: err
            })
          })
          this.loadItems()
          this.dialogFixedDeviceTarrif = false
          // this.openSingleDeviceTariffAmount(index)

        })
    },


    loadItems() {
      axios.get('api/devices').then(({ data }) => {
        this.serverItems = data

        this.chackAdminEmail()
      })
    },
    detailDevice(id) {
      router.push({ name: `devicesDetail`, params: { id: id } })
    },
    openAddUsersExel(id){
      this.deviceID = id
      this.isAddingUserExcel = true 
    },
  // Trigger file input click
  handleDrop(event) {
      const file = event.dataTransfer.files[0];
      if (file) {
        this.excelFile = file;
        console.log("File dropped:", this.excelFile.name);
      } else {
        alert("გთხოვთ ატვირთოთ სწორი Excel ფაილი.");
      }
    },

    // Handle file selection from the input
    handleFileInput(event) {
      const file = event.target.files[0];
      if (file) {
        this.excelFile = file;
        console.log("File selected:", this.excelFile.name);
      }
    },

    // Remove the uploaded file
    removeUploadedFile() {
      this.excelFile = null;
      console.log("Uploaded file removed.");
    },

    // Close the dialog
    closeExelDialog() {
      this.isAddingUserExcel = false;
      this.excelFile = null;
    },

    // Upload and process Excel file
    uploadExel() {
      if (!this.excelFile) {
        alert("ფაილი არ არის არჩეული.");
        return;
      }

      const reader = new FileReader();
      reader.onload = (e) => {
        const data = e.target.result;
        const workbook = XLSX.read(data, { type: "binary" });
        const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
        const parsedData = XLSX.utils.sheet_to_json(firstSheet);
        // console.log("Parsed Excel Data:", parsedData);

        axios.post("api/create-multiple-users", {
          users:parsedData,
          device_id:this.deviceID
        }).then((res)=>{

          this.$swal.fire({
            icon: 'success',
            position: 'center',
            text:"მომხმარებლებლი წარმატებით აიტივრთა",
            allowOutsideClick: false,
          })
        }).catch((err)=>{
         
          this.$swal.fire({
            icon: 'error',
            position: 'center',
        text:err.response.data.msg,
            allowOutsideClick: false,
          })
        })
      };
      reader.readAsBinaryString(this.excelFile);
    },
  
    editItem(item) {
      this.editedIndex = this.serverItems.indexOf(item)
      this.editedItem = Object.assign({ admin_email: item.user.email }, item)
      this.editedItem.can_search = !!this.editedItem.can_search
      this.editedItem.op_mode = Number(this.editedItem.op_mode)
      this.dialog = true
    },
    resetDevice(item) {
      const userResponse = confirm('Do you want to reset Device settings?')
      if (userResponse) {
        axios
          .put('/api/devices/' + item.id + '/reset', {
            ...item,
            ...this.resetItem,
            admin_email: item.user.email,
          })
          .then(() => this.loadItems())
      }
    },
    setConfToDevice(item) {
      const userResponse = confirm('Do you want to set Device settings?')
      if (userResponse) {
        axios.get('/api/devices/' + item.id + '/appconf')
      }
    },
    setExtToDevice(item) {
      const userResponse = confirm('Do you want to set Device ext settings?')
      if (userResponse) {
        axios.get('/api/devices/' + item.id + '/extconf')
      }
    },
    deleteItem(item) {
      const userResponse = confirm('Do you really want to delete?')
      if (userResponse) {
        axios.delete('api/devices/' + item.id).then(({ data }) => {
          this.$swal.fire({
            icon: 'success',
            position: 'center',
            allowOutsideClick: false,
          })
          this.loadItems()
        })
      }
    },

    deleteItemConfirm() {
      this.desserts.splice(this.editedIndex, 1)
      this.closeDelete()
    },

    close() {
      this.dialog = false
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      })
    },
    deleteError(id) {
      this.$swal
        .fire({
          title: 'ნამდვილად გსურთ თავიმჯდომარის განბლოკვა?',
          showCancelButton: false,
          showDenyButton: true,
          confirmButtonText: this.$t('Yes'),
          denyButtonText: this.$t('Close'),
        })
        .then((result) => {
          /* Read more about isConfirmed, isDenied below */
          if (result.isConfirmed) {
            axios.delete('/api/device/error/' + id).then((x) => {
              this.$swal.fire({
                icon: 'success',
                position: 'center',
                allowOutsideClick: false,
              })
              this.loadItems()
            })
          } else if (result.isDenied) {
          }
        })
    },
    closeDelete() {
      this.dialogDelete = false
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      })
    },

    save() {
      axios.post('/api/devices', this.editedItem).then(() => this.loadItems())
      this.$swal.fire({
        icon: 'success',
        position: 'center',
        allowOutsideClick: false,
      })
      this.close()
    },
  },
}
</script>

<style scoped>
.transparent-dialog .v-overlay {
  background-color: rgba(0, 0, 0, 0);
  /* Transparent backdrop */
}

.drop-zone-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 200px;
}

.drop-zone {
  width: 100%;
  max-width: 400px;
  height: 150px;
  border: 2px dashed #ccc;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  transition: border-color 0.2s ease-in-out;
}

.drop-zone:hover {
  border-color: #007bff;
}

.drop-zone p {
  margin: 0;
  font-size: 14px;
  color: #555;
}

.drop-zone input {
  display: none;
}

.drop-zone v-btn {
  margin-top: 10px;
}
.button-container {
  display: flex;
  justify-content: space-between;
  margin-top: 10px;
}

.my-styled-btn {
  margin: 10px;
  font-size: 14px;
}

.drop-zone {
  width: 100%;
  max-width: 400px;
  height: 150px;
  border: 2px dashed #ccc;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  transition: border-color 0.2s ease-in-out;
}

.drop-zone:hover {
  border-color: #007bff;
}
</style>
