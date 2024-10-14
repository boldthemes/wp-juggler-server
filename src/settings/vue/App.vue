<script setup>

import { useWpjsStore } from './store.js'
import { onMounted, computed, ref, watch } from 'vue'
import { useQueryClient, useQuery, useMutation } from '@tanstack/vue-query'

const queryClient = useQueryClient()

const store = useWpjsStore()

const nonce = ref(wpjs_settings_object.nonce)

const short_schedules = {
    'wpjs_5min': 'Once every 5 minutes',
    'wpjs_10min': 'Once every 10 minutes',
    'wpjs_30min': 'Once every 30 minutes',
    'wpjs_hourly': 'Once Hourly'
}

const long_schedules = {
    'wpjs_twicedaily': 'Twice Daily',
    'wpjs_daily': 'Once Daily',
    'wpjs_weekly': 'Once Weekly'
  }

const wpjs_uptime_cron_interval = ref('')
const wpjs_health_cron_interval = ref('')
const wpjs_debug_cron_interval = ref('')
const wpjs_core_checksum_cron_interval = ref('')
const wpjs_plugins_cron_interval = ref('')
const wpjs_plugins_checksum_cron_interval = ref('')
const wpjs_notices_cron_interval = ref('')

const save_loading = ref(false)

const snackbar = ref(false)
const snackbar_color = ref('success')
const snackbar_text = ref(snack_succ_text)
const snack_succ_text = 'WP Juggler Settings Saved'

const wpjc_authorization = ref('Checking...');


const { isLoading, isError, isFetching, data, error, refetch } = useQuery({
  queryKey: ['wpjs-settings'],
  queryFn: getSettings,
  refetchOnWindowFocus: false
})

const mutation = useMutation({
  mutationFn: saveSettings,
  onSuccess: async () => {
    // Invalidate and refetch
    queryClient.invalidateQueries({ queryKey: ['wpjs-settings'] })
    save_loading.value = false

    snackbar_color.value = 'success'
    snackbar_text.value = snack_succ_text
    snackbar.value = true
  },
  onError: (error, variables, context) => {
    queryClient.invalidateQueries({ queryKey: ['wpjs-settings'] })
    save_loading.value = false

    snackbar_color.value = 'error'
    snackbar_text.value = error.responseJSON.data[0].message
    snackbar.value = true
  },
})

async function doAjax(args) {
  let result;
  try {
    result = await jQuery.ajax({
      url: wpjs_settings_object.ajaxurl,
      type: 'POST',
      data: args
    });
    return result;
  } catch (error) {
    throw (error)
  }
}

async function getSettings() {

  let ret = {}
  const response = await doAjax(
    {
      action: "wpjs_get_settings",  // the action to fire in the server
    }
  )
  ret = response.data

  wpjs_uptime_cron_interval.value = response.data.wpjs_uptime_cron_interval
  wpjs_health_cron_interval.value = response.data.wpjs_health_cron_interval
  wpjs_debug_cron_interval.value = response.data.wpjs_debug_cron_interval
  wpjs_core_checksum_cron_interval.value = response.data.wpjs_core_checksum_cron_interval
  wpjs_plugins_cron_interval.value = response.data.wpjs_plugins_cron_interval
  wpjs_plugins_checksum_cron_interval.value = response.data.wpjs_plugins_checksum_cron_interval 
  wpjs_notices_cron_interval.value = response.data.wpjs_notices_cron_interval

  return ret
}

function clickSaveSettings() {
  save_loading.value = true
  mutation.mutate({
    wpjs_uptime_cron_interval: wpjs_uptime_cron_interval.value,
    wpjs_health_cron_interval: wpjs_health_cron_interval.value,
    wpjs_debug_cron_interval: wpjs_debug_cron_interval.value,
    wpjs_core_checksum_cron_interval: wpjs_core_checksum_cron_interval.value,
    wpjs_plugins_cron_interval: wpjs_plugins_cron_interval.value,
    wpjs_plugins_checksum_cron_interval: wpjs_plugins_checksum_cron_interval.value,
    wpjs_notices_cron_interval: wpjs_notices_cron_interval.value
  })

}

async function saveSettings(obj) {

  obj.action = "wpjs_save_settings"
  obj.nonce = nonce.value

  const response = await doAjax(obj)
}

async function checkHeaders(url, api_key) {

let result;
try {
  result = await jQuery.ajax({
    url: url,
    type: 'POST',
    data: [],
    beforeSend: function (xhr) {
      xhr.setRequestHeader('Authorization', 'Bearer ' + api_key);
    }
  })

  if (result.data.headers_passed) {
    wpjc_authorization.value = 'Passed'
  } else {
    wpjc_authorization.value = 'Failed'
  }

} catch (error) {
  wpjc_authorization.value = 'Failed'
}
}

watch(data, async (newData, oldData) => {
  wpjc_authorization.value = 'Checking...'
  await (checkHeaders(wpjs_settings_object.resturl + 'juggler/v1/checkHeaders', wpjs_settings_object.checktoken))
})

onMounted(() => {
  console.log(wpjs_settings_object.nonce)
})


</script>

<template>

  <h1>WP Juggler Server Settings</h1>

  <v-card class="pa-4 mr-4" v-if="data">

    <table class="form-table" role="presentation">

      <tbody>
        <tr>
          <th scope="row"><label for="wpjs_uptime_cron_interval">Uptime Cron Interval</label></th>
          <td>
            <select name="wpjs_uptime_cron_interval" id="wpjs_uptime_cron_interval" v-model="wpjs_uptime_cron_interval" >
              <option v-for=" item, slug in short_schedules" :value="slug">{{ item }}</option>
            </select>
          </td>
        </tr>

        <tr>
          <th scope="row"><label for="wpjs_health_cron_interval">Site Health Cron Interval</label></th>
          <td>
            <select name="wpjs_health_cron_interval" id="wpjs_health_cron_interval" v-model="wpjs_health_cron_interval" >
              <option v-for=" item, slug in long_schedules" :value="slug">{{ item }}</option>
            </select>
          </td>
        </tr>

        <tr>
          <th scope="row"><label for="wpjs_debug_cron_interval">Debug Info Cron Interval</label></th>
          <td>
            <select name="wpjs_debug_cron_interval" id="wpjs_debug_cron_interval" v-model="wpjs_debug_cron_interval" >
              <option v-for=" item, slug in long_schedules" :value="slug">{{ item }}</option>
            </select>
          </td>
        </tr>

        <tr>
          <th scope="row"><label for="wpjs_core_checksum_cron_interval">Core Checksum Cron Interval</label></th>
          <td>
            <select name="wpjs_core_checksum_cron_interval" id="wpjs_core_checksum_cron_interval" v-model="wpjs_core_checksum_cron_interval" >
              <option v-for=" item, slug in long_schedules" :value="slug">{{ item }}</option>
            </select>
          </td>
        </tr>

        <tr>
          <th scope="row"><label for="wpjs_plugins_cron_interval">Plugins and Themes Cron Interval</label></th>
          <td>
            <select name="wpjs_plugins_cron_interval" id="wpjs_plugins_cron_interval" v-model="wpjs_plugins_cron_interval" >
              <option v-for=" item, slug in long_schedules" :value="slug">{{ item }}</option>
            </select>
          </td>
        </tr>

        <tr>
          <th scope="row"><label for="wpjs_plugins_checksum_cron_interval">Plugins and Themes Checksum Cron Interval</label></th>
          <td>
            <select name="wpjs_plugins_checksum_cron_interval" id="wpjs_plugins_checksum_cron_interval" v-model="wpjs_plugins_checksum_cron_interval" >
              <option v-for=" item, slug in long_schedules" :value="slug">{{ item }}</option>
            </select>
          </td>
        </tr>

        <tr>
          <th scope="row"><label for="wpjs_notices_cron_interval">Notices Cron Interval</label></th>
          <td>
            <select name="wpjs_notices_cron_interval" id="wpjs_notices_cron_interval" v-model="wpjs_notices_cron_interval" >
              <option v-for=" item, slug in long_schedules" :value="slug">{{ item }}</option>
            </select>
          </td>
        </tr>

        <tr>
          <th scope="row"><label for="server url">Auth Header Test</label></th>
          <td>
            {{ wpjc_authorization }} 
            <v-icon color="success" icon="mdi-check-bold" size="large" class="mr-1"
              v-if="wpjc_authorization == 'Passed'"></v-icon>
            <v-icon color="error" icon="mdi-alert-outline" size="large" class="mr-1"
              v-if="wpjc_authorization == 'Failed'"></v-icon> 
            <div v-if="wpjc_authorization == 'Failed'">You need to enable PHP HTTP Authorization Header. <br>Please refer to plugin documentation for more information</div>
          </td>
        </tr>

      </tbody>
    </table>
    <p></p>
    <v-divider class="border-opacity-100"></v-divider>

    <p></p>

    <v-btn variant="flat" class="text-none text-caption" color="#2271b1" @click="clickSaveSettings"
      :loading="save_loading">
      Save Settings
    </v-btn>

    <v-snackbar v-model="snackbar" :timeout="3000" :color="snackbar_color">
      {{ snackbar_text }}
      <template v-slot:actions>
        <v-btn variant="text" @click="snackbar = false">
          X
        </v-btn>
      </template>
    </v-snackbar>

  </v-card>
  <v-card class="pa-4 mr-4" v-else>
    <v-skeleton-loader type="table-tbody" > </v-skeleton-loader>
  </v-card>
</template>

<style>

select {
  border-style: solid !important;
}

#app .form-table th {
  width: 350px !important;
}

</style>