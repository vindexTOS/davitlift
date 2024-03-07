<template>
    <div>
        <v-card class="mb-3">
            <v-card-title>
                {{ $t('Upload file') }}
            </v-card-title>
            <v-card-text class="mb-0">

                <v-file-input type="file" id="file" :accept="['.bin']" v-on:change="handleFileUpload($event)"/>
                <v-card-actions class="justify-end">
                    <v-btn color="primary" v-on:click="submitFile()">{{ $t('save') }}</v-btn>
                </v-card-actions>
            </v-card-text>


        </v-card>

        <v-card
            class="mx-auto mb-3"
        >
            <v-card-title>
                {{ $t('Uploaded files') }}
            </v-card-title>

            <v-divider></v-divider>
            <v-virtual-scroll
                :items="files"
                height="200"
                item-height="48"
            >
                <template v-slot:default="{ item }">
                    <v-list-item
                        :title="item.filename"
                        :subtitle="item.version"
                    >
                        <template v-slot:prepend>
                            <v-icon class="bg-primary">mdi-file</v-icon>
                        </template>

                        <template v-slot:append>
                            <v-btn
                                icon="mdi-update"
                                size="x-small"
                                @click="updateDevice(item.filename,item.version)"
                                variant="tonal"
                            ></v-btn>
                            <v-btn
                                icon="mdi-delete"
                                size="x-small"
                                @click="deleteItem(item.id)"
                                variant="tonal"
                            ></v-btn>
                        </template>
                    </v-list-item>
                </template>
            </v-virtual-scroll>

        </v-card>
        <div v-if="updatingDevice.length">
            <v-card class="pa-2 mb-3">

                <v-row class="justify-space-between">
                    <v-col style="min-height: 100%" cols="12" md="6">
                    </v-col>
                    <v-col v-if="seriesB[0] + seriesB[1] + seriesB[2]" cols="12" md="6">
                        <v-card style="height: 100%" class="overflow-auto pa-2">

                            <h3>{{ $t('განახლებისთვის გამზადებული ლიფტების მდგომარეობა') }} </h3>
                            <h4>{{ $t('Total number of elevators') }}:{{ seriesB[0] + seriesB[1] + seriesB[2] }}</h4>
                            <apexchart width="400" height="350" type="donut" :options="chartOptionsC"
                                       :series="seriesB"></apexchart>
                        </v-card>

                    </v-col>
                </v-row>
            </v-card>

            <v-data-table
                v-if="updatingDevice.length"
                :items="updatingDevice"
                :headers="headers"
                v-model="selected"
            >

                <template v-slot:top>
                    <v-toolbar>
                      <div class='d-flex align-center'>
                      <v-checkbox
                          class="mt-5 mr-2"
                          style="color: black;opacity: 1"
                          v-model="allSelected"
                      >
                        <template v-slot:label>
                          <b style="opacity: 200 !important; color: black !important;">{{
                            $vuetify.display.mdAndUp ? $t('Select all') : $t('Updating lifts')
                          }}</b>
                        </template>
                      </v-checkbox>
                        <v-toolbar-title style="min-width: fit-content" v-if="$vuetify.display.mdAndUp" class="text-capitalize">{{ $t('Updating lifts') }}</v-toolbar-title>
                      </div>
                      <v-spacer></v-spacer>
                      <v-menu v-if="$vuetify.display.smAndDown">
                        <template v-slot:activator="{ props }">
                          <v-icon size="large" class="ma-2" icon="mdi-dots-vertical" v-bind="props"></v-icon>
                        </template>
                        <v-card  width="200" class="pa-0 ma-0">
                          <v-btn style="width: 100%;" color="primary" v-if="selected.length" @click="sendUpdateSelectedDevices">
                            {{ $t('Update software') }}
                          </v-btn>
                          <v-btn style="width: 100%;" color="red" v-if="seriesB[2]" @click="checkFailed">{{ $t('Clear failed') }}</v-btn>
                          <v-btn style="width: 100%;" color="red" v-if="seriesB[1]" @click="checkSuccess">{{ $t('Clear success') }}</v-btn>
                          <v-btn style="width: 100%;" @click="getUpdatingDevices">
                            <v-icon>mdi-reload</v-icon>
                          </v-btn>
                        </v-card>
                      </v-menu>
                      <template v-if="$vuetify.display.mdAndUp">
                        <v-btn color="primary" v-if="selected.length" @click="sendUpdateSelectedDevices">
                          {{ $t('Update software') }}
                        </v-btn>
                        <v-btn color="red" v-if="seriesB[2]" @click="checkFailed">{{ $t('Clear failed') }}</v-btn>
                        <v-btn color="red" v-if="seriesB[1]" @click="checkSuccess">{{ $t('Clear success') }}</v-btn>
                        <v-btn @click="getUpdatingDevices">
                          <v-icon>mdi-reload</v-icon>
                        </v-btn>
                      </template>


                    </v-toolbar>
                </template>

                <template v-slot:item="{ item }">
                    <tr>
                        <td>
                            <v-checkbox
                                :disabled="item.raw.status == 1 || item.raw.status == 4"
                                :value="item.raw.dev_id"
                                v-model="selected"
                            ></v-checkbox>
                        </td>

                        <td>
                            <div>{{item.raw.device.name}}</div>
                            <router-link :to="'/devices/'+item.raw.device_id">{{ item.raw.dev_id }}</router-link>
                        </td>
                        <td>
                            <v-chip
                                v-if="item.raw.status ==0"
                                class=""
                                color="yellow"
                                text-color="white"
                            >
                                გაუგზავნეელი
                            </v-chip>
                            <v-chip
                                v-if="item.raw.status ==4"
                                class=""
                                color="primary"
                                text-color="white"
                            >
                                უცნობია
                            </v-chip>
                            <v-chip
                                v-if="item.raw.status == 2"
                                class=""
                                color="red"
                                text-color="white"
                            >
                                წარუმატებელია
                            </v-chip>
                            <v-chip
                                v-if="item.raw.status == 1"
                                class=""
                                color="green"
                                text-color="white"
                            >
                                {{$t('completed')}}
                            </v-chip>
                        </td>
                        <td>{{ item.raw.previous_version }}</td>
                        <td>{{ item.raw.new_version }}</td>
                        <td>{{ formatMyDate(item.raw.updated_at) }}</td>

                        <!-- Render other columns as needed -->
                    </tr>
                </template>
            </v-data-table>
        </div>
    </div>
</template>

<script>
import {VDataTable} from "vuetify/labs/components";
import VueApexCharts from "vue3-apexcharts";

export default {
    components: {
        VDataTable, apexchart: VueApexCharts,
    },
    data() {
        return {
            selected: [],

            seriesB: [0, 0, 0],
            files: [],
            file: null,
            updatingDevice: [],

            allSelected: false
        };
    },

    created() {
        this.fetchFiles();
        this.getUpdatingDevices()
    },
    watch: {
      allSelected(val) {
        if(val) {
          this.selected = this.updatingDevice.map(x => {
            if (x.status != 4 && x.status != 1) {
              return x.dev_id
            }
          })
        } else {
          this.selected = []
        }
      }
    },
    computed: {
        chartOptionsC() {
            return {
                labels: [this.$t("უცნობია"), this.$t("წარმატებული"), this.$t("წარუმატებელი")]
            }
        },
        headers() {
            return [
                {
                    title: "", key: 'created_at',
                },
                {
                    title: this.$t("UUID"), key: 'dev_id',
                },
                {
                    title: this.$t("Status"), key: 'status',
                },
                {
                    title: this.$t("Old version"), key: 'old_version',
                },
                {
                    title: this.$t("New version"), key: 'new_version',
                },
                {
                    title: this.$t('Last interaction'), key: 'updated_at'
                }
            ]
        }
    },
    methods: {
        formatMyDate(date) {
            const d = new Date(date);
            const month = (d.getMonth() + 1).toString().padStart(2, '0'); // Months are zero-based
            const day = d.getDate().toString().padStart(2, '0');
            const hours = d.getHours().toString().padStart(2, '0');
            const minutes = d.getMinutes().toString().padStart(2, '0');

            return `${month}/${day} ${hours}:${minutes}`;
        },

        sendUpdateSelectedDevices() {
            this.$swal.fire({
                title: 'გსურთ დააგზავნოთ განახლებები მონიშნულ მოწყობილობებზე?',
                showCancelButton: false,
                showDenyButton: true,
                confirmButtonText: this.$t('Yes'),
                denyButtonText: this.$t('Close'),
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.post('/api/send/update/to/selected/devices', {
                        dev_ids: this.selected,
                        version: this.updatingDevice[0].new_version
                    }).then(() => {
                        this.$swal.fire({
                            icon: 'success',
                            position: 'center',
                            allowOutsideClick: false,
                        })
                        this.getUpdatingDevices()
                        this.loadItems();
                    })
                } else if (result.isDenied) {
                }
            })

        },
        getUpdatingDevices() {

            axios.get('/api/updating-device/last-created').then(({data}) => {
                this.updatingDevice = data['devices'];
                this.seriesB = [data['status_counts']['4'] ?? 0, data['status_counts']['1'] ?? 0, data['status_counts']['2'] ?? 0];
            })
        },
        checkSuccess() {
            this.$swal.fire({
                title: 'გსურთ გაასუთავოთ წარმატებულად განახლებული მოწყობილობები?',
                showCancelButton: false,
                showDenyButton: true,
                confirmButtonText: this.$t('Yes'),
                denyButtonText: this.$t('Close'),
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.get('/api/updating-device/check-success/last-created').then(() => {
                        this.$swal.fire({
                            icon: 'success',
                            position: 'center',
                            allowOutsideClick: false,
                        })
                        this.getUpdatingDevices()
                    })
                    this.loadItems();
                } else if (result.isDenied) {
                }
            })
        },
        checkFailed() {
            this.$swal.fire({
                title: 'გსურთ გაასუთავოთ წარუმატებელად განახლებული მოწყობილობები?',
                showCancelButton: false,
                showDenyButton: true,
                confirmButtonText: this.$t('Yes'),
                denyButtonText: this.$t('Close'),
            }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
                    axios.get('/api/updating-device/check-failed/last-created').then(() => {
                        this.$swal.fire({
                            icon: 'success',
                            position: 'center',
                            allowOutsideClick: false,
                        })
                        this.getUpdatingDevices()
                    })

                    this.loadItems();
                } else if (result.isDenied) {
                }
            })
        },
        handleFileUpload(event) {
            this.file = event.target.files[0];
        },
        submitFile() {
            /*
                    Initialize the form data
                */
            let formData = new FormData();

            /*
                Add the form data we need to submit
            */
            formData.append('file', this.file);

            /*
              Make the request to the POST /single-file URL
            */
            axios.post('/api/upload',
                formData,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }
            ).then(() => {
                this.$swal.fire({
                    icon: 'success',
                    position: 'center',
                    allowOutsideClick: false,
                })
                this.fetchFiles()
            })
                .catch(function () {
                    console.log('FAILURE!!');
                });
        },

        async fetchFiles() {
            try {
                const response = await axios.get('/api/files');
                this.files = response.data;
            } catch (error) {
                console.error("There was an error fetching the files:", error);
            }
        },

        async deleteItem(id) {
            this.$swal.fire({
                title: this.$t("Do you really want to delete?"),
                showCancelButton: false,
                showDenyButton: true,
                confirmButtonText: this.$t('Yes'),
                denyButtonText: this.$t('Close'),
            }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
                    axios.delete(`/api/files/${id}`).then(() => {
                        this.fetchFiles()
                        this.$swal.fire({
                            icon: 'success',
                            position: 'center',
                            allowOutsideClick: false,
                        })
                    });
                } else if (result.isDenied) {
                }
            })
        },
        async updateDevice(name, version) {
            this.$swal.fire({
                title: this.$t("Do you really want to update all lifts?"),
                showCancelButton: false,
                showDenyButton: true,
                confirmButtonText: this.$t('Yes'),
                denyButtonText: this.$t('Close'),
            }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
                    axios.get(`/api/files/${name}/${version}`).then(() => {
                        this.$swal.fire({
                            icon: 'success',
                            position: 'center',
                            allowOutsideClick: false,
                        })
                        this.getUpdatingDevices()
                    });
                } else if (result.isDenied) {
                }
            })
        },


    },
};
</script>
