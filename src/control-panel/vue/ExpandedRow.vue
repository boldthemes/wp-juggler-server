<script setup>
import { useWpjsStore } from "./store.js";
import { onMounted, computed, ref, BaseTransitionPropsValidators } from "vue";
import { useQueryClient, useQuery, useMutation } from "@tanstack/vue-query";

const store = useWpjsStore();

const props = defineProps(["columns", "item"]);

const uptimePeriods = [
  { title: "Last 24 hours" },
  { title: "Last 7 days" },
  { title: "Last 30 days" },
  { title: "Last 3 months" },
];

const selectedUptimePeriod = ref(0);

const ajaxError = ref(false);
const ajaxErrorText = ref("");

const refreshAllActive = ref(false);

function selectUptimePeriod(ind) {
  selectedUptimePeriod.value = ind;
}

function openThemesPlugins(site) {
  if (site.wp_juggler_site_activation) {
    store.activatedSite = site;
    store.activatedThemes = true;
  }
}

function openHealth(site) {
  if (site.wp_juggler_site_activation) {
    store.activatedSite = site;
    store.activatedHealth = true;
  }
}

function openUptime(site) {
  if (site.wp_juggler_site_activation) {
    store.activatedSite = site;
    store.activatedUptime = true;
  }
}
function openNotices(site) {
  if (site.wp_juggler_site_activation) {
    store.activatedSite = site;
    store.activatedNotices = true;
  }
}

const themesButton = ref(null);
const noticesButton = ref(null);

const queryClient = useQueryClient();

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

    return result;
  } catch (error) {
    throw new Error("No response from the WP Juggler Server");
  }
}

async function refreshPlugins() {
  let ret = {};

  try {
    const response = await doAjax({
      action: "wpjs-refresh-plugins", // the action to fire in the server
      siteId: props.item.id,
    });

    if (response.success) {
      ret = response.data;

    } else {
      throw new Error(`${response.data.code} - ${response.data.message}`);
    }
  } catch (error) {
    ajaxError.value = true;
    ajaxErrorText.value = error.message;
  }
}

async function refreshHealth() {
  let ret = {};

  try {
    const response = await doAjax({
      action: "wpjs-refresh-health", // the action to fire in the server
      siteId: props.item.id,
    });

    if (response.success) {
      ret = response.data;

    } else {
      throw new Error(`${response.data.code} - ${response.data.message}`);
    }
  } catch (error) {
    ajaxError.value = true;
    ajaxErrorText.value = error.message;
  }
}

async function refreshNotices() {
  let ret = {};

  try {
    const response = await doAjax({
      action: "wpjs-refresh-notices", // the action to fire in the server
      siteId: props.item.id,
    });

    if (response.success) {
      ret = response.data;

    } else {
      throw new Error(`${response.data.code} - ${response.data.message}`);
    }
  } catch (error) {
    ajaxError.value = true;
    ajaxErrorText.value = error.message;
  }
}

async function refreshAll() {
  refreshAllActive.value = true;
  await refreshHealth();
  await refreshPlugins();
  await refreshNotices();
  queryClient.invalidateQueries({
    queryKey: ["wpjs-control-panel"],
  });
  refreshAllActive.value = false;
}
</script>

<template>
  <tr>
    <td :colspan="props.columns?.length + 1" class="wp-juggler-expanded-panel">
      <v-sheet class="pt-2 px-2">
        <v-row class="d-flex align-center">
          <div class="text-h5 font-weight-bold mt-5 mb-3">
            {{ props.item?.title }}
          </div>

          <v-spacer></v-spacer>

          <v-btn class="ml-3 text-none text-caption" :loading="refreshAllActive" @click="refreshAll"
            variant="outlined">Refresh All
          </v-btn>
        </v-row>
        <div class="text-h6 text-medium-emphasis font-weight-regular mb-5">
          <v-icon v-if="props.item.wp_juggler_multisite" color="#2196f3" icon="mdi-checkbox-multiple-blank-outline"
            size="large" class="rm-4 mr-4"></v-icon>
          <a :href="props.item.wp_juggler_server_site_url" target="_blank">{{ props.item.wp_juggler_server_site_url }}</a>
        </div>

        <v-row class="mb-4">
          <v-col cols="12" md="3">
            <v-card>
              <v-card-item title="Site Health">
                <template v-slot:subtitle>
                  <div v-if="props.item.wp_juggler_health_data_timestamp">
                    <v-icon class="me-1 pb-1" icon="mdi-refresh" size="18"></v-icon>
                    {{ props.item.wp_juggler_health_data_timestamp }}
                  </div>
                  <div v-else>
                    <v-icon class="me-1 pb-1" icon="mdi-refresh" size="18"></v-icon>
                    Never
                  </div>
                </template>
              </v-card-item>

              <v-card-text class="text-medium-emphasis">
                <div class="d-flex py-3 justify-space-between pt-0">
                  <div>
                    <div>Critical Improvements</div>
                    <v-row align="center" no-gutters>
                      <v-col class="text-h2" cols="12" v-if="props.item.wp_juggler_health_data_count">
                        {{ props.item.wp_juggler_health_data_count.critical }}
                      </v-col>
                      <v-col class="text-h2" cols="12" v-else> ? </v-col>
                    </v-row>
                  </div>
                  <div>
                    <div>Recommended Improvements</div>
                    <v-row align="center" no-gutters>
                      <v-col class="text-h2" cols="12" v-if="props.item.wp_juggler_health_data_count">
                        {{ props.item.wp_juggler_health_data_count.recommended }}
                      </v-col>
                      <v-col class="text-h2" cols="12" v-else> ? </v-col>
                    </v-row>
                  </div>
                </div>

                <div class="d-flex py-3 justify-space-between pt-0">
                  <div v-if="props.item.wp_juggler_wordpress_version">
                    WordPress version:
                    {{ props.item.wp_juggler_wordpress_version }}
                  </div>
                  <div v-else>WordPress version: ?</div>
                  <div v-if="props.item.wp_juggler_core_checksum">
                    Checksum
                    <v-icon color="success" icon="mdi-check-bold" size="large" class="mr-1"
                      v-if="!props.item.wp_juggler_core_checksum.errors"></v-icon>
                    <v-icon color="error" icon="mdi-alert-outline" size="large" class="mr-1" v-else></v-icon>
                  </div>
                </div>
              </v-card-text>

              <v-divider></v-divider>

              <v-sheet class="pr-10">
                <v-btn v-if="props.item.wp_juggler_site_activation" text="Full Report" append-icon="mdi-chevron-right"
                  class="mb-5 ml-5 mt-4 text-none text-caption" @click="openHealth(props.item)" block
                  variant="outlined"></v-btn>
                <v-btn v-else text="Site is not activated" append-icon="mdi-alert-outline"
                  class="mb-5 ml-5 mt-4 text-none text-caption" block readonly color="error" variant="plain"></v-btn>
              </v-sheet>
            </v-card>
          </v-col>

          <v-col cols="12" md="3">
            <v-card>
              <div class="d-flex py-3 justify-space-between pb-0">
                <v-card-item title="Uptime Cron"> </v-card-item>

                <div class="mr-5">
                  <v-menu open-on-hover>
                    <template v-slot:activator="{ props }">
                      <v-btn v-bind="props" class="text-none text-caption" variant="outlined">
                        {{ uptimePeriods[selectedUptimePeriod].title }}
                      </v-btn>
                    </template>

                    <v-list>
                      <v-list-item v-for="(item, index) in uptimePeriods" :key="index"
                        @click="selectUptimePeriod(index)">
                        <v-list-item-title>{{ item.title }}</v-list-item-title>
                      </v-list-item>
                    </v-list>
                  </v-menu>
                </div>
              </div>

              <v-card-text class="text-medium-emphasis pt-0 mb-4">
                <div class="d-flex py-3 justify-space-between">
                  <div>
                    <div>Failed frontend checks</div>
                    <v-row align="center" no-gutters>
                      <v-col class="text-h2" cols="12">
                        {{
                          item.wp_juggler_uptime_stats.summary[
                            selectedUptimePeriod
                          ].front
                        }}
                      </v-col>
                    </v-row>
                  </div>
                  <div>
                    <div>Failed API checks</div>
                    <v-row align="center" no-gutters>
                      <v-col class="text-h2" cols="12">
                        {{
                          item.wp_juggler_uptime_stats.summary[
                            selectedUptimePeriod
                          ].api
                        }}
                      </v-col>
                    </v-row>
                  </div>
                </div>

                <div>
                  Total failed checks:
                  {{
                    parseInt(
                      item.wp_juggler_uptime_stats.summary[selectedUptimePeriod]
                        .front
                    ) +
                    parseInt(
                      item.wp_juggler_uptime_stats.summary[selectedUptimePeriod]
                        .api
                    )
                  }}
                </div>
              </v-card-text>

              <v-divider></v-divider>

              <v-sheet class="pr-10">
                <v-btn v-if="props.item.wp_juggler_site_activation" text="Full Report" append-icon="mdi-chevron-right"
                  class="mb-5 ml-5 mt-4 text-none text-caption" @click="openUptime(props.item)" block
                  variant="outlined"></v-btn>
                <v-btn v-else text="Site is not activated" append-icon="mdi-alert-outline"
                  class="mb-5 ml-5 mt-4 text-none text-caption" block readonly color="error" variant="plain"></v-btn>
              </v-sheet>
            </v-card>
          </v-col>

          <v-col cols="12" md="3">
            <v-card>
              <v-card-item title="Themes & Plugins">
                <template v-slot:subtitle>
                  <div v-if="props.item.wp_juggler_plugins_summary_timestamp">
                    <v-icon class="me-1 pb-1" icon="mdi-refresh" size="18"></v-icon>
                    {{ props.item.wp_juggler_plugins_summary_timestamp }}
                  </div>
                  <div v-else>
                    <v-icon class="me-1 pb-1" icon="mdi-refresh" size="18"></v-icon>
                    Never
                  </div>
                </template>
              </v-card-item>

              <v-card-text class="text-medium-emphasis">
                <div class="d-flex py-3 justify-space-between pt-0">
                  <div>
                    <div>Theme updates available</div>
                    <v-row align="center" no-gutters>
                      <v-col class="text-h2" cols="12" v-if="props.item?.wp_juggler_themes_summary !== false">
                        {{ props.item.wp_juggler_themes_summary.updates_num }}
                      </v-col>
                      <v-col class="text-h2" cols="12" v-else> ? </v-col>
                    </v-row>
                  </div>
                  <div>
                    <div>Plugin updates available</div>
                    <v-row align="center" no-gutters>
                      <v-col class="text-h2" cols="12" v-if="props.item?.wp_juggler_plugins_summary">
                        {{ props.item.wp_juggler_plugins_summary.updates_num }}
                      </v-col>
                      <v-col class="text-h2" cols="12" v-else> ? </v-col>
                    </v-row>
                  </div>
                </div>
                <div class="d-flex py-3 justify-space-between pt-0">
                  <div v-if="props.item?.wp_juggler_plugins_summary">
                    Recorded vulnerabilities:
                    {{
                      props.item.wp_juggler_plugins_summary.vulnerabilities_num
                    }}
                  </div>
                  <div v-else>Recorded vulnerabilities: ?</div>
                  <div v-if="props.item.wp_juggler_plugins_checksum !== false">
                    Checksum
                    <v-icon color="success" icon="mdi-check-bold" size="large" class="rm-4"
                      v-if="props.item.wp_juggler_plugins_checksum == 0"></v-icon>
                    <v-icon color="error" icon="mdi-alert-outline" size="large" class="rm-4" v-else></v-icon>
                  </div>
                </div>
              </v-card-text>

              <v-divider></v-divider>
              <v-sheet class="pr-10">
                <v-btn v-if="props.item.wp_juggler_site_activation" text="Manage Themes & Plugins"
                  append-icon="mdi-chevron-right" class="mb-5 ml-5 mt-4 text-none text-caption"
                  @click="openThemesPlugins(props.item)" ref="themesButton" block variant="outlined"></v-btn>

                <v-btn v-else text="Site is not activated" append-icon="mdi-alert-outline"
                  class="mb-5 ml-5 mt-4 text-none text-caption" block readonly color="error" variant="plain"></v-btn>
              </v-sheet>
            </v-card>
          </v-col>

          <v-col cols="12" md="3">
            <v-card>
              <v-card-item title="Notices">
                <template v-slot:subtitle>
                  <div v-if="props.item.wp_juggler_notices_timestamp">
                    <v-icon class="me-1 pb-1" icon="mdi-refresh" size="18"></v-icon>
                    {{ props.item.wp_juggler_notices_timestamp }}
                  </div>
                  <div v-else>
                    <v-icon class="me-1 pb-1" icon="mdi-refresh" size="18"></v-icon>
                    Never
                  </div>
                </template>
              </v-card-item>

              <v-card-text class="text-medium-emphasis">
                <div class="d-flex py-3 justify-space-between pt-0">
                  <div>
                    <div>Number of notices</div>
                    <v-row align="center" no-gutters>
                      <v-col class="text-h2" cols="12" v-if="props.item?.wp_juggler_notices_count !== false">
                        {{ props.item.wp_juggler_notices_count }}
                      </v-col>
                      <v-col class="text-h2" cols="12" v-else> ? </v-col>
                    </v-row>
                  </div>
                </div>
                <div class="d-flex py-3 justify-space-between pt-0">
                  <div>&nbsp;</div>
                </div>
              </v-card-text>

              <v-divider></v-divider>
              <v-sheet class="pr-10">
                <v-btn v-if="props.item.wp_juggler_site_activation" text="All notices" append-icon="mdi-chevron-right"
                  class="mb-5 ml-5 mt-4 text-none text-caption" @click="openNotices(props.item)" ref="noticesButton"
                  block variant="outlined"></v-btn>
                <v-btn v-else text="Site is not activated" append-icon="mdi-alert-outline"
                  class="mb-5 ml-5 mt-4 text-none text-caption" block readonly color="error" variant="plain"></v-btn>
              </v-sheet>
            </v-card>
          </v-col>
        </v-row>
      </v-sheet>
    </td>
  </tr>
  <v-snackbar v-model="ajaxError" color="red-lighten-2">
    {{ ajaxErrorText }}

    <template v-slot:actions>
      <v-btn color="red-lighten-4" variant="text" @click="ajaxError = false">
        Close
      </v-btn>
    </template>
  </v-snackbar>
</template>

<style>
.wp-juggler-expanded-panel {
  border: 2px solid rgb(33, 150, 243) !important;
}
</style>
