<script setup>

import { useWpjsStore } from './store.js'
import { onMounted, computed, ref } from 'vue'
import { useQueryClient, useQuery, useMutation } from '@tanstack/vue-query'

const queryClient = useQueryClient()

const store = useWpjsStore()

const nonce = ref(wpjs_control_panel_object.nonce)

const search = ref("")

const menu_items = [
  { title: 'Click Me' },
  { title: 'Click Me' },
  { title: 'Click Me' },
  { title: 'Click Me 2' },
]

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

                <div class="text-h5 font-weight-bold mt-5 mb-3">{{ item.title }}</div>

                <div class="text-h6 text-medium-emphasis font-weight-regular mb-5">
                  <v-icon color="#2196f3" icon="mdi-checkbox-multiple-blank-outline" size="large"
                    class='rm-4 mr-4'></v-icon>{{ item.wp_juggler_server_site_url }}
                </div>

                <v-row class="mb-4">

                  <v-col cols="12" md="3">

                    <v-card>
                      <v-card-item title="Site Health">
                        <template v-slot:subtitle>
                          <v-icon class="me-1 pb-1" icon="mdi-refresh" size="18"></v-icon>

                          12 hours ago
                        </template>
                      </v-card-item>

                      <v-card-text class="text-medium-emphasis">
                        <div>Number of recommended improvements</div>
                        <v-row align="center" no-gutters>
                          <v-col class="text-h2" cols="12">
                            0
                          </v-col>
                        </v-row>
                        <div class="d-flex py-3 justify-space-between">
                          <div>WordPress version: 6.31</div>
                          <div>Checksum <v-icon color="success" icon="mdi-check-bold" size="large"
                              class='rm-4'></v-icon> 12 hours ago</div>
                        </div>
                      </v-card-text>

                      <v-divider></v-divider>

                      <v-btn text="Full Report" append-icon="mdi-chevron-right" class="mb-5 ml-5"></v-btn>

                    </v-card>

                  </v-col>

                  <v-col cols="12" md="3">

                    <v-card>
                      <div class="d-flex py-3 justify-space-between pb-0">
                        
                        <v-card-item title="Uptime Cron">
                        </v-card-item>

                        <div class="mr-5">
                          <v-menu open-on-hover>
                            <template v-slot:activator="{ props }">
                              <v-btn v-bind="props" class="text-none text-caption">
                                Last 24 hours
                              </v-btn>
                            </template>

                            <v-list>
                              <v-list-item v-for="(item, index) in menu_items" :key="index">
                                <v-list-item-title>{{ item.title }}</v-list-item-title>
                              </v-list-item>
                            </v-list>
                          </v-menu>
                        </div>
                      </div>

                      <v-card-text class="text-medium-emphasis pt-0">

                        <div class="d-flex py-3 justify-space-between">
                          <div>
                            <div>Failed frontend checks</div>
                            <v-row align="center" no-gutters>
                              <v-col class="text-h2" cols="12">
                                12
                              </v-col>
                            </v-row>
                          </div>
                          <div>
                            <div>Failed API checks</div>
                            <v-row align="center" no-gutters>
                              <v-col class="text-h2" cols="12">
                                21
                              </v-col>
                            </v-row>
                          </div>
                        </div>
                        
                          <div>Uptime percetige: 99.54%</div>
                        
                      </v-card-text>

                      <v-divider></v-divider>

                      <v-btn text="Full Report" append-icon="mdi-chevron-right" class="mb-5 ml-5 mt-4"></v-btn>

                    </v-card>

                  </v-col>

                  <v-col cols="12" md="3">

                    <v-card>
                      <v-card-item title="Themes & Plugins">
                        <template v-slot:subtitle>
                          <v-icon class="me-1 pb-1" icon="mdi-refresh" size="18"></v-icon>

                          12 hours ago
                        </template>

                      </v-card-item>

                      <v-card-text class="text-medium-emphasis">
                        <div class="d-flex py-3 justify-space-between pt-0">
                          <div>
                            <div>Theme updates available</div>
                            <v-row align="center" no-gutters>
                              <v-col class="text-h2" cols="12">
                                3
                              </v-col>
                            </v-row>
                          </div>
                          <div>
                            <div>Plugin updates available</div>
                            <v-row align="center" no-gutters>
                              <v-col class="text-h2" cols="12">
                                21
                              </v-col>
                            </v-row>
                          </div>
                        </div>
                        <div class="d-flex py-3 justify-space-between pt-0">
                          <div>Recorded vulnerabilities: No</div>
                          <div>Checksum <v-icon color="success" icon="mdi-check-bold" size="large"
                            class='rm-4'></v-icon> 12 hours ago</div>
                        </div>
                      </v-card-text>

                      <v-divider></v-divider>

                      <v-btn text="Manage Themes & Plugins" append-icon="mdi-chevron-right" class="mb-5 ml-5"></v-btn>

                    </v-card>

                  </v-col>

                  <v-col cols="12" md="3">

                    <v-card>
                      <div class="d-flex py-3 justify-space-between pb-0">
                        
                        <v-card-item title="Messages">
                        </v-card-item>

                        <div class="mr-5">
                          <v-menu open-on-hover>
                            <template v-slot:activator="{ props }">
                              <v-btn v-bind="props" class="text-none text-caption">
                                Last 24 hours
                              </v-btn>
                            </template>

                            <v-list>
                              <v-list-item v-for="(item, index) in menu_items" :key="index">
                                <v-list-item-title>{{ item.title }}</v-list-item-title>
                              </v-list-item>
                            </v-list>
                          </v-menu>
                        </div>
                      </div>

                      <v-card-text class="text-medium-emphasis pt-0">

                        <div class="d-flex py-3 justify-space-between">
                          <div>
                            <div>Number of messages</div>
                            <v-row align="center" no-gutters>
                              <v-col class="text-h2" cols="12">
                                12
                              </v-col>
                            </v-row>
                          </div>
                          
                        </div>
                        
                          <div>Last message: 9 hours ago</div>
                        
                      </v-card-text>

                      <v-divider></v-divider>

                      <v-btn text="All Messages" append-icon="mdi-chevron-right" class="mb-5 ml-5 mt-4"></v-btn>

                    </v-card>

                  </v-col>


                </v-row>
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