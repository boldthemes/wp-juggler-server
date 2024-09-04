<script setup>
import { useWpjsStore } from "./store.js";
import { onMounted, computed, ref } from "vue";
import { useQueryClient, useQuery, useMutation } from "@tanstack/vue-query";
import ExpandedRow from "./ExpandedRow.vue";
import ThemesPluginsPanel from "./ThemesPluginsPanel.vue";
import HealthPanel from "./HealthPanel.vue";
import UptimePanel from "./UptimePanel.vue";
import NoticesPanel from "./NoticesPanel.vue";

const queryClient = useQueryClient();

const store = useWpjsStore();

const search = ref("");

const expanded = ref([]);
const headers = [
  { title: "", key: "network", align: "center", sortable: false },
  { title: "Title", value: "title", align: "start", sortable: true },
  {
    title: "Url",
    key: "wp_juggler_server_site_url",
    align: "start",
    sortable: true,
  },
  { title: "Messages", key: "events", align: "center", sortable: false },
  {
    title: "Downtime incidents",
    key: "uptime",
    align: "center",
    sortable: false,
  },
  { title: "Updates", key: "updates", align: "center", sortable: false },
  { title: "Checksum", key: "checksum", align: "center", sortable: false },
  { title: "Links", key: "links", align: "center", sortable: false },
  { title: "WP admin", key: "wp_admin", align: "center", sortable: false },
];

const { isLoading, isError, isFetching, data, error, refetch } = useQuery({
  queryKey: ["wpjs-control-panel"],
  queryFn: getDashboard,
});

async function doAjax(args) {
  let result;

  try {
    const response = await fetch(store.ajaxUrl, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams(args),
    });
    const result = await response.json();
    return result;
  } catch (error) {
    throw error;
  }
}

async function getDashboard() {
  let ret = {};
  const response = await doAjax({
    action: "wpjs_get_control_panel", // the action to fire in the server
  });
  ret = response.data;
  return ret;
}

function backToDashboard() {
  window.location.href = wpjs_control_panel_object.adminurl;
}

function calculateColor(day) {
  if (day.total_num == 0) return "blue-lighten-5";
  if (day.fail_num == 0) return "success";
  return "error";
}

const gotoUrl = (url) => {
  const newWindow = window.open(url, "_blank", "noopener,noreferrer");
  if (newWindow) newWindow.opener = null;
};

onMounted(() => {
  store.nonce = wpjs_control_panel_object.nonce;
  store.ajaxUrl = wpjs_control_panel_object.ajaxurl
});
</script>

<template>
  <div class="mt-4 ml-4 mb-4">
  <v-btn
    color="#2196f3"
    variant="flat"
    class="text-none text-caption"
    @click="backToDashboard"
    >Back to Dashboard</v-btn
  >
  <v-spacer></v-spacer>

  <v-card class="pa-4 mr-4 mt-5 mb-5">
    <v-card flat>
      <v-card-title class="d-flex align-center pe-2 mb-6">
        <v-icon icon="mdi-video-input-component"></v-icon> &nbsp; WP Juggler
        Control Panel

        <v-spacer></v-spacer>

        <v-text-field
          v-model="search"
          density="compact"
          label="Search"
          prepend-inner-icon="mdi-magnify"
          variant="solo-filled"
          flat
          hide-details
          single-line
        ></v-text-field>
      </v-card-title>

      <v-divider></v-divider>
      <v-data-table
        v-model:search="search"
        :items="data"
        :headers="headers"
        item-key="id"
        v-model:expanded="expanded"
        show-expand
      >
        <template v-slot:item.network="{ item }">
          <div v-if="item.wp_juggler_multisite">
            <v-icon
              color="#2196f3"
              icon="mdi-checkbox-multiple-blank-outline"
              size="large"
              class="rm-4"
            ></v-icon>
          </div>
        </template>

        <template v-slot:item.events="{ item }">
          <div v-if="item.wp_juggler_automatic_login">
            <v-icon
              color="#2196f3"
              icon="mdi-email-alert-outline"
              size="large"
              class="rm-4"
            ></v-icon>
          </div>
        </template>

        <template v-slot:item.uptime="{ item }">
          <div v-if="item.wp_juggler_site_activation">
            <v-icon
              v-for="day in item.wp_juggler_uptime_stats.uptime_timeline"
              :color="calculateColor(day)"
              icon="mdi-square"
              size="large"
              class="rm-4"
            ></v-icon>
          </div>
          <div v-if="!item.wp_juggler_site_activation">Inactive</div>
        </template>

        <template v-slot:item.updates="{ item }">
          <div v-if="item.wp_juggler_site_activation">
            <div v-if="item.wp_juggler_plugins_summary">
              <v-icon
                v-if="item.wp_juggler_plugins_summary.vulnerabilities_num > 0"
                color="error"
                icon="mdi-bug-check-outline"
                size="large"
                class="rm-4"
              ></v-icon>
              <v-icon
                v-else-if="
                  item.wp_juggler_plugins_summary.updates_num > 0 ||
                  item.wp_juggler_themes_summary.updates_num > 0
                "
                color="error"
                icon="mdi-check-bold"
                size="large"
                class="rm-4"
              ></v-icon>
            </div>
            <div v-else>
              <v-icon
                color="blue-lighten-5"
                icon="mdi-help"
                size="large"
                class="rm-4"
              ></v-icon>
            </div>
          </div>
          <div v-if="!item.wp_juggler_site_activation">Inactive</div>
        </template>

        <template v-slot:item.checksum="{ item }">
          <div v-if="item.wp_juggler_site_activation">
            <div
              v-if="
                item.wp_juggler_plugins_checksum &&
                item.wp_juggler_core_checksum
              "
            >
              <v-icon
                v-if="
                  item.wp_juggler_plugins_checksum.failures > 0 ||
                  item.wp_juggler_core_checksum.errors
                "
                color="error"
                icon="mdi-alert-outline"
                size="large"
                class="rm-4"
              ></v-icon>
            </div>
            <div v-else>
              <v-icon
                color="blue-lighten-5"
                icon="mdi-help"
                size="large"
                class="rm-4"
              ></v-icon>
            </div>
          </div>
          <div v-if="!item.wp_juggler_site_activation">Inactive</div>
        </template>

        <template v-slot:item.links="{ item }">
          <div v-if="item.wp_juggler_site_activation">
            <v-btn
              v-for="button in item.wp_juggler_login_tools"
              variant="elevated"
              @click="gotoUrl(button.wp_juggler_tool_url)"
              class="text-none text-caption mr-1 ml-1"
              >{{ button.wp_juggler_tool_label }}</v-btn
            >
          </div>
          <div v-if="!item.wp_juggler_site_activation">Inactive</div>
        </template>

        <template v-slot:item.wp_admin="{ item }">
          <div
            v-if="
              item.wp_juggler_site_activation && item.wp_juggler_automatic_login
            "
          >
            <v-btn
              color="#2196f3"
              variant="elevated"
              class="text-none text-caption"
              prepend-icon="mdi-login"
              @click="gotoUrl(item.wp_juggler_login_url)"
              >Login</v-btn
            >
          </div>
          <div
            v-if="
              item.wp_juggler_site_activation &&
              !item.wp_juggler_automatic_login
            "
          >
            <v-btn
              color="#2196f3"
              variant="elevated"
              class="text-none text-caption"
              prepend-icon="mdi-account-remove"
              @click="gotoUrl(item.wp_juggler_login_url)"
              >Login</v-btn
            >
          </div>
          <div v-if="!item.wp_juggler_site_activation">Inactive</div>
        </template>

        <template v-slot:expanded-row="{ columns, item }">
          <ExpandedRow :columns="columns" :item="item"></ExpandedRow>
        </template>
      </v-data-table>
    </v-card>
  </v-card>
  <v-btn
    color="#2196f3"
    variant="flat"
    class="text-none text-caption"
    @click="backToDashboard"
    >Back to Dashboard</v-btn
  >
</div>
<ThemesPluginsPanel v-if="store.activatedThemes"></ThemesPluginsPanel>
<HealthPanel v-if="store.activatedHealth"></HealthPanel>
<UptimePanel v-if="store.activatedUptime"></UptimePanel>
<NoticesPanel v-if="store.activatedNotices"></NoticesPanel>
</template>

<style>
body,
html {
  height: revert !important;
}

.wpjs-cp-table td {
  padding: 15px 0px;
}
</style>
