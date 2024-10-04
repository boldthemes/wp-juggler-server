<script setup>
import { useWpjsStore } from "./store.js";
import { onMounted, computed, ref } from "vue";
import { useQueryClient, useQuery, useMutation } from "@tanstack/vue-query";

const store = useWpjsStore();
const passedOpen = ref(false);

const search = ref("");

const dialogInner = ref(false);
const vulnerabilitiesItem = ref(null);

const refreshAllActive = ref(false);
const refreshHealthActive = ref(false);
const refreshDebugActive = ref(false);
const refreshCoreChecksumActive = ref(false);
const ajaxError = ref(false);
const ajaxErrorText = ref("");

// Refresh Dialog Params
const dialogRefreshTabs = ref(false)
const refreshTabsInProgress = ref(false)
const refreshTabsFinished = ref(false)
const refreshProgressIndicator = ref(0)
const currentProgressIndex = ref(0)
const currentRefreshAction = ref('')
const refreshArr = [
  {
    text: 'Refreshing Health Status',
    ajaxParam: 'wpjs-refresh-health'
  },
  {
    text: 'Refreshing Health Info',
    ajaxParam: 'wpjs-refresh-debug'
  },
  {
    text: 'Refreshing Core Checksum Info',
    ajaxParam: 'wpjs-refresh-core-checksum'
  },
]

const tab = ref(0);

const queryClient = useQueryClient();
const { isLoading, isError, isFetching, data, error, refetch } = useQuery({
  queryKey: ["wpjs-health-panel", store.activatedSite.id],
  queryFn: getHealthPanel,
});

async function getHealthPanel() {
  let ret = {};
  const response = await doAjax({
    action: "wpjs-get-health-panel", // the action to fire in the server
    siteId: store.activatedSite.id,
  });
  ret = response.data[0];
  return ret;
}

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

const recommendations = computed(() => {
  if (data.value.wp_juggler_health_data_status) {
    return data.value.wp_juggler_health_data_status.filter(
      (item) =>
        item.status === "recommended" && item.test !== "rest_availability"
    );
  } else {
    return [];
  }
});

const criticals = computed(() => {
  if (data.value.wp_juggler_health_data_status) {
    return data.value.wp_juggler_health_data_status.filter(
      (item) => item.status === "critical" && item.test !== "rest_availability"
    );
  } else {
    return [];
  }
});

const goods = computed(() => {
  if (data.value.wp_juggler_health_data_status) {
    return data.value.wp_juggler_health_data_status.filter(
      (item) => item.status === "good" && item.test !== "rest_availability"
    );
  } else {
    return [];
  }
});

const openIcon = computed(() => {
  return passedOpen.value ? "mdi-chevron-up" : "mdi-chevron-down";
});

function debugFields(fieldArray) {
  return fieldArray.filter((item) => item.debug !== "loading...");
}

const gotoUrl = (url) => {
  const newWindow = window.open(url, "_blank", "noopener,noreferrer");
  if (newWindow) newWindow.opener = null;
};

async function refreshAll() {
  
  currentProgressIndex.value = 0
  refreshTabsFinished.value = false;
  dialogRefreshTabs.value = true;
  refreshTabsInProgress.value = true;

  for (const elem of refreshArr) {
    currentRefreshAction.value = elem.text;
    console.log()
    refreshProgressIndicator.value = Math.ceil((currentProgressIndex.value + 1) / refreshArr.length * 100)
    try {
      const response = await doAjax({
        action: elem.ajaxParam, // the action to fire in the server
        siteId: store.activatedSite.id,
      });

      if (!response.success) {
        throw new Error(`${response.data.code} - ${response.data.message}`);
      }

      currentProgressIndex.value++

    } catch (error) {
      ajaxError.value = true;
      ajaxErrorText.value = error.message;
    }
  }

  queryClient.invalidateQueries({
    queryKey: ["wpjs-health-panel", store.activatedSite.id],
  });
  queryClient.invalidateQueries({
    queryKey: ["wpjs-control-panel"],
  });

  refreshTabsFinished.value = true;
  dialogRefreshTabs.value = false;

}

async function refreshHealth() {
  refreshHealthActive.value = true;

  let ret = {};

  try {
    const response = await doAjax({
      action: "wpjs-refresh-health", // the action to fire in the server
      siteId: store.activatedSite.id,
    });

    if (response.success) {
      ret = response.data;

      queryClient.invalidateQueries({
        queryKey: ["wpjs-health-panel", store.activatedSite.id],
      });
      queryClient.invalidateQueries({
        queryKey: ["wpjs-control-panel"],
      });

      refreshHealthActive.value = false;
    } else {
      throw new Error(`${response.data.code} - ${response.data.message}`);
    }
  } catch (error) {
    ajaxError.value = true;
    ajaxErrorText.value = error.message;
    refreshHealthActive.value = false;
  }
}

async function refreshDebug() {
  refreshDebugActive.value = true;

  let ret = {};

  try {
    const response = await doAjax({
      action: "wpjs-refresh-debug", // the action to fire in the server
      siteId: store.activatedSite.id,
    });

    if (response.success) {
      ret = response.data;

      queryClient.invalidateQueries({
        queryKey: ["wpjs-health-panel", store.activatedSite.id],
      });
      queryClient.invalidateQueries({
        queryKey: ["wpjs-control-panel"],
      });

      refreshDebugActive.value = false;
    } else {
      throw new Error(`${response.data.code} - ${response.data.message}`);
    }
  } catch (error) {
    ajaxError.value = true;
    ajaxErrorText.value = error.message;
    refreshDebugActive.value = false;
  }
}

async function refreshCoreChecksum() {
  refreshCoreChecksumActive.value = true;

  let ret = {};

  try {
    const response = await doAjax({
      action: "wpjs-refresh-core-checksum", // the action to fire in the server
      siteId: store.activatedSite.id,
    });

    if (response.success) {
      ret = response.data;

      queryClient.invalidateQueries({
        queryKey: ["wpjs-health-panel", store.activatedSite.id],
      });
      queryClient.invalidateQueries({
        queryKey: ["wpjs-control-panel"],
      });

      refreshCoreChecksumActive.value = false;
    } else {
      throw new Error(`${response.data.code} - ${response.data.message}`);
    }
  } catch (error) {
    ajaxError.value = true;
    ajaxErrorText.value = error.message;
    refreshCoreChecksumActive.value = false;
  }
}
</script>

<template>
  <div class="text-center pa-4">
    <v-dialog v-model="store.activatedHealth" transition="dialog-bottom-transition" fullscreen>
      <v-card>
        <v-toolbar>
          <v-btn icon="mdi-close" @click="store.activatedHealth = false"></v-btn>

          <v-toolbar-title>{{ store.activatedSite.title }} </v-toolbar-title>

          <v-spacer></v-spacer>

          <v-toolbar-items> </v-toolbar-items>
        </v-toolbar>

        <v-card-text v-if="data">
          <v-sheet class="pa-4 text-right mx-auto" elevation="0" width="100%" rounded="lg">
            <div v-if="data.wp_juggler_health_data_timestamp" class="wpjs-timestamp">
              <v-icon class="me-1 pb-1" icon="mdi-refresh" size="18"></v-icon>
              Health Status Data: <strong>{{ data.wp_juggler_health_data_timestamp }}</strong>
              <v-icon class="me-1 pb-1 ml-2" icon="mdi-circle-medium" size="18"></v-icon>
              Health Info Data: <strong>{{ data.wp_juggler_debug_data_timestamp }}</strong>
              <v-icon class="me-1 pb-1 ml-2" icon="mdi-circle-medium" size="18"></v-icon>
              Core Checksum Data: <strong>{{ data.wp_juggler_core_checksum_data_timestamp }}</strong>
              <v-btn
                class="ml-3 text-none text-caption"
                :loading="refreshAllActive"
                @click="refreshAll"
                variant="outlined"
                >Refresh Data
              </v-btn>
            </div>

            <div v-else class="wpjs-timestamp">
              <v-icon class="me-1 pb-1" icon="mdi-refresh" size="18"></v-icon>
              Never
              <v-btn
                class="ml-3 text-none text-caption"
                :loading="refreshAllActive"
                @click="refreshAll"
                variant="outlined"
                >Refresh Data
              </v-btn>
            </div>
          </v-sheet>

          <v-card>
            <v-tabs v-model="tab" bg-color="surface">
              <v-tab value="status">Status</v-tab>
              <v-tab value="info">Info</v-tab>
              <v-tab value="core">WP Core Files</v-tab>
            </v-tabs>

            <v-card-text class="mt-10">
              <v-tabs-window v-model="tab">
                <v-tabs-window-item value="status" transition="false" reverse-transition="false">
                  <v-sheet max-width="1200" v-if="goods.length > 0"
                    class="align-center justify-center text-center mx-auto px-4 pb-4">
                    <v-sheet class="align-left justify-left text-left mb-10">
                      <div class="text-h6">Site Health Status</div>
                      <div class="mt-3 mb-4">
                        The site health check shows information about your
                        WordPress configuration and items that may need your
                        attention.
                      </div>

                      <div v-if="criticals.length > 0" class="text-h6">
                        {{ criticals.length }} critical issue
                      </div>
                      <div class="mt-3 mb-4">
                        Critical issues are items that may have a high impact on
                        your sites performance or security, and resolving these
                        issues should be prioritized.
                      </div>

                      <v-expansion-panels v-if="criticals.length > 0" class="mt-8 mb-10" variant="accordion">
                        <v-expansion-panel v-for="critical in criticals">
                          <v-expansion-panel-title>
                            {{ critical.label }}
                            <v-spacer></v-spacer>
                            <div :class="[
                              'mr-3 pa-2 wpjs-health-badge-label',
                              critical.badge.color,
                            ]">
                              {{ critical.badge.label }}
                            </div>
                          </v-expansion-panel-title>
                          <v-expansion-panel-text>
                            <div class="wpjs-health-panel-description" v-html="critical.description"></div>
                            <div class="wpjs-health-panel-actions" v-html="critical.actions"></div>
                          </v-expansion-panel-text>
                        </v-expansion-panel>
                      </v-expansion-panels>

                      <div class="text-h6">
                        {{ recommendations.length }} recommended improvements
                      </div>
                      <div class="mt-3 mb-4">
                        Recommended items are considered beneficial to your
                        site, although not as important to prioritize as a
                        critical issue, they may include improvements to things
                        such as; Performance, user experience, and more.
                      </div>

                      <v-expansion-panels v-if="recommendations.length > 0" class="mt-8" variant="accordion">
                        <v-expansion-panel v-for="recommendation in recommendations">
                          <v-expansion-panel-title>
                            {{ recommendation.label }}
                            <v-spacer></v-spacer>
                            <div :class="[
                              'mr-3 pa-2 wpjs-health-badge-label',
                              recommendation.badge.color,
                            ]">
                              {{ recommendation.badge.label }}
                            </div>
                          </v-expansion-panel-title>
                          <v-expansion-panel-text>
                            <div class="wpjs-health-panel-description" v-html="recommendation.description"></div>
                            <div class="wpjs-health-panel-actions" v-html="recommendation.actions"></div>
                          </v-expansion-panel-text>
                        </v-expansion-panel>
                      </v-expansion-panels>
                    </v-sheet>

                    <v-btn class="ml-3 text-none text-caption" :append-icon="openIcon" @click="passedOpen = !passedOpen"
                      variant="outlined">Passed tests</v-btn>

                    <v-sheet v-if="passedOpen" class="align-left justify-left text-left my-10">
                      <div class="text-h6">
                        {{ goods.length }} items with no issues detected
                      </div>

                      <v-expansion-panels class="mt-8" variant="accordion">
                        <v-expansion-panel v-for="good in goods">
                          <v-expansion-panel-title>
                            {{ good.label }}
                            <v-spacer></v-spacer>
                            <div :class="[
                              'mr-3 pa-2 wpjs-health-badge-label',
                              good.badge.color,
                            ]">
                              {{ good.badge.label }}
                            </div>
                          </v-expansion-panel-title>
                          <v-expansion-panel-text>
                            <div class="wpjs-health-panel-description" v-html="good.description"></div>
                          </v-expansion-panel-text>
                        </v-expansion-panel>
                      </v-expansion-panels>
                    </v-sheet>
                  </v-sheet>

                  <v-sheet v-else max-width="1200" class="align-center justify-center text-center mx-auto px-4 pb-4">
                    <v-sheet class="align-left justify-left text-left mb-10">
                      <div class="text-h6">No Recorded Site Health Status</div>
                    </v-sheet>
                  </v-sheet>
                </v-tabs-window-item>

                <v-tabs-window-item value="info" transition="false" reverse-transition="false">
                  <v-sheet v-if="data.wp_juggler_health_data_info" max-width="1200"
                    class="align-center justify-center text-center mx-auto px-4 pb-4">
                    <v-sheet class="align-left justify-left text-left mb-10">
                      <div class="text-h6">Site Health Info</div>

                      <v-expansion-panels class="mt-8" variant="accordion">
                        <v-expansion-panel v-for="debug in data.wp_juggler_health_data_info">
                          <v-expansion-panel-title v-if="debug.fields.length > 0 && debug.show_count">
                            {{ debug.label }} ({{ debug.fields.length }})
                          </v-expansion-panel-title>
                          <v-expansion-panel-title v-else-if="debug.fields.length > 0">
                            {{ debug.label }}
                          </v-expansion-panel-title>
                          <v-expansion-panel-text>
                            <v-table density="compact">
                              <tbody>
                                <tr v-for="field in debugFields(debug.fields)" class="wpjs-debug-table-row">
                                  <td>{{ field.label }}</td>
                                  <td>{{ field.value }}</td>
                                </tr>
                              </tbody>
                            </v-table>
                          </v-expansion-panel-text>
                        </v-expansion-panel>
                      </v-expansion-panels>
                    </v-sheet>
                  </v-sheet>

                  <v-sheet v-else max-width="1200" class="align-center justify-center text-center mx-auto px-4 pb-4">
                    <v-sheet class="align-left justify-left text-left mb-10">
                      <div class="text-h6">No Recorded Site Health Info</div>
                    </v-sheet>
                  </v-sheet>
                </v-tabs-window-item>

                <v-tabs-window-item value="core" transition="false" reverse-transition="false">
                  <v-sheet v-if="data.wp_juggler_health_data_core" max-width="1200"
                    class="align-center justify-center text-center mx-auto px-4 pb-4">
                    <v-sheet class="align-left justify-left text-left mb-10">
                      <div class="text-h6">WordPress Core Files</div>

                      <v-sheet class="pa-4 text-left mx-auto" elevation="0" width="100%" rounded="lg"
                        v-if="data.wp_juggler_health_data_upgrade">
                        <div>
                          Core Update Available - version {{ data.wp_juggler_health_data_upgrade }}
                          <v-btn class="ml-3 text-none text-caption"
                            @click="gotoUrl(data.wp_juggler_health_data_upgrade_url)" variant="outlined">Go to Core
                            Update Panel
                          </v-btn>
                        </div>
                      </v-sheet>

                      <div v-if="!data.wp_juggler_health_data_core.errors" class="text-h7 mb-4 mt-10">
                        <v-icon color="success" icon="mdi-check-bold" size="large" class="mr-1"></v-icon>
                        WordPress installation verifies against checksums
                      </div>
                      <div v-else class="text-h7 mb-4 mt-10">
                        <v-icon color="error" icon="mdi-alert-outline" size="large" class="mr-1"></v-icon>
                        WordPress installation does not verify against checksums
                      </div>
                      <v-divider class="mb-4"></v-divider>

                      <div v-if="data.wp_juggler_health_data_core.errors" class="text-h7 mb-4 mt-4">
                        These core files don't verify against checksum:
                      </div>
                      <v-sheet v-if="data.wp_juggler_health_data_core.errors">
                        <v-row class="wpjs-debug-table-row pl-5" v-for="item in data.wp_juggler_health_data_core
                          .error_files">
                          <v-col class="text-left">
                            {{ item }}
                          </v-col>
                        </v-row>
                      </v-sheet>

                      <div v-if="
                        !data.wp_juggler_health_data_core.additional.length >
                        0
                      " class="text-h7 mb-4 mt-10">
                        <v-icon color="success" icon="mdi-check-bold" size="large" class="mr-1"></v-icon>
                        WordPress installation does not contain additional files
                      </div>
                      <div v-else class="text-h7 mb-4 mt-10">
                        <v-icon color="error" icon="mdi-alert-outline" size="large" class="mr-1"></v-icon>
                        WordPress installation contains additional files
                      </div>
                      <v-divider class="mb-4"></v-divider>

                      <div v-if="
                        data.wp_juggler_health_data_core.additional.length > 0
                      " class="text-h7 mb-4 mt-4">
                        These files should not exist:
                      </div>
                      <v-sheet v-if="
                        data.wp_juggler_health_data_core.additional.length > 0
                      ">
                        <v-row class="wpjs-debug-table-row pl-5" v-for="item in data.wp_juggler_health_data_core
                          .additional">
                          <v-col class="text-left">
                            {{ item }}
                          </v-col>
                        </v-row>
                      </v-sheet>
                    </v-sheet>
                  </v-sheet>

                  <v-sheet v-else max-width="1200" class="align-center justify-center text-center mx-auto px-4 pb-4">
                    <v-sheet class="align-left justify-left text-left mb-10">
                      <div class="text-h6">No Recorded Core Files Report</div>
                    </v-sheet>
                  </v-sheet>
                </v-tabs-window-item>
              </v-tabs-window>
            </v-card-text>
          </v-card>
        </v-card-text>

        <v-card-text v-else>
          <v-skeleton-loader type="heading, table-row-divider, list-item-two-line@6, table-tfoot" class="mt-15 mx-auto"
            max-width="1200">
          </v-skeleton-loader>
        </v-card-text>
      </v-card>

      <v-snackbar v-model="ajaxError" color="red-lighten-2">
        {{ ajaxErrorText }}

        <template v-slot:actions>
          <v-btn color="red-lighten-4" variant="text" @click="ajaxError = false">
            Close
          </v-btn>
        </template>
      </v-snackbar>
    </v-dialog>

    <v-dialog v-model="dialogRefreshTabs" width="800" :persistent="true">
      <v-card>
        <v-toolbar>
          <v-btn v-if="!(refreshTabsInProgress && !refreshTabsFinished)" icon="mdi-close"
            @click="dialogRefreshTabs = false"></v-btn>
          <v-toolbar-title>Site Health Data</v-toolbar-title>
        </v-toolbar>

        <v-card-text>
          <v-sheet v-if="refreshTabsInProgress && !refreshTabsFinished" class="mb-4" height="200">
            <div class="my-8">
              Refresh in progress - do not close the window, you will
              interrupt the progress:
            </div>
            <div class="my-8">
              <strong>{{ currentRefreshAction }}</strong>
            </div>
            <v-progress-linear color="light-blue" height="30" :model-value="refreshProgressIndicator" striped>
              <strong>{{ currentProgressIndex + 1 }}/{{
                refreshArr.length
              }}</strong>
            </v-progress-linear>
          </v-sheet>

          <v-sheet v-else-if="refreshTabsInProgress && refreshTabsFinished" class="mb-4" height="200">
            <div class="my-8">Refresh finished</div>
          </v-sheet>
        </v-card-text>
      </v-card>
    </v-dialog>
  </div>
</template>

<style>
.wpjs-health-panel-description {
  padding-top: 24px;
}

.wpjs-health-panel-description p {
  margin-bottom: 24px;
}

.wpjs-health-panel-actions p {
  margin-bottom: 8px;
}

.wpjs-debug-table-row {
  &:nth-child(odd) {
    background-color: #f7f7f7;
  }
}

.wpjs-health-badge-label.blue {
  border: 1px solid #72aee6;
}

.wpjs-health-badge-label.orange {
  border: 1px solid #dba617;
}

.wpjs-health-badge-label.red {
  border: 1px solid #e65054;
}

.wpjs-health-badge-label.green {
  border: 1px solid #00ba37;
}

.wpjs-health-badge-label.purple {
  border: 1px solid #2271b1;
}

.wpjs-health-badge-label.gray {
  border: 1px solid #c3c4c7;
}
</style>
