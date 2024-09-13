<script setup>

import { useWpjsStore } from './store.js'
import { onMounted, computed, ref } from 'vue'
import { useQueryClient, useQuery, useMutation } from '@tanstack/vue-query'

const queryClient = useQueryClient()

const store = useWpjsStore()

const nonce = ref(wpjs_dashboard_object.nonce)

const activation_status = ref(false)

const dashboardHistoryItems = ref([]);
const dashboardPage = ref(0);

const ajaxError = ref(false);
const ajaxErrorText = ref('');

let infiniteScrollEvents

const { isLoading, isError, isFetching, data, error, refetch } = useQuery({
  queryKey: ['wpjs-dashboard'],
  queryFn: getDashboard
})

const dashboard_headers = [
  { title: "Event Name", value: "event_name", align: "start", sortable: true },
  { title: "Next Run", value: "next_run", align: "start", sortable: true },
  { title: "Schedule", value: "frequency", align: "start", sortable: true },
];

async function getDashboard() {

  let ret = {}
  const response = await doAjax({
    action: "wpjs_get_dashboard",  // the action to fire in the server
  })
  ret = response.data
  //api_key.value = response.data.api_key
  activation_status.value = (response.data.activation_status === 'true')
  return ret
}

async function getDashboardHistory() {
  let ret = {};
  const response = await doAjax({
    action: "wpjs-get-dashboard-history", // the action to fire in the server
    page: dashboardPage.value,
  });

  ret = response.data;
  return ret;
}

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

    return result

  } catch (error) {
    throw new Error('No response from the WP Juggler Server');
  }
}

async function loadDashboardHistory({ done }) {

  infiniteScrollEvents = done;

  const res = await getDashboardHistory();
  if (res.length == 0) {
    done("empty");
  } else {
    dashboardHistoryItems.value.push(...res);
    dashboardPage.value =
      dashboardHistoryItems.value[dashboardHistoryItems.value.length - 1].ID;
    done("ok");
  }
}

const cron_data = computed(() => {
  if (data.value) {

    const copiedArray = data.value
      .filter(item => {
        if (item.hook_slug === 'wpjs_check_health_api' || item.hook_slug === 'wpjs_check_plugins_api' || item.hook_slug === 'wpjs_check_notices_api') {
          return true
        } else {
          return false
        }
      })
      .map(item => {
        let eventName;
        if (item.hook_slug === 'wpjs_check_health_api') {
          eventName = 'Check Health and WP Core Data' // Example: new property based on condition
        } else if (item.hook_slug === 'wpjs_check_plugins_api') {
          eventName = 'Check Plugin and Theme Data'
        } else if (item.hook_slug === 'wpjs_check_notices_api') {
          eventName = 'Check Notices Data'
        }
        return {
          ...item,
          event_name: eventName,
          next_run: item.label_ago + ' - ' + item.time
        };
      });

    return Object.values(copiedArray);
  } else {
    return [];
  }
});

const cron_history = computed(() => {
  if (dashboardHistoryItems.value) {

    const copiedArray = dashboardHistoryItems.value
      .map(item => {
        let eventName;
        if (item.log_type === 'checkHealth') {
          eventName = 'Check Health and WP Core Data' // Example: new property based on condition
        } else if (item.log_type === 'checkPlugins') {
          eventName = 'Check Plugin and Theme Data'
        } else if (item.log_type === 'checkNotices') {
          eventName = 'Check Notices Data'
        }
        return {
          ...item,
          event_name: eventName,
        };
      });

    return Object.values(copiedArray);
  } else {
    return [];
  }
});


const openInNewTab = (url) => {
  const newWindow = window.open(url, '_blank', 'noopener,noreferrer')
  if (newWindow) newWindow.opener = null
}

onMounted(() => {
  store.nonce = wpjs_dashboard_object.nonce;
  store.ajaxUrl = wpjs_dashboard_object.ajaxurl
})

</script>

<template>
  <h1>WP Juggler Server Dashboard</h1>

  <v-card class="pa-4 mr-4" v-if="data">

    <v-sheet class="align-left justify-left text-left mx-auto mt-4 px-4 pb-4 mb-10">
      <div class="text-h6">WP Juggler Cron Events</div>
      <v-divider class="mb-10"></v-divider>

      <v-sheet>
        <v-spacer></v-spacer>

        <v-data-table :items="cron_data" :headers="dashboard_headers" item-key="index" hide-default-footer>
          <template v-slot:item.active="{ item }">
            <div v-if="item.Active && !item.NetworkActive">
              <v-icon color="success" icon="mdi-check-bold" size="large" class="rm-4"></v-icon>
            </div>
            <div v-if="item.NetworkActive">
              <v-icon color="success" icon="mdi-check-network-outline" size="large" class="rm-4"></v-icon>
            </div>
          </template>

          <template v-slot:item.update="{ item }">
            <div v-if="item.Update">
              <v-icon color="success" icon="mdi-check-bold" size="large" class="rm-4"></v-icon>
              {{ item.UpdateVersion }}
            </div>
          </template>

          <template v-slot:item.vulnerabilities="{ item }">
            <div v-if="
              item.Vulnerabilities.length > 0 &&
              item.Wporg &&
              !item.WpJuggler
            ">
              <v-icon color="error" icon="mdi-bug-check-outline" size="large" class="mr-1"></v-icon>
              {{ item.Vulnerabilities.length }}
              <v-btn class="ml-3 text-none text-caption" @click="openVulnerabilities(item)">Details
              </v-btn>
            </div>
            <div v-else-if="!item.Wporg || item.WpJuggler">
              <v-icon color="blue-lighten-5" icon="mdi-help" size="large" class="rm-4"></v-icon>
            </div>
          </template>

          <template v-slot:item.checksum="{ item }">
            <div v-if="!item.Checksum && !item.WpJuggler && item.Wporg">
              <v-icon color="error" icon="mdi-alert-outline" size="large" class="mr-1"></v-icon>
            </div>
            <div v-else-if="!item.Wporg || item.WpJuggler">
              <v-icon color="blue-lighten-5" icon="mdi-help" size="large" class="rm-4"></v-icon>
            </div>
            <div v-else>
              <v-icon color="success" icon="mdi-check-bold" size="large" class="rm-4"></v-icon>
            </div>
          </template>

          <template v-slot:item.source="{ item }">
            <div v-if="item.Tgmpa">
              <v-icon color="grey-lighten-1" icon="mdi-package-variant-closed" size="large" class="rm-4"></v-icon>
            </div>
            <div v-else-if="item.WpJuggler">
              <v-icon color="grey-lighten-1" icon="mdi-lan" size="large" class="rm-4"></v-icon>
            </div>
            <div v-else-if="item.Wporg">
              <v-icon color="grey-lighten-1" icon="mdi-wordpress" size="large" class="mr-1"></v-icon>
            </div>
            <div v-else>
              <v-icon color="blue-lighten-5" icon="mdi-help" size="large" class="rm-4"></v-icon>
            </div>
          </template>
          <template v-slot:item.actions="{ item }">
            <v-btn v-if="item.Active || item.NetworkActive" :loading="item.Slug == deactivateActive"
              @click="deactivatePlugin(item.Slug)" class="ml-3 text-none text-caption">Deactivate
            </v-btn>
            <v-btn v-if="!item.Active && !item.Multisite" :loading="item.Slug == activateActive"
              @click="activatePlugin(item.Slug, false)" class="ml-3 text-none text-caption">Activate
            </v-btn>
            <v-btn v-if="!item.Active && !item.NetworkActive && item.Multisite && !item.Network"
              :loading="item.Slug == activateActive" @click="activatePlugin(item.Slug, false)"
              class="ml-3 text-none text-caption">Activate
            </v-btn>
            <v-btn v-if="!item.Active && !item.NetworkActive && item.Multisite && !item.Network"
              :loading="item.Slug == activateNetworkActive" @click="activatePlugin(item.Slug, true)"
              class="ml-3 text-none text-caption">Network Activate
            </v-btn>
            <v-btn v-if="!item.Active && !item.NetworkActive && item.Multisite && item.Network"
              :loading="item.Slug == activateNetworkActive" @click="activatePlugin(item.Slug, true)"
              class="ml-3 text-none text-caption">Network Activate
            </v-btn>
            <v-btn v-if="item.Update" :loading="item.Slug == updateActive" @click="updatePlugin(item.Slug)"
              color="#2196f3" variant="elevated" class="text-none text-caption ml-3">Update
            </v-btn>
          </template>
        </v-data-table>
      </v-sheet>

      <v-sheet v-if="true" class="align-left justify-left text-left mb-15">
        <div class="text-h6 mt-15">Failed Cron Events History:</div>
        <v-divider class="mb-10"></v-divider>

        <v-row class="wpjs-debug-table-row">
              <v-col class="text-left pl-5" cols="2">
                <strong>Time</strong>
              </v-col>
              <v-col class="text-left" cols="2">
                <strong>Site Name</strong>
              </v-col>
              <v-col class="text-left" cols="2">
                <strong>Site url</strong>
              </v-col>
              <v-col class="text-left" cols="2">
                <strong>Cron Type</strong>
              </v-col>
              <v-col class="text-left" cols="4">
                <strong>Error Message</strong>
              </v-col>
            </v-row>

        <v-infinite-scroll :height="600" :items="cron_history" :onLoad="loadDashboardHistory" style="overflow-x: hidden;">
         
          <template v-for="( item ) in cron_history" :key="item.ID">

            <v-row class="wpjs-debug-table-row align-center">
              <v-col class="text-left pl-5 " cols="2">
                {{ item.log_time }}
              </v-col>
              <v-col class="text-left" cols="2">
                {{ item.site_name }}
              </v-col>
              <v-col class="text-left" cols="2">
                {{ item.site_url }}
              </v-col>
              <v-col class="text-left" cols="2">
                {{ item.event_name }}
              </v-col>
              <v-col class="text-left" cols="4">
                {{ item.log_value }}
              </v-col>
            </v-row>


          </template>
        </v-infinite-scroll>
      </v-sheet>

      <v-sheet v-else class="align-left justify-left text-left px-5 mb-15">
        <div class="text-h6 mt-15">No Recorded Failed Cron Events</div>
      </v-sheet>

    </v-sheet>



  </v-card>

  <v-card class="pa-4 mr-4" v-else>
    <v-skeleton-loader type="table-tbody"> </v-skeleton-loader>
  </v-card>
</template>

<style>

.wpjs-debug-table-row {

&:nth-child(odd) {
  background-color: #f7f7f7;
}

}

</style>