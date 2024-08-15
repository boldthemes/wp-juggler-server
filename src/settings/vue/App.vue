<script setup>

import { useDirekttStore } from './store.js'
import { onMounted, computed, ref } from 'vue'
import { useQueryClient, useQuery, useMutation } from '@tanstack/vue-query'

const queryClient = useQueryClient()

const store = useDirekttStore()

const nonce = ref(wpjs_settings_object.nonce)

const api_key = ref('')
const redirect_url = ref('')
const activation_status = ref(false)
const save_loading = ref(false)

const snackbar = ref(false)
const snackbar_color = ref('success')
const snackbar_text = ref(snack_succ_text)
const snack_succ_text = 'WP Juggler Settings Saved'


const { isLoading, isError, isFetching, data, error, refetch } = useQuery({
  queryKey: ['direktt-settings'],
  queryFn: getSettings
})

const mutation = useMutation({
  mutationFn: saveSettings,
  onSuccess: async () => {
    // Invalidate and refetch
    queryClient.invalidateQueries({ queryKey: ['direktt-settings'] })
    save_loading.value = false

    snackbar_color.value = 'success'
    snackbar_text.value = snack_succ_text
    snackbar.value = true
  },
  onError: (error, variables, context) => {
    queryClient.invalidateQueries({ queryKey: ['direktt-settings'] })
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

  api_key.value = response.data.api_key
  activation_status.value = (response.data.activation_status === 'true')
  redirect_url.value = response.data.redirect_url

  return ret
}

function clickSaveSettings() {
  save_loading.value = true
  mutation.mutate({
    api_key: api_key.value,
    redirect_url: redirect_url.value
  })
}

async function saveSettings(obj) {

  obj.action = "wpjs_save_settings"
  obj.nonce = nonce.value

  const response = await doAjax(obj)
}

const openInNewTab = (url) => {
  const newWindow = window.open(url, '_blank', 'noopener,noreferrer')
  if (newWindow) newWindow.opener = null
}

onMounted(() => {
})

</script>

<template>

  <h1>Direktt Settings</h1>

  <v-card class="pa-4 mr-4">

    <table class="form-table" role="presentation">

      <tbody v-if="data">
        <tr>
          <th scope="row"><label for="blogname">Direktt API Key</label></th>
          <td>
            <input type="text" name="direkttapikey" id="direkttapikey" size="50" placeholder="" v-model="api_key">
          </td>
        </tr>

        <tr>
          <th scope="row"><label for="blogname">Activation status:</label></th>
          <td>
            <div v-if="!activation_status">
              <v-icon color="error" icon="mdi-alert-outline" size="large" class='rm-4'></v-icon>
              Not activated
            </div>
            <div v-if="activation_status">
              <v-icon color="success" icon="mdi-check-bold" size="large" class='rm-4'></v-icon>
              Activated
            </div>
          </td>
        </tr>

        <tr v-if="activation_status">
          <th scope="row"><label for="blogname">Registered domain:</label></th>
          <td>
            <div v-if="activation_status">
              {{ data.direktt_registered_domain }}
            </div>
          </td>
        </tr>
        <tr v-if="activation_status">
          <th scope="row"><label for="blogname">Channel Id:</label></th>
          <td>
            <div v-if="activation_status">
              {{ data.direktt_channel_id }}
            </div>
          </td>
        </tr>
        <tr v-if="activation_status">
          <th scope="row"><label for="blogname">Channel title:</label></th>
          <td>
            <div v-if="activation_status">
              {{ data.direktt_channel_title }}
            </div>
          </td>
        </tr>

      </tbody>
    </table>
    <p></p>
    <v-divider class="border-opacity-100"></v-divider>
    <p></p>
    <table class="form-table" role="presentation">

      <tbody v-if="data">
        <tr>
          <th scope="row"><label for="blogname">Optional redirect url upon unaturhorized access</label></th>
          <td>
            <input type="text" name="unauthorized_redirect_url" id="unauthorized_redirect_url" size="50" placeholder="" v-model="redirect_url">
          </td>
        </tr>
      </tbody>
    </table>

    <p></p>

    <v-btn variant="flat" class="text-none text-caption" color="#2271b1" @click="clickSaveSettings"
      :loading="save_loading">
      Save Direktt Settings
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

<style></style>