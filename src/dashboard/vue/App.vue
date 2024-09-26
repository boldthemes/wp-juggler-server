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

const runCronActive = ref('') 

const ajaxError = ref(false);
const ajaxErrorText = ref('');

const { __, _x, _n, sprintf } = wp.i18n;

let infiniteScrollEvents

const { isLoading, isError, isFetching, data, error, refetch } = useQuery({
  queryKey: ['wpjs-dashboard'],
  queryFn: getDashboard
})

const dashboard_headers = [
  { title: "Event Name", value: "event_name", align: "start", sortable: true },
  { title: "Next Run", value: "next_run", align: "start", sortable: true },
  { title: "Schedule", value: "frequency", align: "start", sortable: true },
  { title: "Actions", key:"actions", align: "center", sortable: false}
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

async function startCron(hookSlug) {
  console.log(hookSlug);
  runCronActive.value = hookSlug

  let ret = {};

  try {
    const response = await doAjax({
      action: "wpjs-start-cron", // the action to fire in the server
      hookSlug: hookSlug,
    });

    console.log(response);

    if (response.success) {
      ret = response.data;

      //Ovde ispisati succ

      runCronActive.value = false;
    } else {
      throw new Error(`${response.data.code} - ${response.data.message}`);
    }
  } catch (error) {
    console.log(error);
    ajaxError.value = true;
    ajaxErrorText.value = error.message;
    runCronActive.value = false;
  }
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
  <h1>{{ __( 'WP Juggler Server Dashboard', 'wp-juggler-server' ) }}</h1>

  <v-card class="pa-4 mr-4" v-if="data">

    <v-sheet class="align-left justify-left text-left mx-auto mt-4 px-4 pb-4 mb-10">
      <div class="text-h6">{{ __( 'WP Juggler Cron Events', 'wp-juggler-server' ) }}</div>
      <v-divider class="mb-10"></v-divider>

      <v-sheet>
        <v-spacer></v-spacer>

        <v-data-table :items="cron_data" :headers="dashboard_headers" item-key="index" hide-default-footer>
          
          <template v-slot:item.actions="{ item }">
            <v-btn :loading="item.hook_slug == runCronActive"
              @click="startCron(item.hook_slug)" class="ml-3 text-none text-caption" variant="outlined">{{ __( 'Run Cron', 'wp-juggler-server' ) }}

            </v-btn>
          </template>
        </v-data-table>
      </v-sheet>

      <v-sheet v-if="true" class="align-left justify-left text-left mb-15">
        <div class="text-h6 mt-15">{{ __( 'Failed Cron Events History:', 'wp-juggler-server' ) }}</div>
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

  <v-snackbar v-model="ajaxError" color="red-lighten-2">
        {{ ajaxErrorText }}

        <template v-slot:actions>
          <v-btn
            color="red-lighten-4"
            variant="text"
            @click="ajaxError = false"
          >
            Close
          </v-btn>
        </template>
      </v-snackbar>
</template>

<style>

.wpjs-debug-table-row {

&:nth-child(odd) {
  background-color: #f7f7f7;
}

}

</style>