<script setup>

import { useDirekttStore } from './store.js'
import { onMounted, computed, ref } from 'vue'
import { useQueryClient, useQuery, useMutation } from '@tanstack/vue-query'

const queryClient = useQueryClient()

const store = useDirekttStore()

const nonce = ref(direktt_dashboard_object.nonce)

const activation_status = ref(false)

const { isLoading, isError, isFetching, data, error, refetch } = useQuery({
  queryKey: ['direktt-dashboard'],
  queryFn: getDashboard
})

async function doAjax(args) {
  let result;
  try {
    result = await jQuery.ajax({
      url: direktt_dashboard_object.ajaxurl,
      type: 'POST',
      data: args
    });
    return result;
  } catch (error) {
    throw (error)
  }
}

async function getDashboard() {

  let ret = {}
  const response = await doAjax(
    {
      action: "direktt_get_dashboard",  // the action to fire in the server
    }
  )
  ret = response.data
  //api_key.value = response.data.api_key
  activation_status.value = (response.data.activation_status === 'true')
  return ret
}

const openInNewTab = (url) => {
  const newWindow = window.open(url, '_blank', 'noopener,noreferrer')
  if (newWindow) newWindow.opener = null
}

onMounted(() => {

})

</script>

<template>
   <h1>Direktt Dashboard</h1>

<v-card class="pa-4 mr-4">

  <table class="form-table" role="presentation">

    <tbody v-if="data">

      <tr>
        <th scope="row"><label for="blogname">QR Code for subscription:</label></th>
        <td>
          <div>
            <vue-qrcode :value="'https://direktt.io/?site=' + data.direktt_channel_id + '&name=' + encodeURIComponent(data.direktt_channel_title)" :options="{ width: 300 }"></vue-qrcode>
          </div>
        </td>
      </tr>

  
      <tr>
        <th scope="row"><label for="blogname">Activation status:</label></th>
        <td>
          <div v-if="!activation_status">
            <v-icon color="error" icon="mdi-alert-outline" size="large"class='rm-4'></v-icon>
            Not activated
          </div>
          <div v-if="activation_status">
            <v-icon color="success" icon="mdi-check-bold" size="large"class='rm-4'></v-icon>
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

</v-card>
</template>

<style></style>