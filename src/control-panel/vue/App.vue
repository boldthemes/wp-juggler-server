<script setup>

import { useWpjsStore } from './store.js'
import { onMounted, computed, ref } from 'vue'
import { useQueryClient, useQuery, useMutation } from '@tanstack/vue-query'

const queryClient = useQueryClient()

const store = useWpjsStore()

const nonce = ref(wpjs_control_panel_object.nonce)

const { isLoading, isError, isFetching, data, error, refetch } = useQuery({
  queryKey: ['wpjs-control-panel'],
  queryFn: getDashboard
})

async function doAjax(args) {
  let result;
  try {
    result = await jQuery.ajax({
      url: wpjs_control_panel_object.ajaxurl,
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
      action: "wpjs_get_control_panel",  // the action to fire in the server
    }
  )
  ret = response.data
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
   <h1>WP Juggler Control Panel</h1>

<v-card class="pa-4 mr-4">

  <table class="form-table" role="presentation">

    <tbody v-if="data">

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
        <th>
          <div>
            Activation Status
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
        <td>
          <div v-if="!item.wp_juggler_site_activation">
            <v-icon color="error" icon="mdi-alert-outline" size="large"class='rm-4'></v-icon>
            Not activated
          </div>
          <div v-if="item.wp_juggler_site_activation">
            <v-icon color="success" icon="mdi-check-bold" size="large"class='rm-4'></v-icon>
            Activated
          </div>
        </td>
      </tr>
    </tbody>
  </table>

</v-card>
</template>

<style></style>