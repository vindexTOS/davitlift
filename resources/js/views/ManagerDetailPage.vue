<template>
    <!-- to do თუ მენეჯერს აქვს პროცენტი ამ შემთხვევაში კოპმანის სრული შემოსავლიდან უხდის გაწერილ პროცენტს თუ მენეჯრს ნული აქვს არ გავუტანთ გრაფას  -->
    <v-card v-if="data.manager" class="mb-4">
        <v-card-title>
            <v-btn icon="mdi-arrow-left" size="small" @click="goBack"></v-btn>
            {{ data.manager.name }}
        </v-card-title>
        <v-card-text>
            <div></div>
            <div>
                <b>{{ $t("Phone") }}:</b>
                {{ data.manager.phone }}
            </div>
            <div>
                <b>{{ $t("Email") }}:</b>
                {{ data.manager.email }}
            </div>
            <div>
                <b>{{ $t("Cashback") }}:</b>
                {{ data.manager.cashback }}%
            </div>
            <!-- <div v-if="isAdmin">
        <b>სრული ტარიფი:</b>
        {{ eachLiftTariffAmount }}
      </div> -->

            <div
                v-if="isAdmin"
                style="
                    display: flex;
                    align-items: start;
                    flex-wrap: wrap;
                    flex-direction: column;
                "
            >
                <div>
                    <b>ფიქსირებული ტარიფი ლიფტზე:</b>
                    {{ filtredDevices[0]["deviceTariffAmount"] }}
                    <v-icon @click="toggleLiftTariff" size="xs" color="gray">
                        mdi-pencil
                    </v-icon>
                </div>
                <div
                    v-if="allLiftTariff"
                    style="
                        display: flex;
                        align-items: center;
                        width: 200px;
                        margin-top: 10px;
                    "
                >
                    <v-text-field
                        v-model="liftTariffValue"
                        @input="handleInput"
                        :value="liftTariffValue"
                        type="number"
                        outlined
                        dense
                        :style="{
                            width: '100px',
                            'margin-top': '5px',
                        }"
                    ></v-text-field>
                    <v-icon
                        @click="changeLiftAmountMany"
                        size="40px"
                        color="green"
                    >
                        mdi-check-circle
                    </v-icon>
                    <v-icon @click="toggleLiftTariff" size="40px" color="red">
                        mdi-close-circle-outline
                    </v-icon>
                </div>
            </div>
        </v-card-text>
    </v-card>

    <v-card class="pa-2">
        <v-row class="justify-space-between">
            <v-col style="min-height: 100%" cols="12" md="6">
                <v-card style="height: 100%" class="overflow-auto pa-2">
                    <h3>{{ $t("Amounts deposited in months") }}</h3>
                    <h4>
                        {{ $t("Total amount earned") }}: {{ fullAmount }}
                        {{ $t("Lari") }}
                    </h4>
                    <apexchart
                        width="400"
                        type="bar"
                        :options="chartOptions"
                        :series="series"
                    ></apexchart>
                </v-card>
            </v-col>
            <v-col cols="12" md="6">
                <v-card style="height: 100%" class="overflow-auto pa-2">
                    <h3>{{ $t("Condition of elevators") }}</h3>
                    <h4>
                        {{ $t("Total number of elevators") }}:{{
                            seriesB[0] + seriesB[1]
                        }}
                    </h4>
                    <apexchart
                        width="400"
                        height="350"
                        type="donut"
                        :options="chartOptionsB"
                        :series="seriesB"
                    ></apexchart>
                </v-card>
            </v-col>
        </v-row>
        <!-- <h1 @click="testClick()">TESTTSETESTSETEST</h1> -->
        <v-row class="justify-space-between">
            <v-col style="min-height: 100%" cols="12" md="6"></v-col>
            <v-col v-if="seriesC" cols="12" md="6">
                <!-- ქეშბექი გასაცემი კლიენტზე -->
                <v-card
                    v-if="seriesC[0] + seriesC[1] > 0"
                    style="height: 100%"
                    class="overflow-auto pa-2"
                >
                    <h3>
                        {{ $t("Cashback") }} ჩასარიცხი
                        {{ seriesC[0] <= 0 ? 0 : seriesC[0].toFixed(2) }}
                    </h3>
                    <h4>
                        {{ $t("Total Cashback") }}: ჩარიცხული {{ seriesC[1] }}
                    </h4>
                    <apexchart
                        width="400"
                        height="350"
                        type="donut"
                        :options="chartOptionsC"
                        :series="seriesC"
                    ></apexchart>
                </v-card>
            </v-col>
        </v-row>
    </v-card>
    <div class="mt-3">
        <v-card>
            <v-container>
                <v-row>
                    <v-checkbox
                        :label="$t('Active')"
                        style="min-width: fit-content"
                        v-model="isActive"
                    ></v-checkbox>

                    <v-checkbox
                        :label="$t('Inactive')"
                        style="min-width: fit-content"
                        v-model="notActive"
                    ></v-checkbox>
                    <v-checkbox
                        :label="$t('With problems')"
                        style="min-width: fit-content"
                        v-model="hasError"
                    ></v-checkbox>
                    <v-checkbox
                        :label="$t('Deleted')"
                        style="min-width: fit-content"
                        v-model="deleted"
                    ></v-checkbox>
                </v-row>
            </v-container>
            <v-container>
                <v-row v-if="filtredDevices">
                    <v-col
                        style="min-width: fit-content"
                        v-for="(item, index) in filtredDevices"
                        cols="12"
                        sm="6"
                        md="4"
                        lg="3"
                        :key="item.id"
                    >
                        <!-- @click="detailDevice(item.id)" -->
                        <v-card @click="detailDevice(item.id)">
                            <template v-slot:title>
                                <div class="d-flex justify-space-between">
                                    <span>
                                        <v-icon
                                            v-if="item.errors.length"
                                            size="xs"
                                            color="red"
                                        >
                                            mdi-alert
                                        </v-icon>
                                        {{ item.name }}
                                        <SignalIcon
                                            :signal="
                                                Math.ceil(item.signal / 20)
                                            "
                                        />
                                    </span>
                                </div>
                            </template>

                            <template v-slot:subtitle>
                                <div class="d-flex justify-space-between">
                                    <v-chip
                                        v-if="
                                            new Date(item.lastBeat).getTime() >
                                            new Date().getTime()
                                        "
                                        class=""
                                        color="green"
                                        text-color="white"
                                    >
                                        {{ $t("Active") }}
                                    </v-chip>
                                    <v-chip
                                        v-else
                                        class=""
                                        color="red"
                                        text-color="white"
                                    >
                                        {{ $t("Inactive") }}
                                    </v-chip>
                                </div>
                                <b
                                    >{{ $t("Count of cards") }}:
                                    {{ item.limit }}</b
                                >
                                <hr />
                                <b>{{ item.dev_id }}</b>
                                <hr />
                                <b>
                                    {{
                                        item.network == 1
                                            ? "Ethernet"
                                            : item.network == 2
                                            ? "Cellular"
                                            : "Wifi"
                                    }}
                                </b>
                                <hr />
                                <b>
                                    {{
                                        item.op_mode == 0
                                            ? "გადახდის რიცხვი: " + item.pay_day
                                            : ""
                                    }}
                                </b>

                                <hr />
                                <div v-if="isAdmin">
                                    <div>
                                        <b
                                            >ლიფტის ტარიფი
                                            {{ item.deviceTariffAmount }} ₾</b
                                        >
                                        <v-icon
                                            @click="
                                                openSingleDeviceTariffAmount(
                                                    index
                                                )
                                            "
                                            size="xs"
                                            color="gray"
                                        >
                                            mdi-pencil
                                        </v-icon>
                                    </div>
                                    <!-- ლიფტის ტარიფის შეცვლა -->

                                    <div
                                        v-if="singleLiftMapBool[index]"
                                        style="
                                            display: flex;
                                            align-items: center;
                                            width: 200px;
                                            margin-top: 10px;
                                        "
                                    >
                                        <v-text-field
                                            v-model="liftTariffValue"
                                            @input="handleInput"
                                            :value="liftTariffValue"
                                            type="number"
                                            outlined
                                            dense
                                            :style="{
                                                width: '100px',
                                                'margin-top': '5px',
                                            }"
                                        ></v-text-field>
                                        <v-icon
                                            @click="
                                                changeSingleLiftAmount(
                                                    item.id,
                                                    index
                                                )
                                            "
                                            size="40px"
                                            color="green"
                                        >
                                            mdi-check-circle
                                        </v-icon>
                                        <v-icon
                                            @click="
                                                openSingleDeviceTariffAmount(
                                                    index
                                                )
                                            "
                                            size="40px"
                                            color="red"
                                        >
                                            mdi-close-circle-outline
                                        </v-icon>
                                    </div>
                                </div>
                                <hr />

                                <!-- <div v-if="isAdmin" @click="detailDevice(item.id)">
                  <v-btn class="my-styled-btn">ლიფტის ინფომრაცია ვრცლად</v-btn>
                </div> -->
                            </template>

                            <template v-slot:text></template>
                        </v-card>
                    </v-col>
                </v-row>
            </v-container>
        </v-card>
        <div class="mt-3">
            <CashbackTable
                @getCashback="getCashback"
                :serverItems="cashbackData['transaction']"
                :availableCashback="
                    cashbackData['total'] - cashbackData['totalWithdrow'] <= 0
                        ? 0
                        : cashbackData['total'] - cashbackData['totalWithdrow']
                "
                :maxCashback="seriesC[0].toFixed(2) + 200"
            ></CashbackTable>
        </div>
    </div>
</template>
<script>
import VueApexCharts from "vue3-apexcharts";
import router from "@/router";
import { th } from "vuetify/locale";
import ManagersTable from "../components/ManagersTable.vue";
import SignalIcon from "@/components/icon/SignalIcon.vue";
import CashbackTable from "../components/CashbackTable.vue";

export default {
    components: {
        SignalIcon,
        apexchart: VueApexCharts,
        ManagersTable,
        CashbackTable,
    },
    data: function () {
        return {
            data: {},
            singleLiftMapBool: [],

            isAdmin: false,
            hasError: true,
            isActive: true,
            notActive: true,
            allLiftTariff: false,
            liftTariffValue: "",
            deleted: false,
            seriesB: [0, 0],
            seriesC: [0, 0],
            seriesD: [0, 0],
            eachLiftTariffAmount: 0,
            cashBackAmount: 0,
            shouldCashBackOnManager: false,
            series: [
                {
                    name: "",
                    data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                },
            ],
            deviceEarningByMonth: [],

            totalMoney: 0,
            fullAmount: 0,
            cashbackData: {},
            displayCashBack: 0,
            totalDeviceTariff: 0,
            totalDeviceAmount: 0,
            sortedEarnings: [],
            companyFee: 0,
            mtlianiCash: 0,
        };
    },
    created() {
        this.getCashback();
        this.chackAdminEmail();
    },
    computed: {
        chartOptionsB() {
            return {
                labels: [this.$t("Inactive"), this.$t("Active")],
            };
        },
        chartOptions() {
            return {
                chart: {
                    id: "vuechart-exampl2e",
                },
                xaxis: {
                    categories: [
                        this.$t("იან"),
                        this.$t("თებ"),
                        this.$t("მარტი"),
                        this.$t("აპრ"),
                        this.$t("მაისი"),
                        this.$t("ივნ"),
                        this.$t("ივლ"),
                        this.$t("აგვ"),
                        this.$t("სექ"),
                        this.$t("ოქტ"),
                        this.$t("ნოემბ"),
                        this.$t("დეკ"),
                    ],
                },
            };
        },
        filtredDevices() {
            return this.data.device
                ? this.data.device.filter((x) => {
                      const active =
                          new Date(x.lastBeat).getTime() > new Date().getTime();
                      if (!x.deleted_at) {
                          if (this.isActive && active) {
                              return x;
                          }
                          if (this.notActive && !active) {
                              return x;
                          }
                          if (this.hasError && x.errors.length) {
                              return x;
                          }
                      }
                      if (this.deleted && x.deleted_at) {
                          return x;
                      }
                  })
                : [];
        },
        chartOptionsC() {
            return {
                labels: [this.$t("ჩასარიცხი"), this.$t("ჩარიცხული")],
            };
        },
        maxCashback() {
            // console.log(this.data.manager)
            if (this.data.manager) {
                // this.totalMoney = 210
                let cashbackAmount =
                    (this.totalMoney * this.data.manager.cashback) / 100;

                if (
                    cashbackAmount >
                    this.totalMoney - this.eachLiftTariffAmount
                ) {
                    this.cashBackAmount =
                        this.totalMoney - this.eachLiftTariffAmount;
                    return this.cashBackAmount;
                } else {
                    this.cashBackAmount = cashbackAmount;
                    return this.cashBackAmount;
                }
            }
        },
        //   seriesC() {
        //     if (this.cashbackData.total > -1) {
        //       this.shouldCashBackOnManager =
        //         Number(this.cashbackData.total) > this.displayCashBack
        //       return [this.displayCashBack, Number(this.cashbackData.total)]
        //     }
        //     return [0, 0]
        //   },
    },
    methods: {
        openSingleDeviceTariffAmount(index) {
            let newBoolArr = [...this.singleLiftMapBool];
            newBoolArr[index] = !newBoolArr[index];
            this.singleLiftMapBool = newBoolArr;
        },

        chackAdminEmail() {
            const token = localStorage.getItem("vuex");
            let email = JSON.parse(token).auth.user.email;
            this.isAdmin = email === "info@3.71.18.216";
        },
        handleLiftAmountTariffInput(event) {
            this.liftTariffValue = event.target.value;
        },
        toggleLiftTariff() {
            this.allLiftTariff = !this.allLiftTariff;
        },
        getCashBackData(data, deviceEarning) {
            let deviceTariffCombined = this.eachLiftTariffAmount;
            let cashback = data?.manager?.cashback;
            // console.log(data)
            // let needToPay = Object.values(data.earnings)[0].earnings / 100
            let needToPay = deviceEarning;

            let cashbackAmount = (needToPay * cashback) / 100;

            if (cashbackAmount > needToPay - deviceTariffCombined) {
                // this.companyFee = deviceTariffCombined
                let result = needToPay - deviceTariffCombined;

                return result;
            } else {
                let result = cashbackAmount;

                // this.companyFee = needToPay - cashbackAmount
                return result;
            }
        },

        getDeviceAmount() {
            this.eachLiftTariffAmount = this.filtredDevices
                .map((val) => val.deviceTariffAmount)
                .reduce((a, b) => a + b);
            console.log(this.filtredDevices);
        },
        getCashback() {
            axios
                .get(
                    `/api/get/pay/cashback/${this.$route.params.companyId}/${this.$route.params.id}`
                )
                .then(({ data }) => {
                    this.cashbackData = data;
                    console.log(data);
                    // conso  le.log(this.cashbackData['transaction'])
                    this.loadItems();
                    this.getDeviceAmount();
                    this.eachLiftTariffAmount = this.filtredDevices
                        .map((val) => val.deviceTariffAmount)
                        .reduce((a, b) => a + b);
                    this.liftTariffValue =
                        this.filtredDevices[0]["deviceTariffAmount"];
                    this.singleLiftMapBool = new Array(
                        this.filtredDevices.length
                    ).fill(false);
                });
        },
        calculateProecnt(data) {
            console.log(data);
            this.companyFee = 0;
            this.mtlianiCash = 0;
            this.totalDeviceAmount = data.device.filter(
                (val) => val.deleted_at == null
            ).length;
            let needToPay = 0;

            this.sortedEarnings.forEach((x) => {
                needToPay = x.earnings / 100;
                let totalDeviceTariff = x.devicetariff * this.totalDeviceAmount;
                let cashbackAmount = (x.cashback * needToPay) / 100;

                // ვამოწმებ თუ პროცენტით მოგება მეტია ტარიფზე
                let isProcenteMore = needToPay - cashbackAmount;
                console.log(data.device);
                if (isProcenteMore < totalDeviceTariff) {
                    this.mtlianiCash += needToPay - totalDeviceTariff;
                    this.companyFee += totalDeviceTariff;
                } else {
                    console.log(needToPay);

                    this.mtlianiCash += needToPay - isProcenteMore;
                    this.companyFee += isProcenteMore;
                }
            });

            let amountAlreadyPayed =
                Object.values(this.cashbackData["transaction"]).length <= 0
                    ? [
                          { amount: "0", type: 1 },
                          { amount: "0", type: 1 },
                      ]
                    : Object.values(this.cashbackData["transaction"]);

            // console.log(data['companyTransaction'])

            let amountAlreadyPayedNumber = amountAlreadyPayed
                ?.filter((val) => val.type !== 3)
                ?.map((val) => Number(val.amount))
                .reduce((a, b) => a + b);

            // console.log(data)
            this.totalTransfers = amountAlreadyPayedNumber;

            let finalResultOfDisplayAmount =
                this.mtlianiCash - amountAlreadyPayedNumber;
            console.log(finalResultOfDisplayAmount);
            console.log(amountAlreadyPayedNumber);

            this.seriesB = [
                data.deviceActivity.inactive,
                data.deviceActivity.active,
            ];

            this.seriesD = [
                finalResultOfDisplayAmount,
                Number(amountAlreadyPayedNumber),
            ];
            // this.seriesC = [finalResultOfDisplayAmount, Number(data.payedCompanyFee)]

            if (this.cashbackData.total > -1 && data.manager.cashback > 0) {
                // this.shouldCashBackOnManager =
                //   Number(this.cashbackData.total) > this.displayCashBack

                this.seriesC = [
                    finalResultOfDisplayAmount,
                    Number(this.cashbackData.total),
                ];
            }
            console.log(this.cashbackData.total);
            console.log(this.cashbackData);
            console.log(this.seriesC, this.seriesB, this.seriesD);

            // console.log(this.mtlianiCash)
        },
        loadItems() {
            axios
                .get("/api/companies/manager/" + this.$route.params.id)
                .then(({ data }) => {
                    this.data = data;

                    this.series[0].data = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
                    // console.log(data)
                    let lastEarning;

                    const sortedEarnings = Object.values(
                        this.data.earnings
                    ).sort((a, b) => {
                        return new Date(a.fullTime) - new Date(b.fullTime);
                    });
                    this.sortedEarnings = sortedEarnings;

                    sortedEarnings.forEach((x) => {
                        this.fullAmount += x.earnings / 100;
                        const earningsIndex = new Date(x.fullTime).getMonth();
                        if (this.series[0].data[earningsIndex] === undefined) {
                            this.series[0].data[earningsIndex] = 0;
                        }
                        this.series[0].data[earningsIndex] += x.earnings / 100;
                    });
                    //
                    // this.deviceEarningByMonth = Array.from(
                    //   Object.values(this.data.earnings),
                    // )
                    //   .map((x) => {
                    //     if (this.getCashBackData(data, x.earnings / 100) < 0) {
                    //       return 0
                    //     } else {
                    //       return this.getCashBackData(data, x.earnings / 100)
                    //     }
                    //   })
                    //   .reduce((a, b) => a + b)
                    // let amountAlreadyPayed =
                    //   Object.values(this.cashbackData['transaction']).length <= 0
                    //     ? [
                    //         { amount: '0', type: 1 },
                    //         { amount: '0', type: 1 },
                    //       ]
                    //     : Object.values(this.cashbackData['transaction'])

                    // let amountAlreadyPayedNumber = amountAlreadyPayed
                    //   ?.filter((val) => val.type === 1)
                    //   ?.map((val) => Number(val.amount))
                    //   .reduce((a, b) => a + b)
                    // let finalResultOfDisplayAmount =
                    //   this.deviceEarningByMonth - amountAlreadyPayedNumber
                    // this.displayCashBack = finalResultOfDisplayAmount
                    // Object.values(this.data.earnings).forEach((x) => {
                    //   if (
                    //     !lastEarning ||
                    //     new Date(x.fullTime) > new Date(lastEarning.fullTime)
                    //   ) {
                    //     lastEarning = x
                    //     this.totalMoney = lastEarning.earnings / 100
                    //   }
                    // })

                    console.log(this.totalMoney);
                    // this.seriesB = [
                    //   this.data.deviceActivity.inactive,
                    //   this.data.deviceActivity.active,
                    // ]
                    this.calculateProecnt(data);
                });

            this.eachLiftTariffAmount = this.filtredDevices
                .map((val) => val.deviceTariffAmount)
                .reduce((a, b) => a + b);
        },
        changeSingleLiftAmount(id, index) {
            axios
                .put(`/api/device/tariff/${id}`, {
                    amount: Number(this.liftTariffValue),
                })
                .then((res) => {
                    // console.log(res)

                    this.loadItems();
                    this.getCashback();
                    this.openSingleDeviceTariffAmount(index);
                    this.liftTariffValue = "";
                });
        },
        changeLiftAmountMany() {
            axios
                .put(`/api/device/manyTariff/${this.data.manager.id}`, {
                    amount: Number(this.liftTariffValue),
                })
                .then((res) => {
                    // console.log(res)
                    this.toggleLiftTariff();
                    this.getCashback();
                    this.liftTariffValue = "";
                })
                .catch((err) => console.log(err));
        },
        detailDevice(id) {
            router.push({ name: `devicesDetail`, params: { id: id } });
        },
        goBack() {
            router.go(-1);
        },
    },
};
</script>
<style scoped></style>
