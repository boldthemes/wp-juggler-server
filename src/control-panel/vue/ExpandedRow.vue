<script setup>

import { useWpjsStore } from './store.js'
import { onMounted, computed, ref } from 'vue'
import { useQueryClient, useQuery, useMutation } from '@tanstack/vue-query'

const props = defineProps(["columns", "item"]);

const menu_items = [
  { title: 'Click Me' },
  { title: 'Click Me' },
  { title: 'Click Me' },
  { title: 'Click Me 2' },
]

</script>

<template>

  <tr>
    <td :colspan="props.columns?.length">
      <v-container>

        <div class="text-h5 font-weight-bold mt-5 mb-3">{{ props.item?.title }}</div>

        <div class="text-h6 text-medium-emphasis font-weight-regular mb-5">
          <v-icon v-if="props.item.wp_juggler_multisite" color="#2196f3" icon="mdi-checkbox-multiple-blank-outline" size="large" class='rm-4 mr-4'></v-icon>
          {{ props.item.wp_juggler_server_site_url }}
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
                  <div>Checksum <v-icon color="success" icon="mdi-check-bold" size="large" class='rm-4'></v-icon> 12
                    hours ago</div>
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
                  <div>Checksum <v-icon color="success" icon="mdi-check-bold" size="large" class='rm-4'></v-icon> 12
                    hours ago
                  </div>
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

<style>
.wpjs-cp-table td {
  padding: 15px 0px;
}
</style>