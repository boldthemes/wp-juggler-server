<script setup>

import { useWpjsStore } from './store.js'
import { onMounted, computed, ref } from 'vue'
import { useQueryClient, useQuery, useMutation } from '@tanstack/vue-query'

const queryClient = useQueryClient()

const store = useWpjsStore()

const nonce = ref(wpjs_control_panel_object.nonce)

const search = ref("")

const expanded = ref([])
const headers = [
  { title: '', key: 'network', align: 'center', sortable: false },
  { title: 'Title', value: 'title', align: 'start', sortable: true },
  { title: 'Url', key: 'wp_juggler_server_site_url', align: 'start', sortable: true },
  { title: 'Messages', key: 'events', align: 'center', sortable: false },
  { title: 'Uptime', key: 'uptime', align: 'center', sortable: false },
  { title: 'Updates', key: 'updates', align: 'center', sortable: false },
  { title: 'Checksum', key: 'checksum', align: 'center', sortable: false },
  { title: 'Links', key: 'links', align: 'center', sortable: false },
  { title: 'WP admin', key: 'wp_admin', align: 'center', sortable: false },
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

const gotoLogin = (url) => {
  const newWindow = window.open(url, '_blank', 'noopener,noreferrer')
  if (newWindow) newWindow.opener = null
}

onMounted(() => {

})

</script>

<template>

  <v-btn color="#2196f3" variant="flat" class="text-none text-caption" @click="backToDashboard">Back to
    Dashboard</v-btn>
  <v-spacer></v-spacer>

  <v-card class="pa-4 mr-4 mt-5 mb-5">

    <v-card flat>

      <v-card-title class="d-flex align-center pe-2 mb-6">
        <v-icon icon="mdi-video-input-component"></v-icon> &nbsp;
        WP Juggler Control Panel

        <v-spacer></v-spacer>

        <v-text-field v-model="search" density="compact" label="Search" prepend-inner-icon="mdi-magnify"
          variant="solo-filled" flat hide-details single-line></v-text-field>

      </v-card-title>

      <v-divider></v-divider>
      <v-data-table v-model:search="search" :items="data" :headers="headers" item-key="id" show-select
        v-model:expanded="expanded" show-expand>

        <template v-slot:item.network="{ item }">
          <div v-if="item.wp_juggler_automatic_login">
            <v-icon color="#2196f3" icon="mdi-checkbox-multiple-blank-outline" size="large" class='rm-4'></v-icon>
          </div>
        </template>

        <template v-slot:item.events="{ item }">
          <div v-if="item.wp_juggler_automatic_login">
            <v-icon color="#2196f3" icon="mdi-email-alert-outline" size="large" class='rm-4'></v-icon>
          </div>
        </template>

        <template v-slot:item.uptime="{ item }">
          <div v-if="item.wp_juggler_site_activation">
            <v-icon color="success" icon="mdi-square" size="large" class='rm-4'></v-icon>
            <v-icon color="success" icon="mdi-square" size="large" class='rm-4'></v-icon>
            <v-icon color="error" icon="mdi-square" size="large" class='rm-4'></v-icon>
            <v-icon color="error" icon="mdi-square" size="large" class='rm-4'></v-icon>
            <v-icon color="success" icon="mdi-square" size="large" class='rm-4'></v-icon>
          </div>
          <div v-if="!item.wp_juggler_site_activation">
            Inactive
          </div>
        </template>

        <template v-slot:item.updates="{ item }">
          <div v-if="item.wp_juggler_automatic_login">
            <v-icon color="error" icon="mdi-bug-check-outline" size="large" class='rm-4'></v-icon>
          </div>
          <div v-if="!item.wp_juggler_site_activation">
            <v-icon color="error" icon="mdi-check-bold" size="large" class='rm-4'></v-icon>
          </div>
        </template>

        <template v-slot:item.checksum="{ item }">
          <div v-if="!item.wp_juggler_site_activation">
            <v-icon color="error" icon="mdi-alert-outline" size="large" class='rm-4'></v-icon>
          </div>
        </template>

        <template v-slot:item.links="{ item }">
          <div v-if="item.wp_juggler_automatic_login">
            <v-btn variant="elevated" class="text-none text-caption mr-2 ml-2">Maps Api Key</v-btn>
            <v-btn variant="elevated" class="text-none text-caption">GTM Setup</v-btn>
          </div>
        </template>

        <template v-slot:item.wp_admin="{ item }">
          <div v-if="item.wp_juggler_automatic_login">
            <v-btn color="#2196f3" variant="elevated" class="text-none text-caption" prepend-icon="mdi-login"
              @click="gotoLogin(item.wp_juggler_login_url)">Login</v-btn>
          </div>
          <div v-if="!item.wp_juggler_automatic_login">
            <v-btn color="#2196f3" variant="elevated" class="text-none text-caption" prepend-icon="mdi-account-remove"
              @click="gotoLogin(item.wp_juggler_login_url)">Login</v-btn>
          </div>
        </template>

        <template v-slot:expanded-row="{ columns, item }">
          <tr>
            <td :colspan="columns.length">
              <v-container>
                <v-card>
                  <v-card-title class="text-overline">
                    

                    <div class="text-h5 font-weight-bold">{{item.title}}</div>

                    <div class="text-h6 text-medium-emphasis font-weight-regular">
                      <v-icon color="#2196f3" icon="mdi-checkbox-multiple-blank-outline" size="large" class='rm-4 mr-4'></v-icon>{{item.wp_juggler_server_site_url}}
                    </div>
                  </v-card-title>
                  <br>
                  <v-card-text>
                    <div :style="`right: calc(${review} - 32px)`"
                      class="position-absolute mt-n8 text-caption text-green-darken-3">
                      Eligibility review
                    </div>
                    <v-progress-linear color="green-darken-3" height="22" model-value="90" rounded="lg">
                      <v-badge :style="`right: ${review}`" class="position-absolute" color="white" dot inline></v-badge>
                    </v-progress-linear>

                    <div class="d-flex justify-space-between py-3">
                      <span class="text-green-darken-3 font-weight-medium">
                        $26,442.00 remitted
                      </span>

                      <span class="text-medium-emphasis"> $29,380.00 total </span>
                    </div>
                  </v-card-text>

                  <v-divider></v-divider>

                  <v-list-item append-icon="mdi-chevron-right" lines="two" subtitle="Details and agreement"
                    link></v-list-item>
                </v-card>
              </v-container>
            </td>
          </tr>
        </template>

      </v-data-table>
    </v-card>

  </v-card>
  <v-btn color="#2196f3" variant="flat" class="text-none text-caption" @click="backToDashboard">Back to
    Dashboard</v-btn>
</template>

<style>
.wpjs-cp-table td {
  padding: 15px 0px;
}
</style>