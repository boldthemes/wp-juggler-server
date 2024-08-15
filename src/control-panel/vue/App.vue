<script setup>

import { useWpjsStore } from './store.js'
import { onMounted, computed, ref } from 'vue'
import { useQueryClient, useQuery, useMutation } from '@tanstack/vue-query'

const queryClient = useQueryClient()

const store = useWpjsStore()

const nonce = ref(wpjs_control_panel_object.nonce)

const search = ref("")

const headers = [
  { title: 'Title', value: 'title' },
  { title: 'Site URL', key: 'wp_juggler_server_site_url' },
  { title: 'Activation Status', key: 'activation' }
]

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

function backToDashboard() {
  window.location.href = wpjs_control_panel_object.adminurl
}

onMounted(() => {

})

</script>

<template>

<v-btn color="#2271b1" variant="flat" class="text-none text-caption" @click="backToDashboard">Back to Dashboard</v-btn>
<v-spacer></v-spacer>

<v-card class="pa-4 mr-4 mt-5 mb-5">
  
  <v-card flat>
    
    <v-card-title class="d-flex align-center pe-2 mb-6">
      <v-icon icon="mdi-video-input-component"></v-icon> &nbsp;
      WP Juggler Control Panel

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
    <v-data-table v-model:search="search" :items="data" :headers="headers" item-key="id" show-select>
      <template v-slot:item.activation="{ item }">
        <div v-if="!item.wp_juggler_site_activation">
            <v-icon color="error" icon="mdi-alert-outline" size="large"class='rm-4'></v-icon>
            Not activated
          </div>
          <div v-if="item.wp_juggler_site_activation">
            <v-icon color="success" icon="mdi-check-bold" size="large"class='rm-4'></v-icon>
            Activated
          </div>
      </template>

     
    </v-data-table>
  </v-card>

</v-card>
<v-btn color="#2271b1" variant="flat" class="text-none text-caption" @click="backToDashboard">Back to Dashboard</v-btn>
</template>

<style>
.wpjs-cp-table td{
   padding: 15px 0px;
}
</style>