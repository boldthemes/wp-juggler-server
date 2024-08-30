<script setup>
import { useWpjsStore } from "./store.js";
import { onMounted, computed, ref, BaseTransitionPropsValidators } from "vue";
import { useQueryClient, useQuery, useMutation } from "@tanstack/vue-query";

const store = useWpjsStore()

const props = defineProps(["columns", "item"])

const uptimePeriods = [
  { title: "Last 24 hours" },
  { title: "Last 7 days" },
  { title: "Last 30 days" },
  { title: "Last 3 months" },
];

const selectedUptimePeriod = ref(0)

function selectUptimePeriod( ind ){
  selectedUptimePeriod.value = ind
}

function openThemesPlugins( site ){
  store.activatedSite = site
  store.activatedThemes = true
}

const themesButton = ref(null)

</script>

<template>
  <tr>
    <td :colspan="props.columns?.length + 1 ">
        <div class="text-h5 font-weight-bold mt-5 mb-3">
          {{ props.item?.id}} {{ props.item?.title }}
        </div>

        <div class="text-h6 text-medium-emphasis font-weight-regular mb-5">
          <v-icon
            v-if="props.item.wp_juggler_multisite"
            color="#2196f3"
            icon="mdi-checkbox-multiple-blank-outline"
            size="large"
            class="rm-4 mr-4"
          ></v-icon>
          {{ props.item.wp_juggler_server_site_url }}
        </div>

        <v-row class="mb-4">
          <v-col cols="12" md="3">
            <v-card>
              <v-card-item title="Site Health">
                <template v-slot:subtitle>
                  <div v-if="props.item.wp_juggler_health_data_timestamp">
                    <v-icon
                      class="me-1 pb-1"
                      icon="mdi-refresh"
                      size="18"
                    ></v-icon>
                    {{ props.item.wp_juggler_health_data_timestamp }}
                  </div>
                  <div v-else>
                    <v-icon
                      class="me-1 pb-1"
                      icon="mdi-refresh"
                      size="18"
                    ></v-icon>
                    Never
                  </div>
                </template>
              </v-card-item>

              <v-card-text class="text-medium-emphasis">
                <div class="d-flex py-3 justify-space-between pt-0">
                  <div>
                    <div>Critical Improvements</div>
                    <v-row align="center" no-gutters>
                      <v-col class="text-h2" cols="12" v-if="props.item.wp_juggler_health_data"> {{ props.item.wp_juggler_health_data.critical }} </v-col>
                      <v-col class="text-h2" cols="12" v-else> ? </v-col>
                    </v-row>
                  </div>
                  <div>
                    <div>Recommended Improvements</div>
                    <v-row align="center" no-gutters>
                      <v-col class="text-h2" cols="12" v-if="props.item.wp_juggler_health_data"> {{ props.item.wp_juggler_health_data.recommended }} </v-col>
                      <v-col class="text-h2" cols="12" v-else> ? </v-col>
                    </v-row>
                  </div>
                </div>

                <div class="d-flex py-3 justify-space-between pt-0">
                  <div v-if="props.item.wp_juggler_wordpress_version">WordPress version: {{ props.item.wp_juggler_wordpress_version }}</div>
                  <div v-else>WordPress version: ?</div>
                  <div v-if="props.item.wp_juggler_core_checksum">
                    Checksum
                    <v-icon
                      color="success"
                      icon="mdi-check-bold"
                      size="large"
                      class="rm-4"
                      v-if="!props.item.wp_juggler_core_checksum.errors"
                    ></v-icon>
                    <v-icon
                      color="error"
                      icon="mdi-alert-outline"
                      size="large"
                      class="rm-4"
                      v-else
                    ></v-icon>
                    {{ props.item.wp_juggler_core_checksum_timestamp }}
                  </div>
                </div>
              </v-card-text>

              <v-divider></v-divider>

              <v-btn
                text="Full Report"
                append-icon="mdi-chevron-right"
                class="mb-5 ml-5 mt-4"
              ></v-btn>
            </v-card>
          </v-col>

          <v-col cols="12" md="3">
            <v-card>
              <div class="d-flex py-3 justify-space-between pb-0">
                <v-card-item title="Uptime Cron"> </v-card-item>

                <div class="mr-5">
                  <v-menu open-on-hover>
                    <template v-slot:activator="{ props }">
                      <v-btn v-bind="props" class="text-none text-caption">
                        {{ uptimePeriods[selectedUptimePeriod].title }}
                      </v-btn>
                    </template>

                    <v-list>
                      <v-list-item
                        v-for="(item, index) in uptimePeriods"
                        :key="index"
                        @click="selectUptimePeriod( index )"
                      >
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
                      <v-col class="text-h2" cols="12"> {{ item.wp_juggler_uptime_stats.summary[selectedUptimePeriod].front }} </v-col>
                    </v-row>
                  </div>
                  <div>
                    <div>Failed API checks</div>
                    <v-row align="center" no-gutters>
                      <v-col class="text-h2" cols="12"> {{ item.wp_juggler_uptime_stats.summary[selectedUptimePeriod].api }} </v-col>
                    </v-row>
                  </div>
                </div>

                <div>Total failed checks: {{ parseInt(item.wp_juggler_uptime_stats.summary[selectedUptimePeriod].front) + parseInt(item.wp_juggler_uptime_stats.summary[selectedUptimePeriod].api) }}</div>
              </v-card-text>

              <v-divider></v-divider>

              <v-btn
                text="Full Report"
                append-icon="mdi-chevron-right"
                class="mb-5 ml-5 mt-4"
              ></v-btn>
            </v-card>
          </v-col>

          <v-col cols="12" md="3">
            <v-card>
              <v-card-item title="Themes & Plugins">
                <template v-slot:subtitle>
                  <div v-if="props.item.wp_juggler_plugins_summary_timestamp">
                    <v-icon
                      class="me-1 pb-1"
                      icon="mdi-refresh"
                      size="18"
                    ></v-icon>
                    {{ props.item.wp_juggler_plugins_summary_timestamp }}
                  </div>
                  <div v-else>
                    <v-icon
                      class="me-1 pb-1"
                      icon="mdi-refresh"
                      size="18"
                    ></v-icon>
                    Never
                  </div>
                </template>
              </v-card-item>

              <v-card-text class="text-medium-emphasis">
                <div class="d-flex py-3 justify-space-between pt-0">
                  <div>
                    <div>Theme updates available</div>
                    <v-row align="center" no-gutters>
                      <v-col class="text-h2" cols="12" v-if="props.item?.wp_juggler_themes_summary"> {{ props.item.wp_juggler_themes_summary.updates_num }} </v-col>
                      <v-col class="text-h2" cols="12" v-else> ? </v-col>
                    </v-row>
                  </div>
                  <div>
                    <div>Plugin updates available</div>
                    <v-row align="center" no-gutters>
                      <v-col class="text-h2" cols="12" v-if="props.item?.wp_juggler_plugins_summary"> {{ props.item.wp_juggler_plugins_summary.updates_num }} </v-col>
                      <v-col class="text-h2" cols="12" v-else> ? </v-col>
                    </v-row>
                  </div>
                </div>
                <div class="d-flex py-3 justify-space-between pt-0">
                  <div v-if="props.item?.wp_juggler_plugins_summary">Recorded vulnerabilities: {{ props.item.wp_juggler_plugins_summary.vulnerabilities_num }}</div>
                  <div v-else>Recorded vulnerabilities: ?</div>
                  <div v-if="props.item.wp_juggler_plugins_checksum">
                    Checksum
                    <v-icon
                      color="success"
                      icon="mdi-check-bold"
                      size="large"
                      class="rm-4"
                      v-if="props.item.wp_juggler_plugins_checksum.failures == 0"
                    ></v-icon>
                    <v-icon
                      color="error"
                      icon="mdi-alert-outline"
                      size="large"
                      class="rm-4"
                      v-else
                    ></v-icon>
                    {{ props.item.wp_juggler_plugins_checksum_timestamp }}
                  </div>
                </div>
              </v-card-text>

              <v-divider></v-divider>

              <v-btn
                text="Manage Themes & Plugins"
                append-icon="mdi-chevron-right"
                class="mb-5 ml-5 mt-4"
                @click="openThemesPlugins( props.item )"
                ref="themesButton"
              ></v-btn>
            </v-card>
          </v-col>

          <v-col cols="12" md="3">
            <v-card>
              <div class="d-flex py-3 justify-space-between pb-0">
                <v-card-item title="Messages"> </v-card-item>

                <div class="mr-5">
                  <v-menu open-on-hover>
                    <template v-slot:activator="{ props }">
                      <v-btn v-bind="props" class="text-none text-caption">
                        Last 24 hours
                      </v-btn>
                    </template>

                    <v-list>
                      <v-list-item
                        v-for="(item, index) in uptimePeriods"
                        :key="index"
                      >
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
                      <v-col class="text-h2" cols="12"> 12 </v-col>
                    </v-row>
                  </div>
                </div>

                <div>Last message: 9 hours ago</div>
              </v-card-text>

              <v-divider></v-divider>

              <v-btn
                text="All Messages"
                append-icon="mdi-chevron-right"
                class="mb-5 ml-5 mt-4"
              ></v-btn>
            </v-card>
          </v-col>
        </v-row>
    </td>
  </tr>
</template>

<style>
.wpjs-cp-table td {
  padding: 15px 0px;
}
</style>
