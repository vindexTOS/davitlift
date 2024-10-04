<style>
@media (max-width: 600px) {
    .zoom-in-icon {
        display: none;
    }
    .user-tabe-base {
        width: 350px;
        overflow-x: scroll;
    }
}
.user-tabe-base {
    width: 100%;
    padding: 20px;
    background-color: white;
}
.zoom-in-icon {
    font-size: 50px;
    cursor: pointer;
}

.zoom-in-icon:hover {
    color: #007bff;
    font-size: 60px;
}

.zoom-in-wrapper {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: end;
    padding: 20px;
}

.user-table-hidden {
    display: none;
}

.user-table-zoom {
    position: absolute;
    width: 1500px;
    left: 5px;
    background-color: white;
    z-index: 2000;
}
</style>
<template>
    <v-container
        :class="{
            'pa-0': $vuetify.display.xs,
        }"
        v-if="data"
    >
        <v-card class="pa-sm-2">
            <template v-slot:title>
                <div
                    v-if="$vuetify.display.mdAndUp"
                    class="d-flex justify-space-between"
                >
                    <div class="d-flex">
                        <h1>
                            <v-btn
                                icon="mdi-arrow-left"
                                size="small"
                                @click="goBack"
                            ></v-btn>
                            {{ data.name }}
                        </h1>
                        <v-chip
                            v-if="
                                new Date(data.lastBeat).getTime() >
                                new Date().getTime()
                            "
                            class="ma-2"
                            color="green"
                            text-color="white"
                        >
                            აქტიური
                        </v-chip>
                        <v-chip
                            v-else
                            class="ma-2"
                            color="red"
                            text-color="white"
                        >
                            {{ $t("Inactive") }}
                        </v-chip>
                    </div>

                    <div class="d-flex align-center">
                        <SignalIcon :signal="Math.ceil(data.signal / 20)" />

                        <v-icon
                            v-if="data.errors.length"
                            size="small"
                            color="red"
                        >
                            mdi-alert
                        </v-icon>
                        <v-menu v-if="$store.state.auth.user.lvl >= 3">
                            <template v-slot:activator="{ props }">
                                <v-icon
                                    size="small"
                                    icon="mdi-dots-vertical"
                                    v-bind="props"
                                ></v-icon>
                            </template>

                            <v-card class="pa-0 ma-0">
                                <v-btn
                                    style="width: 100%"
                                    @click="editItem(data, 'bussines')"
                                    small
                                >
                                    <v-icon size="small">mdi-pencil</v-icon>
                                    {{ $t("Edit") }}
                                </v-btn>
                                <v-btn
                                    v-if="$store.state.auth.user.lvl >= 4"
                                    style="width: 100%"
                                    @click="deleteItem(data)"
                                    small
                                >
                                    <v-icon size="small">mdi-delete</v-icon>
                                    {{ $t("Delete") }}
                                </v-btn>

                                <v-btn
                                    style="width: 100%"
                                    v-if="$store.state.auth.user.lvl >= 4"
                                    @click="setConfToDevice(data)"
                                    small
                                >
                                    <v-icon size="small">mdi-save</v-icon>
                                    {{ $t("App Configuration") }}
                                </v-btn>
                                <v-btn
                                    style="width: 100%"
                                    @click="setExtToDevice(data)"
                                    small
                                >
                                    <v-icon size="small">mdi-save</v-icon>
                                    {{ $t("Extend Configuration") }}
                                </v-btn>
                                <v-btn
                                    style="width: 100%"
                                    v-if="$store.state.auth.user.lvl >= 4"
                                    @click="resetDevice(data)"
                                    small
                                >
                                    <v-icon size="small">mdi-refresh</v-icon>
                                    {{ $t("Factory Reset") }}
                                </v-btn>
                            </v-card>
                        </v-menu>
                    </div>
                </div>
                <div v-if="$vuetify.display.smAndDown" class=" ">
                    <div class="  ">
                        <v-btn
                            icon="mdi-arrow-left"
                            size="small"
                            @click="goBack"
                        ></v-btn>

                        <span style="font-size: 26px" class="">
                            {{ data.name }}
                        </span>
                        <div class="d-flex align-center">
                            <v-chip
                                v-if="
                                    new Date(data.lastBeat).getTime() >
                                    new Date().getTime()
                                "
                                class="ma-2"
                                color="green"
                                text-color="white"
                            >
                                აქტიური
                            </v-chip>
                            <v-chip
                                v-else
                                class="ma-2"
                                color="red"
                                text-color="white"
                            >
                                {{ $t("Inactive") }}
                            </v-chip>
                            <SignalIcon :signal="Math.ceil(data.signal / 20)" />

                            <v-icon
                                v-if="data.errors.length"
                                size="small"
                                color="red"
                            >
                                mdi-alert
                            </v-icon>
                            <v-menu v-if="$store.state.auth.user.lvl >= 3">
                                <template v-slot:activator="{ props }">
                                    <v-icon
                                        size="small"
                                        icon="mdi-dots-vertical"
                                        v-bind="props"
                                    ></v-icon>
                                </template>

                                <v-card class="pa-0 ma-0">
                                    <v-btn
                                        style="width: 100%"
                                        @click="editItem(data, 'bussines')"
                                        small
                                    >
                                        <v-icon size="small">
                                            mdi-pencil
                                        </v-icon>
                                        {{ $t("Edit") }}
                                    </v-btn>
                                    <v-btn
                                        v-if="$store.state.auth.user.lvl >= 4"
                                        style="width: 100%"
                                        @click="deleteItem(data)"
                                        small
                                    >
                                        <v-icon size="small">
                                            mdi-delete
                                        </v-icon>
                                        {{ $t("Delete") }}
                                    </v-btn>

                                    <v-btn
                                        style="width: 100%"
                                        v-if="$store.state.auth.user.lvl >= 4"
                                        @click="setConfToDevice(data)"
                                        small
                                    >
                                        <v-icon size="small">mdi-save</v-icon>
                                        #
                                        {{ $t("App Configuration") }}
                                    </v-btn>
                                    <v-btn
                                        style="width: 100%"
                                        @click="setExtToDevice(data)"
                                        small
                                    >
                                        <v-icon size="small">mdi-save</v-icon>
                                        {{ $t("Extend Configuration") }}
                                    </v-btn>
                                    <v-btn
                                        style="width: 100%"
                                        v-if="$store.state.auth.user.lvl >= 4"
                                        @click="resetDevice(data)"
                                        small
                                    >
                                        <v-icon size="small">
                                            mdi-refresh
                                        </v-icon>
                                        {{ $t("Factory Reset") }}
                                    </v-btn>
                                </v-card>
                            </v-menu>
                        </div>
                    </div>
                </div>
            </template>
            <template v-slot:subtitle>
                კომპანია: {{ data.company?.company_name }}
            </template>
            <v-card-text>
                <div class="d-sm-flex justify-space-between">
                    <v-col class="px-0" cols="12" sm="6">
                        <v-card>
                            <v-card-text>
                                <h3>{{ $t("Chairman") }}</h3>
                                <h4>{{ $t("Name") }}: {{ data.user.name }}</h4>
                                <h4>
                                    {{ $t("Phone") }}: {{ data.user.phone }}
                                </h4>
                                <h4>
                                    {{ $t("Email") }}: {{ data.user.email }}
                                </h4>
                            </v-card-text>
                        </v-card>
                    </v-col>
                    <v-col
                        class="px-0"
                        style="min-height: 100%"
                        cols="12"
                        sm="6"
                    >
                        <v-card style="min-height: 100%">
                            <v-card-text>
                                <h3>{{ $t("Statistics") }}</h3>
                                <h4>
                                    {{ $t("Total amount earned") }}:
                                    {{ earnings / 100 }}
                                    {{ $t("Lari") }}
                                </h4>
                                <h4>
                                    {{ $t("Number of users") }}:
                                    {{ data.users.length }}
                                </h4>
                                <!--  -->
                                <h4 v-if="isAdmin">
                                    მომხმარებლების ბალანსების ჯამი:
                                    {{ totalUserBalance / 100 }}
                                    {{ $t("Lari") }}
                                </h4>
                                <h4 v-if="isAdmin">
                                    მომხმარებლების ტოტალური ტრანსაქციები:
                                    {{
                                        transactionTotalByMonthTotalledInOne.toFixed(
                                            2
                                        )
                                    }}
                                </h4>
                                <h4 v-if="isAdmin">
                                    აქტიური უსერები:
                                    {{ activeSubscriptions }}
                                </h4>
                                <h4 v-if="isAdmin">
                                    ფიქსირებული ბარათების ჯამი:
                                    {{ combinedCardPay }}
                                </h4>
                            </v-card-text>
                        </v-card>
                    </v-col>
                </div>
                <div
                    style="
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        width: 100%;
                        padding: 20px;
                    "
                    v-if="isAdmin"
                >
                    <div
                        style="
                            width: 40%;

                            flex-direction: column;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            gap: 5px;
                            box-shadow: 0px 0px 2px 0px rgba(0, 0, 0, 0.75);
                        "
                    >
                        <h4>ტრანზაქციების ჯამი</h4>
                        <ul
                            style="
                                height: 200px;
                                overflow-y: scroll;
                                width: 100%;
                                display: flex;
                                justify-content: center;
                                flex-direction: column;
                                padding: 50px;
                            "
                        >
                            <li
                                style="font-size: 20px"
                                v-for="(value, key) in transactionTotalByMonth"
                                :key="key"
                            >
                                {{ key }}:
                                <span style="color: green">{{
                                    value.toFixed(2)
                                }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <v-expansion-panels class="mb-3">
                    <v-expansion-panel v-if="$store.state.auth.user.lvl >= 2">
                        <template v-slot:title>
                            <h3>{{ $t("Lift settings") }}</h3>
                        </template>
                        <template v-slot:text>
                            <h4>
                                {{ $t("Mode of payment") }}:
                                {{
                                    data.op_mode == 1
                                        ? $t("Tariff") +
                                          data.tariff_amount +
                                          $t("Tetri")
                                        : this.$t("Fixed") +
                                          data.tariff_amount / 100 +
                                          this.$t("Lari")
                                }}
                         
                            </h4>
                            <h4>
                                {{ $t("გადახდის თარიღი") }}: {{ data.pay_day }}
                            </h4>
                            <h4>
                                {{ $t("Received signal strength") }}:
                                {{ data.signal }}%
                            </h4>
                            <h4>
                                {{ $t("Number of cards per user") }}:
                                {{ data.limit }}
                            </h4>
                            <h4 v-if="$store.state.auth.user.lvl >= 4">
                                {{ $t("Lift storage") }}:
                                {{
                                    data.storage_disable
                                        ? $t("Turned off")
                                        : $t("Turned on")
                                }}
                            </h4>
                            <h4 v-if="$store.state.auth.user.lvl >= 4">
                                {{ $t("LCD brightness") }}:
                                {{ data.lcd_brightness }}%
                            </h4>
                            <h4 v-if="$store.state.auth.user.lvl >= 4">
                                {{ $t("LED brightness") }}:
                                {{ data.led_brightness }}%
                            </h4>
                            <h4 v-if="$store.state.auth.user.lvl >= 4">
                                {{ $t("Card reading time") }}:
                                {{ data.card_read_delay }}{{ $t("Second") }}
                            </h4>
                            <h4 v-if="$store.state.auth.user.lvl >= 4">
                                {{ $t("Message display time") }}:
                                {{ data.msg_appear_time }}{{ $t("Second") }}
                            </h4>
                            <h4 v-if="$store.state.auth.user.lvl >= 4">
                                {{ $t("Relay pulse time") }}:
                                {{ data.relay_pulse_time }}{{ $t("Second") }}
                            </h4>
                            <h4 v-if="$store.state.auth.user.lvl >= 4">
                                {{ $t("The first line of the guest message") }}:
                                {{ englishToGeorgian(data.guest_msg_L1) }}
                            </h4>
                            <h4 v-if="$store.state.auth.user.lvl >= 4">
                                {{
                                    $t("The second line of the guest message")
                                }}:
                                {{ englishToGeorgian(data.guest_msg_L2) }}
                            </h4>
                            <h4 v-if="$store.state.auth.user.lvl >= 4">
                                {{ $t("The Third line of guest message") }}:
                                {{ englishToGeorgian(data.guest_msg_L3) }}
                            </h4>
                            <h4 v-if="$store.state.auth.user.lvl >= 4">
                                {{
                                    $t(
                                        "The first line of the validation message"
                                    )
                                }}:
                                {{ englishToGeorgian(data.validity_msg_L1) }}
                            </h4>
                            <h4 v-if="$store.state.auth.user.lvl >= 4">
                                {{
                                    $t(
                                        "The second line of the validation message"
                                    )
                                }}:
                                {{ englishToGeorgian(data.validity_msg_L2) }}
                            </h4>
                            <h4>
                                {{ $t("sim_card_number") }}
                                {{ data.sim_card_number ?? "-" }}
                            </h4>
                            <h4>
                                {{ $t("Software version") }}:
                                {{ data.soft_version?.split("").join(".") }}
                            </h4>
                            <h4>
                                {{ $t("Hardware version") }}:
                                {{ data.hardware_version?.split("").join(".") }}
                            </h4>
                            <h4>
                                {{ $t("Last update") }}:{{
                                    data.last_update &&
                                    data.last_update.updated_at
                                        ? formatDateToCustomFormat(
                                              new Date(
                                                  data.last_update.updated_at
                                              )
                                          )
                                        : "-"
                                }}
                            </h4>
                        </template>
                    </v-expansion-panel>
                    <!--                    <v-expansion-panel>-->
                    <!--                        <template v-slot:title>-->
                    <!--                            <h3>მომხმარებლები </h3>-->
                    <!--                        </template>-->
                    <!--                        <template v-slot:text>-->
                    <!--                            <UserTable :deviceId="data.id" @loadDevice="loadItems" :is-fixed="data.op_mode == 0" :server-items="data.users"></UserTable>-->
                    <!--                        </template>-->
                    <!--                    </v-expansion-panel>-->
                    <v-expansion-panel class="ma-0">
                        <template v-slot:title>
                            <h3>{{ $t("Amounts earned") }}</h3>
                        </template>
                        <template v-slot:text>
                            <EarningTable
                                :server-items="data.earnings"
                            ></EarningTable>
                        </template>
                    </v-expansion-panel>
                    <!--                    <v-expansion-panel>-->
                    <!--                        <template v-slot:title>-->
                    <!--                            <h3>გამოტანილი თანხები </h3>-->
                    <!--                        </template>-->
                    <!--                        <template v-slot:text>-->
                    <!--                            <withdrawals-table  :server-items="data.withdrawals"></withdrawals-table>-->
                    <!--                        </template>-->
                    <!--                    </v-expansion-panel>-->
                    <v-expansion-panel v-if="$store.state.auth.user.lvl >= 2">
                        <template v-slot:title>
                            <h3>{{ $t("Lift errors") }}</h3>
                        </template>
                        <template v-slot:text>
                            <div v-if="data.errors.length !== 0">
                                <ul>
                                    <li v-for="error in data.errors">
                                        <b>
                                            {{
                                                formatDateToCustomFormat(
                                                    new Date(error.created_at)
                                                )
                                            }}
                                            -
                                        </b>
                                        {{ error.errorText }}
                                        <v-icon
                                            color="red"
                                            @click="deleteError(error.id)"
                                        >
                                            mdi-delete
                                        </v-icon>
                                    </li>
                                </ul>
                            </div>
                            <div v-else>
                                <h2>{{ $t("There are no problems") }}</h2>
                            </div>
                        </template>
                    </v-expansion-panel>
                </v-expansion-panels>
                <!-- <h1 @click="test(data)">TEST</h1> -->
            </v-card-text>
        </v-card>
    </v-container>
    <v-dialog v-model="dialog" max-width="500px">
        <v-card>
            <v-card-title>
                <span class="text-h5">{{ $t("Edit") }}</span>
            </v-card-title>

            <v-card-text>
                <v-container>
                    <v-row>
                        <v-col v-if="dialogBussines" cols="12">
                            <v-text-field
                                v-model="editedItem.name"
                                class="text-capitalize"
                                :label="$t('Name')"
                                required
                            ></v-text-field>
                        </v-col>
                        <v-col v-if="dialogBussines" cols="12">
                            <v-text-field
                                v-model.number="editedItem.pay_day"
                                class="text-capitalize"
                                :label="$t('Payment number (from 1 to -28)')"
                                type="number"
                                required
                            ></v-text-field>
                        </v-col>
                        <v-col v-if="dialogAppConf" cols="12">
                            <v-autocomplete
                                v-model="editedItem.dev_id"
                                label="UUID"
                                :items="unregistered_device"
                            ></v-autocomplete>
                        </v-col>
                        <v-col v-if="dialogExtConf" cols="12">
                            <v-radio-group
                                :label="$t('Elevator mode')"
                                v-model="editedItem.relay1_node"
                                :inline="true"
                            >
                                <v-radio
                                    :label="$t('Free')"
                                    :value="1"
                                ></v-radio>
                                <v-radio
                                    :label="$t('Tariff')"
                                    :value="0"
                                ></v-radio>
                            </v-radio-group>
                        </v-col>
                        <v-col v-if="dialogBussines" cols="12">
                            <v-radio-group
                                :label="$t('Mode of payment')"
                                v-model="editedItem.op_mode"
                                :inline="true"
                            >
                            <!--     radio heads for tarrif change           -->
                                <v-radio
                                    :label="$t('Tariff')"
                                    :value="1"
                                ></v-radio>
                                <v-radio
                                    :label="$t('Fixed')"
                                    :value="0"
                                ></v-radio>
                                <v-radio
                                    :label="$t('Card Only')"
                                    :value="2"
                                ></v-radio>
                            </v-radio-group>

         <!--     radio heads for tarrif change           -->

                        </v-col>
                        <v-col v-if="dialogBussines" cols="12">
                            <v-text-field
                                v-model.number="editedItem.tariff_amount"
                                class="text-capitalize"
                                :label="$t('Charge (in Tetri)')"
                                required
                            ></v-text-field>
                        </v-col>
                        <v-col v-if="dialogBussines" cols="12">
                            <v-text-field
                                v-model.number="editedItem.fixed_card_amount"
                                class="text-capitalize"
                                :label="'ბარათის ტარიფი(თეთრებში)'"
                                required
                            ></v-text-field>
                        </v-col>

                        <v-col v-if="dialogBussines" cols="12">
                            <v-text-field
                                v-model="editedItem.admin_email"
                                class="text-capitalize"
                                :label="$t('Chairman mail')"
                                required
                            ></v-text-field>
                        </v-col>
                        <v-col v-if="dialogBussines" cols="12">
                            <v-text-field
                                v-model="editedItem.sim_card_number"
                                class="text-capitalize"
                                :label="$t('sim_card_number')"
                                required
                            ></v-text-field>
                        </v-col>

                        <v-col v-if="dialogAppConf" cols="12">
                            <v-text-field
                                v-model="editedItem.guest_msg_L1"
                                class="text-capitalize"
                                :label="
                                    $t('The first line of the guest message')
                                "
                                :rules="[maxLengthRule15]"
                                required
                            ></v-text-field>
                        </v-col>
                        <v-col v-if="dialogAppConf" cols="12">
                            <v-text-field
                                v-model="editedItem.guest_msg_L2"
                                class="text-capitalize"
                                :label="
                                    $t('The second line of the guest message')
                                "
                                :rules="[maxLengthRule15]"
                                required
                            ></v-text-field>
                        </v-col>
                        <v-col v-if="dialogAppConf" cols="12">
                            <v-text-field
                                v-model="editedItem.guest_msg_L3"
                                :rules="[maxLengthRule15]"
                                class="text-capitalize"
                                :label="$t('The Third line of guest message')"
                                required
                            ></v-text-field>
                        </v-col>
                        <v-col v-if="dialogAppConf" cols="12">
                            <v-text-field
                                v-model="editedItem.validity_msg_L1"
                                class="text-capitalize"
                                :label="
                                    $t(
                                        'The first line of the validation message'
                                    )
                                "
                                :rules="[maxLengthRule15]"
                                required
                            ></v-text-field>
                        </v-col>
                        <v-col v-if="dialogAppConf" cols="12">
                            <v-text-field
                                v-model="editedItem.validity_msg_L2"
                                class="text-capitalize"
                                :label="
                                    $t(
                                        'The second line of the validation message'
                                    )
                                "
                                :rules="[maxLengthRule15]"
                                required
                            ></v-text-field>
                        </v-col>
                        <v-col v-if="dialogAppConf" cols="11">
                            <span>
                                {{ $t("LCD brightness") }}:
                                {{ editedItem.lcd_brightness }}
                            </span>
                            <v-slider
                                v-model="editedItem.lcd_brightness"
                                :min="0"
                                :max="100"
                                :step="1"
                            ></v-slider>
                        </v-col>
                        <v-col v-if="dialogAppConf" cols="11">
                            <span>
                                {{ $t("LED brightness") }}:
                                {{ editedItem.led_brightness }}
                            </span>
                            <v-slider
                                v-model="editedItem.led_brightness"
                                :min="0"
                                :max="100"
                                :step="1"
                            ></v-slider>
                        </v-col>
                        <v-col v-if="dialogAppConf" cols="11">
                            <span>
                                {{ $t("Message display time") }}:
                                {{ editedItem.msg_appear_time }}
                            </span>
                            <v-slider
                                v-model="editedItem.msg_appear_time"
                                :min="0"
                                :max="60"
                                :step="1"
                            ></v-slider>
                        </v-col>
                        <v-col v-if="dialogAppConf" cols="11">
                            <span>
                                {{ $t("Relay pulse time") }}:
                                {{ editedItem.relay_pulse_time }}
                            </span>
                            <v-slider
                                v-model="editedItem.relay_pulse_time"
                                :min="0"
                                :max="30"
                                :step="1"
                            ></v-slider>
                        </v-col>
                        <v-col v-if="dialogAppConf" cols="11">
                            <span>
                                {{ $t("Card reading time") }}:
                                {{ editedItem.card_read_delay }}
                            </span>
                            <v-slider
                                v-model="editedItem.card_read_delay"
                                :min="0"
                                :max="30"
                                :step="1"
                            ></v-slider>
                        </v-col>
                        <v-col v-if="dialogBussines" cols="11">
                            <span>
                                {{ $t("Number of cards per user") }}:
                                {{ editedItem.limit }}
                            </span>
                            <v-slider
                                v-model="editedItem.limit"
                                :min="0"
                                :max="100"
                                :step="1"
                            ></v-slider>
                        </v-col>

                        <v-col v-if="dialogBussines" cols="12">
                            <v-textarea
                                v-model="editedItem.comment"
                                class="text-capitalize"
                                :label="$t('Comment')"
                                required
                            ></v-textarea>
                        </v-col>
                        <v-col cols="12" v-if="dialogAppConf">
                            <v-checkbox
                                v-model="editedItem.storage_disable"
                                :label="$t('Disable storage')"
                            ></v-checkbox>
                        </v-col>
                    </v-row>
                </v-container>
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue-darken-1" variant="text" @click="close">
                    {{ $t("Close") }}
                </v-btn>
                <v-btn color="blue-darken-1" variant="text" @click="save">
                    {{ $t("Save") }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
    <div
        @click="zoomIn()"
        :class="!isZoom ? 'user-table-hidden' : 'zoom-in-wrapper'"
    >
        <i class="mdi mdi-fullscreen-exit zoom-in-icon"></i>
    </div>
    <div
        v-if="isAdmin"
        @click="sendTest()"
        style="
            font-size: 14px;
            cursor: pointer;
            border-radius: 20px;
            padding: 1rem;
            width: 90px;
            color: white;
            aligntext: center;
            background-color: green;
        "
    >
        ტესტი
    </div>

    <div :class="isZoom ? 'user-table-zoom ' : 'user-table-hidden'">
        <UserTable
            :deviceId="data.id"
            @loadDevice="loadItems"
            :is-fixed="data.op_mode == 0"
            :server-items="usersInfo"
        ></UserTable>
    </div>
    <!-- meore -->
    <div
        @click="zoomIn()"
        :class="isZoom ? 'user-table-hidden' : 'zoom-in-wrapper'"
    >
        <i class="mdi mdi-fullscreen zoom-in-icon"></i>
    </div>

    <div :class="isZoom ? 'user-table-hidden' : 'user-tabe-base'">
        <UserTable
            :deviceId="data.id"
            @loadDevice="loadItems"
            :is-fixed="data.op_mode == 0"
            :server-items="usersInfo"
        ></UserTable>
    </div>
</template>
<script>
import { VDataTable } from "vuetify/labs/components";
import { th } from "vuetify/locale";
import SignalIcon from "../components/icon/SignalIcon.vue";
import UserTable from "../components/UserTable.vue";
import EarningTable from "../components/EarningTable.vue";
import WithdrawalsTable from "../components/WithdrawalsTable.vue";
import router from "@/router";

export default {
    components: {
        VDataTable,
        SignalIcon,
        UserTable,
        EarningTable,
        WithdrawalsTable,
    },
    name: "detailPage",
    data: () => ({
        usersInfo: { userData: [], pagination: 0 },
        transactionTotalByMonth: [],

        activeSubscriptions: 0,
        combinedCardPay: 0,
        transactionTotalByMonthTotalledInOne: 0,
        isZoom: false,
        totalUserBalance: 0,
        isAdmin: false,
        search: "",
        dialog: false,
        loading: true,
        totalItems: 0,
        desserts: [],
        editedIndex: -1,
        editedItem: {
            fixed_card_amount: 0,
            company_id: null,
            name: "",
            dev_name: "lifti:",
            dev_id: "",
            op_mode: 0,
            tariff_amount: 10,
            fixed_amount: 0,
            admin_email: "",
            guest_msg_L1: "stumris reJimi",
            guest_msg_L2: "daaWireT",
            guest_msg_L3: "sarTuls",
            validity_msg_L1: "saabonentos",
            validity_msg_L2: "vada",
            lcd_brightness: 50,
            led_brightness: 50,
            msg_appear_time: 5,
            relay_pulse_time: 5,
            card_read_delay: 3,
            limit: 5,
            comment: "",
            storage_disable: false,
            can_search: false,
            relay1_node: 0,
        },
        defaultItem: {
            company_id: null,
            name: "",
            dev_name: "lifti:",
            dev_id: "",
            op_mode: 0,
            tariff_amount: 10,
            fixed_amount: 0,
            admin_email: "",
            guest_msg_L1: "daaweqi Rilaks",
            guest_msg_L2: "stumris reJimi",
            guest_msg_L3: "aqtiuria",
            validity_msg_L1: "daaweqi Rilaks",
            validity_msg_L2: "gadaxdilia",
            lcd_brightness: 50,
            led_brightness: 50,
            msg_appear_time: 5,
            relay_pulse_time: 5,
            card_read_delay: 5,
            limit: 5,
            comment: "",
            storage_disable: false,
            can_search: false,
            relay1_node: 0,
        },
        resetItem: {
            guest_msg_L1: "daaweqi Rilaks",
            guest_msg_L2: "stumris reJimi",
            guest_msg_L3: "aqtiuria",
            validity_msg_L1: "daaweqi Rilaks",
            validity_msg_L2: "gadaxdilia",
            lcd_brightness: 50,

            led_brightness: 50,
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
        dialogBussines: false,
        dialogAppConf: false,
        dialogExtConf: false,
        maxLengthRule6: (value) => {
            console.log(value);
            const maxLength = 6;
            if (value.length <= maxLength) return true;
            return `ველის მნიშვნელობა არ უნდა აღემატებოდეს ${maxLength}ს   `;
        },
        maxLengthRule15: (value) => {
            const maxLength = 15;
            if (value.length <= maxLength) return true;
            return `ველის მნიშვნელობა არ უნდა აღემატებოდეს ${maxLength}ს   `;
        },
        data: {
            id: 3,
            startup: null,
            relay_pulse_time: 1,
            lcd_brightness: 50,
            led_brightness: 50,
            msg_appear_time: 5,
            card_read_delay: 3,
            tariff_amount: 15,
            timezone: null,
            storage_disable: 1,
            relay1_node: 0,
            dev_name: "lifti",
            op_mode: "1",
            dev_id: "0043002D3038510437393232",
            guest_msg_L1: "stumris reJimi",
            guest_msg_L2: "daaWireT",
            guest_msg_L3: "sarTuls",
            validity_msg_L1: "saabonentos",
            validity_msg_L2: "vada",
            name: "asdasd",
            comment: "sdfsdf",
            company_id: 1,
            users_id: 1,
            soft_version: "100",
            hardware_version: "02A",
            url: null,
            crc32: null,
            created_at: "2023-09-18T05:53:59.000000Z",
            updated_at: "2023-09-18T09:29:09.000000Z",
            deleted_at: null,
            can_search: 1,
            limit: 6,
            lastBeat: "2023-09-18 13:39:09",
            fixed_amount: "3.00",
            network: "2",
            signal: "93",
            user: {
                id: 1,
                name: "davit",
                email: "davitsarjveladzeee@gmail.com",
                email_verified_at: null,
                phone: "599284416",
                balance: 7520,
                created_at: "2023-09-15T07:38:21.000000Z",
                updated_at: "2023-09-18T09:20:12.000000Z",
            },
            users: [
                {
                    id: 1,
                    name: "davit",
                    email: "davitsarjveladzeee@gmail.com",
                    email_verified_at: null,
                    phone: "599284416",
                    balance: 7520,
                    created_at: "2023-09-15T07:38:21.000000Z",
                    updated_at: "2023-09-18T09:20:12.000000Z",
                    pivot: {
                        device_id: 3,
                        user_id: 1,
                        subscription: "2023-09-28 10:30:52",
                    },
                },
            ],
            earnings: [
                {
                    id: 2,
                    device_id: 3,
                    earnings: "510.00",
                    created_at: "2023-09-18T08:07:23.000000Z",
                    updated_at: "2023-09-18T09:20:12.000000Z",
                    month: 9,
                    year: 2023,
                },
            ],
            errors: [
           
            ],
        },
    }),

    created() {
        this.chackAdminEmail();
        this.loadItems();
    },
    computed: {
        earnings() {
            let sum = 0;
            this.data?.earnings.reverse().forEach((x) => {
                sum += +x.earnings;
            });
            return sum;
        },
        withdrawal() {
            let sum = 0;
            this.data?.withdrawals?.forEach((x) => {
                sum += +x.withdrawn_amount;
            });
            return sum;
        },
    },
    watch: {
        dialog(val) {
            val || this.close();
        },
        dialogDelete(val) {
            val || this.closeDelete();
        },
    },

    methods: {
        chackAdminEmail() {
            const token = localStorage.getItem("vuex");
            let email = JSON.parse(token).auth.user.email;
            this.isAdmin = email === "info@eideas.io";
        },
        zoomIn() {
            this.isZoom = !this.isZoom;
        },
        test(test) {
            console.log(test);
        },
        formatMyDate(date) {
            const d = new Date(date);
            const month = (d.getMonth() + 1).toString().padStart(2, "0"); // Months are zero-based
            const day = d.getDate().toString().padStart(2, "0");
            const hours = d.getHours().toString().padStart(2, "0");
            const minutes = d.getMinutes().toString().padStart(2, "0");

            return `${day}/${month}/${d.getFullYear()} ${hours}:${minutes}`;
        },
        loadItems(withCong = false) {
            axios
                .get("/api/devices/" + this.$route.params.id)
                .then(({ data }) => {
                    this.$nextTick(() => {});

                    this.data = data;

                    this.totalUserBalance = data.users
                        .map((val) => val.balance)
                        .reduce((a, b) => a + b);

                    this.usersInfo.userData = data.users;
                    this.usersInfo.pagination = Math.ceil(
                        data.users.length / 10
                    );

                    this.activeSubscriptions = this.usersInfo.userData.filter(
                        (item) => new Date(item.subscription) > new Date()
                    ).length;

                    this.combinedCardPay = this.usersInfo.userData
                        .map((val) => {
                            return (
                                val.cards_count * (val.fixed_card_amount / 100)
                            );
                        })
                        .reduce((a, b) => a + b);

                    this.getTransactionData();
                })
                .then(() => {
                    if (withCong) {
                    }
                });
        },
        sendTest() {
            axios.post("/api/testing-fix").then((res) => {
                this.$swal.fire({
                    title: "გაიგზავნა",
                });
            });
        },
        resetDevice(item) {
            this.$swal
                .fire({
                    title: "Do you want to reset Device settings?",
                    showCancelButton: false,
                    showDenyButton: true,
                    confirmButtonText: this.$t("Yes"),
                    denyButtonText: this.$t("Close"),
                })
                .then((result) => {
                    /* Read more about isConfirmed, isDenied below */
                    if (result.isConfirmed) {
                        axios
                            .put("/api/devices/" + item.id + "/reset", {
                                ...item,
                                ...this.resetItem,
                                admin_email: item.user.email,
                            })
                            .then(() => {
                                this.loadItems();
                                this.$swal.fire({
                                    icon: "success",
                                    position: "center",
                                    allowOutsideClick: false,
                                });
                            });
                    } else if (result.isDenied) {
                    }
                });
        },
        setConfToDevice(item) {
            this.editItem(item, "appConf");
        },
        setExtToDevice(item) {
            this.editItem(item, "extConf");
        },
        deleteItem(item) {
            this.$swal
                .fire({
                    title: "ნამდვილად გსურთ თავიმჯდომარის დაბლოკვა?",
                    showCancelButton: false,
                    showDenyButton: true,
                    confirmButtonText: this.$t("Yes"),
                    denyButtonText: this.$t("Close"),
                })
                .then((result) => {
                    /* Read more about isConfirmed, isDenied below */
                    if (result.isConfirmed) {
                        axios
                            .delete("/api/devices/" + item.id)
                            .then(({ data }) => {
                                this.$swal.fire({
                                    icon: "success",
                                    position: "center",
                                    allowOutsideClick: false,
                                });

                                this.loadItems();
                            });
                    } else if (result.isDenied) {
                    }
                });
        },

        close() {
            this.dialogBussines = false;
            this.dialogAppConf = false;
            this.dialogExtConf = false;
            this.dialog = false;
        },
        getTransactionData() {
            if (this.isAdmin) {
                axios
                    .get(
                        "/api/user/transaction/history/" + this.$route.params.id
                    )
                    .then(({ data }) => {
                        this.transactionTotalByMonth = data.data;
                        this.transactionTotalByMonthTotalledInOne =
                            this.calculateTotal(this.transactionTotalByMonth);
                    })
                    .catch((err) => console.log(err));
            }
        },
        calculateTotal(transactionTotalByMonth) {
            let total = 0;
            for (const month in transactionTotalByMonth) {
                total += transactionTotalByMonth[month];
            }
            return total;
        },
        deleteError(id) {
            this.$swal
                .fire({
                    title: "დარწმუნებული ხართ რომ გსურთ წაშალოთ ეს პრობლემა?",
                    showCancelButton: false,
                    showDenyButton: true,
                    confirmButtonText: this.$t("Yes"),
                    denyButtonText: this.$t("Close"),
                })
                .then((result) => {
                    /* Read more about isConfirmed, isDenied below */
                    if (result.isConfirmed) {
                        axios
                            .delete("/api/device/error/" + id)
                            .then(() => this.loadItems());
                        this.$swal.fire({
                            icon: "success",
                            position: "center",
                            allowOutsideClick: false,
                        });
                    } else if (result.isDenied) {
                    }
                });
        },
        closeDelete() {
            this.dialogDelete = false;
            this.$nextTick(() => {
                this.editedItem = Object.assign({}, this.defaultItem);
                this.editedIndex = -1;
            });
        },
        editItem(item, type) {
            this.editedItem = Object.assign(
                { admin_email: item.user.email },
                item
            );
            this.editedItem.can_search = !!this.editedItem.can_search;
            this.editedItem.storage_disable = !!this.editedItem.storage_disable;
            this.editedItem.op_mode = Number(this.editedItem.op_mode);
            console.log(this.editedItem.op_mode)
            this.dialogBussines = type === "bussines";
            this.dialogAppConf = type === "appConf";
            this.dialogExtConf = type === "extConf";
            this.dialog = true;
        },
        save() {
            let route = "/api/devices/" + this.editedItem.id;
            if (this.dialogBussines || this.dialogAppConf) {
                route = route + "/appconf";
            }
            if (this.dialogExtConf) {
                route = route + "/extconf";
            }
            axios.put(route, this.editedItem).then(() => {
                this.loadItems();
                this.close();
                this.$swal.fire({
                    icon: "success",
                    position: "center",
                    allowOutsideClick: false,
                    confirmButtonText: this.$t("Close"),
                });
            });

            axios
                .put("/api/update-fixed-card-amount", {
                    device_id: this.editedItem.id,
                    amount: this.editedItem.fixed_card_amount,
                })
                .then((res) => {
                    console.log(res);
                })
                .catch((err) => {
                    console.log(err);
                });
        },

        englishToGeorgian(englishText) {
            const mapping = {
                a: "ა",
                b: "ბ",
                c: "ც",
                d: "დ",
                e: "ე",
                f: "ფ",
                g: "გ",
                h: "ჰ",
                i: "ი",
                j: "ჯ",
                k: "კ",
                l: "ლ",
                m: "მ",
                n: "ნ",
                o: "ო",
                p: "პ",
                q: "ქ",
                r: "რ",
                s: "ს",
                t: "ტ",
                u: "უ",
                v: "ვ",
                w: "წ",
                x: "ხ",
                y: "ყ",
                z: "ზ",
                C: "ჩ",
                Z: "ძ",
                W: "ჭ",
                S: "შ",
                J: "ჟ",
                R: "ღ",
                T: "თ",
            };

            return englishText
                .split("")
                .map((char) => mapping[char] || char)
                .join("");
        },

        formatDateToCustomFormat(date) {
            const dd = String(date.getDate()).padStart(2, "0");
            const mm = String(date.getMonth() + 1).padStart(2, "0"); // January is 0!
            const yyyy = date.getFullYear();

            const hh = String(date.getHours()).padStart(2, "0");
            const min = String(date.getMinutes()).padStart(2, "0");

            return `${dd}/${mm}/${yyyy} ${hh}:${min}`;
        },
        goBack() {
            router.go(-1);
        },
    },
};
</script>
<style scoped></style>
