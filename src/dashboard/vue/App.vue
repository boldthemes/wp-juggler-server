<script setup>

import { useWpjsStore } from './store.js'
import { onMounted, computed, ref } from 'vue'
import { useQueryClient, useQuery, useMutation } from '@tanstack/vue-query'

const queryClient = useQueryClient()

const store = useWpjsStore()

const nonce = ref(wpjs_dashboard_object.nonce)

const activation_status = ref(false)

const { isLoading, isError, isFetching, data, error, refetch } = useQuery({
  queryKey: ['wpjs-dashboard'],
  queryFn: getDashboard
})

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

async function getDashboard() {

  let ret = {}
  const response = await doAjax(
    {
      action: "wpjs_get_dashboard",  // the action to fire in the server
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
   <h1>WP Juggler Server Dashboard</h1>

<v-card class="pa-4 mr-4"  v-if="data">

  <table class="form-table" role="presentation">

    <tbody>

      <tr>
        <th>
          <div>
            Title
          </div>
        </th>
        <th>
          <div>
            Url
          </div>
        </th>
      </tr>

      <tr v-for="item in data">
        <td>
          <div>
            {{ item.title }}
          </div>
        </td>
        <td>
          <div>
            {{ item.wp_juggler_server_site_url }}
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
            dsadasdas
          </div>
        </td>
      </tr>
      <tr v-if="activation_status">
        <th scope="row"><label for="blogname">Channel Id:</label></th>
        <td>
          <div v-if="activation_status">
            dasdasdas
          </div>
        </td>
      </tr>
      <tr v-if="activation_status">
        <th scope="row"><label for="blogname">Channel title:</label></th>
        <td>
          <div v-if="activation_status">
           dsadsadasadsa
          </div>
        </td>
      </tr>

    </tbody>
  </table>

</v-card>

<v-card class="pa-4 mr-4" v-else>
    <v-skeleton-loader type="table-tbody" > </v-skeleton-loader>
  </v-card>
</template>

<style></style>