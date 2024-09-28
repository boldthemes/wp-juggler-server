<script setup>
import { useWpjsStore } from "./store.js";
import { onMounted, computed, ref } from "vue";
import { useQueryClient, useQuery, useMutation } from "@tanstack/vue-query";
import ExpandedRow from "./ExpandedRow.vue";
import ExpandedRowPlugins from "./ExpandedRowPlugins.vue";
import ExpandedRowThemes from "./ExpandedRowThemes.vue";
import ThemesPluginsPanel from "./ThemesPluginsPanel.vue";
import HealthPanel from "./HealthPanel.vue";
import UptimePanel from "./UptimePanel.vue";
import NoticesPanel from "./NoticesPanel.vue";

const queryClient = useQueryClient();

const store = useWpjsStore();

const tab = ref(0);

const searchSites = ref("");
const searchPlugins = ref("");
const searchThemes = ref("");

const expanded = ref([]);
const expandedPlugins = ref([]);
const expandedThemes = ref([]);

const headersSites = [
  { title: "", key: "network", align: "center", sortable: false },
  { title: "Title", value: "title", align: "start", sortable: true },
  {
    title: "Url",
    key: "wp_juggler_server_site_url",
    align: "start",
    sortable: true,
  },
  {
    title: "Messages",
    key: "events",
    align: "center",
    sortable: true,
    sortRaw(a, b) {
      if (!a.wp_juggler_site_activation) return -1;
      if (!b.wp_juggler_site_activation) return 1;

      if (a.wp_juggler_notices_count === false) return -1;
      if (b.wp_juggler_notices_count === false) return 1;

      if (a.wp_juggler_notices_count < b.wp_juggler_notices_count) return -1;
      if (a.wp_juggler_notices_count > b.wp_juggler_notices_count) return 1;

      return 1;
    }
  },
  {
    title: "Downtime incidents",
    key: "uptime",
    align: "center",
    sortable: false,
  },
  { title: "Updates", key: "updates", align: "center", sortable: false },
  { title: "Checksum", key: "checksum", align: "center", sortable: false },
  { title: "Links", key: "links", align: "center", sortable: false },
  { title: "WP admin", key: "wp_admin", align: "center", sortable: false },
];

const headersPlugins = [
  { title: "Plugin Name", value: "Name", align: "start", sortable: true },
  {
    title: "Latest Version",
    value: "Version",
    align: "center",
    sortable: true,
  },
  { title: "Updates", key: "updates", align: "center", sortable: false },
  {
    title: "Number of Installations",
    key: "installations",
    align: "center",
    sortable: false,
  },
];

const headersThemes = [
  { title: "Theme Name", value: "Name", align: "start", sortable: true },
  {
    title: "Latest Version",
    value: "Version",
    align: "center",
    sortable: true,
  },
  { title: "Updates", key: "updates", align: "center", sortable: false },
  {
    title: "Number of Installations",
    key: "installations",
    align: "center",
    sortable: false,
  },
];

const { isLoading, isError, isFetching, data, error, refetch } = useQuery({
  queryKey: ["wpjs-control-panel"],
  queryFn: getDashboard,
});

function countUpdates(arr) {
  return arr.filter((item) => item.Update === true).length;
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
    throw error;
  }
}

async function getDashboard() {
  let ret = {};
  const response = await doAjax({
    action: "wpjs_get_control_panel", // the action to fire in the server
  });
  ret = response.data;
  return ret;
}

function backToDashboard() {
  window.location.href = wpjs_control_panel_object.adminurl;
}

function calculateColor(day) {
  if (day.fail_num == 0) return "success";
  return "error";
}

const gotoUrl = (url) => {
  const newWindow = window.open(url, "_blank", "noopener,noreferrer");
  if (newWindow) newWindow.opener = null;
};

onMounted(() => {
  store.nonce = wpjs_control_panel_object.nonce;
  store.ajaxUrl = wpjs_control_panel_object.ajaxurl;
});
</script>

<template>
  <div class="mt-6 ml-4 mb-4 juggler-main-panel">
    <v-row align="center" class="px-4">
      <v-icon icon="mdi-video-input-component"></v-icon> &nbsp; WP Juggler
      Control Panel

      <v-spacer></v-spacer>

      <v-btn
        color="#2196f3"
        variant="outlined"
        class="text-none text-caption mr-10"
        @click="backToDashboard"
        >Back to Dashboard</v-btn
      >
    </v-row>

    <v-card class="pa-4 mr-4 mt-5 mb-5">
      <v-tabs v-model="tab" bg-color="surface">
        <v-tab value="sites">Sites</v-tab>
        <v-tab value="plugins">Plugins</v-tab>
        <v-tab value="themes">Themes</v-tab>
      </v-tabs>

      <v-tabs-window v-model="tab">
        <v-tabs-window-item
          value="sites"
          transition="false"
          reverse-transition="false"
        >
          <v-card flat v-if="data">
            <v-card-title class="d-flex align-center pe-2 mb-6">
              <v-spacer></v-spacer>

              <v-text-field
                v-model="searchSites"
                density="compact"
                label="Search"
                prepend-inner-icon="mdi-magnify"
                variant="outlined"
                flat
                hide-details
                single-line
                max-width="800"
              ></v-text-field>
            </v-card-title>

            <v-divider></v-divider>
            <v-data-table
              v-model:search="searchSites"
              :items="data.sites_view"
              :headers="headersSites"
              item-value="id"
              v-model:expanded="expanded"
              show-expand
              items-per-page="50"
            >
              <template v-slot:item.network="{ item }">
                <div v-if="item.wp_juggler_multisite">
                  <v-icon
                    color="#2196f3"
                    icon="mdi-checkbox-multiple-blank-outline"
                    size="large"
                    class="rm-4"
                  ></v-icon>
                </div>
              </template>

              <template v-slot:item.wp_juggler_server_site_url="{ item }">
                <a :href="item.wp_juggler_server_site_url" target="_blank">{{
                  item.wp_juggler_server_site_url
                }}</a>
              </template>

              <template v-slot:item.events="{ item }">
                <div v-if="item.wp_juggler_site_activation">
                  <div v-if="item.wp_juggler_notices_count === false">
                    <v-icon
                      color="blue-lighten-5"
                      icon="mdi-help"
                      size="large"
                      class="rm-4"
                    ></v-icon>
                  </div>
                  <div v-else-if="item.wp_juggler_notices_count > 0">
                    <v-icon
                      color="#2196f3"
                      icon="mdi-email-alert-outline"
                      size="large"
                      class="rm-4"
                    ></v-icon>
                  </div>
                </div>
                <div v-if="!item.wp_juggler_site_activation">Inactive</div>
              </template>

              <template v-slot:item.uptime="{ item }">
                <div v-if="item.wp_juggler_site_activation">
                  <div
                    v-for="day in item.wp_juggler_uptime_stats.uptime_timeline"
                    class="wpjs-timeline-icon"
                  >
                    <v-tooltip
                      :text="
                        day.day_label + ' - ' + day.fail_num + ' incidents'
                      "
                      location="top"
                      v-if="day.fail_num > 0"
                    >
                      <template v-slot:activator="{ props }">
                        <v-icon
                          v-bind="props"
                          :color="calculateColor(day)"
                          icon="mdi-square"
                          size="large"
                          class="rm-4"
                        ></v-icon>
                      </template>
                    </v-tooltip>
                    <v-icon
                      v-else
                      :color="calculateColor(day)"
                      icon="mdi-square"
                      size="large"
                      class="rm-4"
                    ></v-icon>
                  </div>
                </div>
                <div v-if="!item.wp_juggler_site_activation">Inactive</div>
              </template>

              <template v-slot:item.updates="{ item }">
                <div v-if="item.wp_juggler_site_activation">
                  <div v-if="item.wp_juggler_plugins_summary">
                    <v-icon
                      v-if="
                        item.wp_juggler_plugins_summary.vulnerabilities_num > 0
                      "
                      color="error"
                      icon="mdi-bug-check-outline"
                      size="large"
                      class="rm-4"
                    ></v-icon>
                    <v-icon
                      v-else-if="
                        item.wp_juggler_plugins_summary.updates_num > 0 ||
                        item.wp_juggler_themes_summary.updates_num > 0
                      "
                      color="error"
                      icon="mdi-check-bold"
                      size="large"
                      class="rm-4"
                    ></v-icon>
                  </div>
                  <div v-else>
                    <v-icon
                      color="blue-lighten-5"
                      icon="mdi-help"
                      size="large"
                      class="rm-4"
                    ></v-icon>
                  </div>
                </div>
                <div v-if="!item.wp_juggler_site_activation">Inactive</div>
              </template>

              <template v-slot:item.checksum="{ item }">
                <div v-if="item.wp_juggler_site_activation">
                  <div
                    v-if="
                      (item.wp_juggler_plugins_checksum &&
                        item.wp_juggler_plugins_checksum > 0) ||
                      (item.wp_juggler_core_checksum &&
                        item.wp_juggler_core_checksum.errors === true)
                    "
                  >
                    <v-icon
                      color="error"
                      icon="mdi-alert-outline"
                      size="large"
                      class="rm-4"
                    ></v-icon>
                  </div>
                  <div
                    v-else-if="
                      item.wp_juggler_plugins_checksum === false ||
                      item.wp_juggler_core_checksum === false
                    "
                  >
                    <v-icon
                      color="blue-lighten-5"
                      icon="mdi-help"
                      size="large"
                      class="rm-4"
                    ></v-icon>
                  </div>
                </div>

                <div v-if="!item.wp_juggler_site_activation">Inactive</div>
              </template>

              <template v-slot:item.links="{ item }">
                <div v-if="item.wp_juggler_site_activation">
                  <v-btn
                    v-for="button in item.wp_juggler_login_tools"
                    variant="outlined"
                    @click="gotoUrl(button.wp_juggler_tool_url)"
                    class="text-none text-caption mr-1 ml-1"
                    >{{ button.wp_juggler_tool_label }}</v-btn
                  >
                </div>
                <div v-if="!item.wp_juggler_site_activation">Inactive</div>
              </template>

              <template v-slot:item.wp_admin="{ item }">
                <div
                  v-if="
                    item.wp_juggler_site_activation &&
                    item.wp_juggler_automatic_login
                  "
                >
                  <v-btn
                    color="#2196f3"
                    variant="outlined"
                    class="text-none text-caption"
                    prepend-icon="mdi-login"
                    @click="gotoUrl(item.wp_juggler_login_url)"
                    >wp-admin</v-btn
                  >
                </div>
                <div
                  v-if="
                    item.wp_juggler_site_activation &&
                    !item.wp_juggler_automatic_login
                  "
                >
                  <v-btn
                    color="#2196f3"
                    variant="outlined"
                    class="text-none text-caption"
                    prepend-icon="mdi-account-remove"
                    @click="gotoUrl(item.wp_juggler_login_url)"
                    >wp-admin</v-btn
                  >
                </div>
                <div v-if="!item.wp_juggler_site_activation">Inactive</div>
              </template>

              <template v-slot:expanded-row="{ columns, item }">
                <ExpandedRow :columns="columns" :item="item"></ExpandedRow>
              </template>
            </v-data-table>
          </v-card>
          <v-card flat v-else>
            <v-skeleton-loader type="table"> </v-skeleton-loader>
          </v-card>
        </v-tabs-window-item>

        <v-tabs-window-item
          value="plugins"
          transition="false"
          reverse-transition="false"
        >
          <v-card flat v-if="data">
            <v-card-title class="d-flex align-center pe-2 mb-6">
              <v-spacer></v-spacer>

              <v-text-field
                v-model="searchPlugins"
                density="compact"
                label="Search"
                prepend-inner-icon="mdi-magnify"
                variant="outlined"
                flat
                hide-details
                single-line
                max-width="800"
              ></v-text-field>
            </v-card-title>

            <v-divider></v-divider>
            <v-data-table
              v-model:search="searchPlugins"
              :items="data.plugins_view.plugins"
              :headers="headersPlugins"
              item-value="Name"
              v-model:expanded="expandedPlugins"
              show-expand
              items-per-page="50"
            >
              <template v-slot:item.updates="{ item }">
                <div v-if="countUpdates(item.Sites) > 0">
                  <v-icon
                    color="success"
                    icon="mdi-check-bold"
                    size="large"
                    class="rm-4"
                  ></v-icon>
                </div>
              </template>

              <template v-slot:item.installations="{ item }">
                <div>{{ item.Sites.length }}</div>
              </template>

              <template v-slot:expanded-row="{ columns, item }">
                <ExpandedRowPlugins
                  :columns="columns"
                  :items="item.Sites"
                  :name="item.Name"
                ></ExpandedRowPlugins>
              </template>
            </v-data-table>
          </v-card>
          <v-card flat v-else>
            <v-skeleton-loader type="table"> </v-skeleton-loader>
          </v-card>
        </v-tabs-window-item>

        <v-tabs-window-item
          value="themes"
          transition="false"
          reverse-transition="false"
        >
          <v-card flat v-if="data">
            <v-card-title class="d-flex align-center pe-2 mb-6">
              <v-spacer></v-spacer>

              <v-text-field
                v-model="searchThemes"
                density="compact"
                label="Search"
                prepend-inner-icon="mdi-magnify"
                variant="outlined"
                flat
                hide-details
                single-line
                max-width="800"
              ></v-text-field>
            </v-card-title>

            <v-divider></v-divider>
            <v-data-table
              v-model:search="searchThemes"
              :items="data.plugins_view.themes"
              :headers="headersThemes"
              item-value="Name"
              v-model:expanded="expandedThemes"
              show-expand
              items-per-page="50"
            >
              <template v-slot:item.updates="{ item }">
                <div v-if="countUpdates(item.Sites) > 0">
                  <v-icon
                    color="success"
                    icon="mdi-check-bold"
                    size="large"
                    class="rm-4"
                  ></v-icon>
                </div>
              </template>

              <template v-slot:item.installations="{ item }">
                <div>{{ item.Sites.length }}</div>
              </template>

              <template v-slot:expanded-row="{ columns, item }">
                <ExpandedRowThemes
                  :columns="columns"
                  :items="item.Sites"
                  :name="item.Name"
                ></ExpandedRowThemes>
              </template>
            </v-data-table>
          </v-card>
          <v-card flat v-else>
            <v-skeleton-loader type="table"> </v-skeleton-loader>
          </v-card>
        </v-tabs-window-item>
      </v-tabs-window>
    </v-card>

    <v-row justify="end" class="px-10 my-4">
      <v-btn
        color="#2196f3"
        variant="outlined"
        class="text-none text-caption"
        @click="backToDashboard"
        >Back to Dashboard</v-btn
      >
    </v-row>
  </div>
  <ThemesPluginsPanel v-if="store.activatedThemes"></ThemesPluginsPanel>
  <HealthPanel v-if="store.activatedHealth"></HealthPanel>
  <UptimePanel v-if="store.activatedUptime"></UptimePanel>
  <NoticesPanel v-if="store.activatedNotices"></NoticesPanel>
</template>

<style>
body,
html {
  height: revert !important;
}

.juggler-main-panel a:link {
  color: #333333;
  text-decoration: none;
}

.juggler-main-panel a:visited {
  color: #333333;
  text-decoration: none;
}

.juggler-main-panel a:hover {
  text-decoration: underline;
}

.wpjs-cp-table td {
  padding: 15px 0px;
}

.wpjs-timeline-icon {
  display: inline;
}
</style>
