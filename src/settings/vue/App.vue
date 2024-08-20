<script setup>

import { useWpjsStore } from './store.js'
import { onMounted, computed, ref } from 'vue'
import { useQueryClient, useQuery, useMutation } from '@tanstack/vue-query'

const queryClient = useQueryClient()

const store = useWpjsStore()

const nonce = ref(wpjs_settings_object.nonce)

const wpjs_cp_slug = ref('')
const wpjs_uptime_cron_interval = ref('')
const wpjs_health_cron_interval = ref('')
const wpjs_plugins_cron_interval = ref('')
const wpjs_checksum_cron_interval = ref('')

const save_loading = ref(false)

const snackbar = ref(false)
const snackbar_color = ref('success')
const snackbar_text = ref(snack_succ_text)
const snack_succ_text = 'WP Juggler Settings Saved'


const { isLoading, isError, isFetching, data, error, refetch } = useQuery({
  queryKey: ['wpjs-settings'],
  queryFn: getSettings
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

  wpjs_cp_slug.value = response.data.wpjs_cp_slug
  wpjs_uptime_cron_interval.value = response.data.wpjs_uptime_cron_interval
  wpjs_health_cron_interval.value = response.data.wpjs_health_cron_interval
  wpjs_plugins_cron_interval.value = response.data.wpjs_plugins_cron_interval
  wpjs_checksum_cron_interval.value = response.data.wpjs_checksum_cron_interval

  return ret
}

function clickSaveSettings() {
  save_loading.value = true
  mutation.mutate({
    wpjs_cp_slug: wpjs_cp_slug.value,
    wpjs_uptime_cron_interval: wpjs_uptime_cron_interval.value,
    wpjs_health_cron_interval: wpjs_health_cron_interval.value,
    wpjs_plugins_cron_interval: wpjs_plugins_cron_interval.value,
    wpjs_checksum_cron_interval: wpjs_checksum_cron_interval.value
  })

}

async function saveSettings(obj) {

  obj.action = "wpjs_save_settings"
  obj.nonce = nonce.value

  const response = await doAjax(obj)
}

</script>

<template>

  <h1>WP Juggler Server Settings</h1>

  <v-card class="pa-4 mr-4">

    <table class="form-table" role="presentation">

      <tbody v-if="data">
        <tr>
          <th scope="row"><label for="wpjscpslug">Page Slug of Control Panel</label></th>
          <td>
            <input type="text" name="wpjscpslug" id="wpjscpslug" size="50" placeholder="" v-model="wpjs_cp_slug">
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="wpjs_uptime_cron_interval">Uptime Cron Interval</label></th>
          <td>
            <select name="wpjs_uptime_cron_interval" id="wpjs_uptime_cron_interval" v-model="wpjs_uptime_cron_interval" >
              <option value="wpjs_5min">Once every 5 minutes</option>
              <option value="wpjs_10min">Once every 10 minutes</option>
              <option value="wpjs_30min">Once every 30 minutes</option>
              <option value="hourly">Once Hourly</option>
            </select>
          </td>
        </tr>

        <tr>
          <th scope="row"><label for="wpjs_health_cron_interval">Site Health Cron Interval</label></th>
          <td>
            <select name="wpjs_health_cron_interval" id="wpjs_health_cron_interval" v-model="wpjs_health_cron_interval" >
              <option value="twicedaily">Twice Daily</option>
              <option value="daily">Once Daily</option>
              <option value="weekly">Once Weekly</option>
            </select>
          </td>
        </tr>

        <tr>
          <th scope="row"><label for="wpjs_plugins_cron_interval">Plugins and Themes Cron Interval</label></th>
          <td>
            <select name="wpjs_plugins_cron_interval" id="wpjs_plugins_cron_interval" v-model="wpjs_plugins_cron_interval" >
              <option value="twicedaily">Twice Daily</option>
              <option value="daily">Once Daily</option>
              <option value="weekly">Once Weekly</option>
            </select>
          </td>
        </tr>

        <tr>
          <th scope="row"><label for="wpjs_checksum_cron_interval">Checksum Cron Interval</label></th>
          <td>
            <select name="wpjs_checksum_cron_interval" id="wpjs_checksum_cron_interval" v-model="wpjs_checksum_cron_interval" >
              <option value="twicedaily">Twice Daily</option>
              <option value="daily">Once Daily</option>
              <option value="weekly">Once Weekly</option>
            </select>
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
</template>

<style>

select {
  border-style: solid !important;
}

</style>