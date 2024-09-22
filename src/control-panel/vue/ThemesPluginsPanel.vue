<script setup>
import { useWpjsStore } from "./store.js";
import { onMounted, computed, ref } from "vue";
import { useQueryClient, useQuery, useMutation } from "@tanstack/vue-query";

const store = useWpjsStore();

const search = ref("");

const dialogInner = ref(false);
const vulnerabilitiesItem = ref(null);

const dialogChecksum = ref(false);
const checksumItem = ref(null);

const dialogBulkAction = ref(false);

const refreshActive = ref(false);
const updateActive = ref("");
const deactivateActive = ref("");
const activateActive = ref("");
const activateNetworkActive = ref("");

const ajaxError = ref(false);
const ajaxErrorText = ref("");

const selectedPlugins = ref([]);
const bulkActionError = ref(false);
const bulkActionText = ref(false);
const actionArrayFiltered = ref([]);
const bulkActionInProgress = ref(false);
const bulkActionFinished = ref(false);
const bulkActionsNumber = ref(0);
const currentAction = ref(null);
const progressIndicator = ref(0);

const bulkActionsPlugins = [
  {
    text: "Update Plugins",
    value: "update",
  },
  {
    text: "Activate Plugins",
    value: "activate",
  },
  {
    text: "Network Activate Plugins",
    value: "network_activate",
  },
  {
    text: "Deactivate Plugins",
    value: "deactivate",
  },
];

const plugin_headers = [
  { title: "Plugin Name", value: "Name", align: "start", sortable: true },
  {
    title: "Active",
    key: "active",
    align: "center",
    sortable: false,
  },
  { title: "Version", value: "Version", align: "center", sortable: true },
  {
    title: "Update",
    key: "update",
    align: "center",
    sortable: false,
  },
  {
    title: "Vulnerabilities",
    key: "vulnerabilities",
    align: "center",
    sortable: true,
  },
  {
    title: "Cheksum",
    key: "checksum",
    align: "start",
    sortable: true,
  },
  {
    title: "Source",
    key: "source",
    align: "start",
    sortable: true,
  },
  {
    title: "Actions",
    key: "actions",
    align: "start",
    sortable: true,
  },
];

const theme_headers = [
  { title: "Themes Name", value: "Name", align: "start", sortable: true },
  { title: "Author", value: "Author", align: "left", sortable: true },
  {
    title: "Active",
    key: "active",
    align: "center",
    sortable: false,
  },
  { title: "Version", value: "Version", align: "center", sortable: true },
  {
    title: "Child Theme",
    key: "child",
    align: "center",
    sortable: false,
  },
  {
    title: "Update",
    key: "update",
    align: "center",
    sortable: false,
  },
];

const tab = ref(0);

const selectedActionPlugins = ref(null);

const queryClient = useQueryClient();
const { isLoading, isError, isFetching, data, error, refetch } = useQuery({
  queryKey: ["wpjs-plugins-panel", store.activatedSite.id],
  queryFn: getPluginsPanel,
});

async function getPluginsPanel() {
  let ret = {};
  const response = await doAjax({
    action: "wpjs-get-plugins-panel", // the action to fire in the server
    siteId: store.activatedSite.id,
  });

  ret = response.data;
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

const plugins_data = computed(() => {
  if (data.value) {
    return Object.values(data.value.plugins_data);
  } else {
    return [];
  }
});

const themes_data = computed(() => {
  if (data.value) {
    return Object.values(data.value.themes_data);
  } else {
    return [];
  }
});

function openVulnerabilities(item) {
  vulnerabilitiesItem.value = item;
  dialogInner.value = true;
}

function openChecksum(item) {
  checksumItem.value = item;
  dialogChecksum.value = true;
}

async function refreshPlugins(e, withoutIndicator = false) {
  refreshActive.value = !withoutIndicator;

  let ret = {};

  try {
    const response = await doAjax({
      action: "wpjs-refresh-plugins", // the action to fire in the server
      siteId: store.activatedSite.id,
    });

    if (response.success) {
      ret = response.data;

      queryClient.invalidateQueries({
        queryKey: ["wpjs-plugins-panel", store.activatedSite.id],
      });
      queryClient.invalidateQueries({
        queryKey: ["wpjs-control-panel"],
      });

      refreshActive.value = false;
    } else {
      throw new Error(`${response.data.code} - ${response.data.message}`);
    }
  } catch (error) {
    ajaxError.value = true;
    ajaxErrorText.value = error.message;
    refreshActive.value = false;
  }
}

async function updatePlugin(pluginSlug, withoutRefresh = false) {
  updateActive.value = pluginSlug;

  if (withoutRefresh) {
    updateActive.value = "";
  }

  let ret = {};

  try {
    const response = await doAjax({
      action: "wpjs-update-plugin", // the action to fire in the server
      siteId: store.activatedSite.id,
      pluginSlug: pluginSlug,
      withoutRefresh: withoutRefresh,
    });

    if (response.success) {
      ret = response.data;

      if (!withoutRefresh) {
        queryClient.invalidateQueries({
          queryKey: ["wpjs-plugins-panel", store.activatedSite.id],
        });

        queryClient.invalidateQueries({
          queryKey: ["wpjs-control-panel"],
        });
      }

      updateActive.value = "";
    } else {
      throw new Error(`${response.data.code} - ${response.data.message}`);
    }
  } catch (error) {
    ajaxError.value = true;
    ajaxErrorText.value = error.message;

    updateActive.value = "";
  }
}

async function deactivatePlugin(pluginSlug, withoutRefresh = false) {
  deactivateActive.value = pluginSlug;

  if (withoutRefresh) {
    deactivateActive.value = "";
  }

  let ret = {};

  try {
    const response = await doAjax({
      action: "wpjs-deactivate-plugin", // the action to fire in the server
      siteId: store.activatedSite.id,
      pluginSlug: pluginSlug,
      withoutRefresh: withoutRefresh,
    });

    if (response.success) {
      ret = response.data;

      if (!withoutRefresh) {
        queryClient.invalidateQueries({
          queryKey: ["wpjs-plugins-panel", store.activatedSite.id],
        });
        queryClient.invalidateQueries({
          queryKey: ["wpjs-control-panel"],
        });
      }

      deactivateActive.value = "";
    } else {
      throw new Error(`${response.data.code} - ${response.data.message}`);
    }
  } catch (error) {
    ajaxError.value = true;
    ajaxErrorText.value = error.message;

    deactivateActive.value = "";
  }
}

async function activatePlugin(pluginSlug, networkWide, withoutRefresh = false) {
  if (networkWide) {
    activateNetworkActive.value = pluginSlug;
  } else {
    activateActive.value = pluginSlug;
  }

  if (withoutRefresh) {
    activateActive.value = "";
    activateNetworkActive.value = "";
  }

  let ret = {};

  try {
    const response = await doAjax({
      action: "wpjs-activate-plugin", // the action to fire in the server
      siteId: store.activatedSite.id,
      pluginSlug: pluginSlug,
      networkWide: networkWide,
      withoutRefresh: withoutRefresh,
    });

    if (response.success) {
      ret = response.data;

      if (!withoutRefresh) {
        queryClient.invalidateQueries({
          queryKey: ["wpjs-plugins-panel", store.activatedSite.id],
        });
        queryClient.invalidateQueries({
          queryKey: ["wpjs-control-panel"],
        });
      }

      activateActive.value = "";
      activateNetworkActive.value = "";
    } else {
      throw new Error(`${response.data.code} - ${response.data.message}`);
    }
  } catch (error) {
    ajaxError.value = true;
    ajaxErrorText.value = error.message;

    activateActive.value = "";
    activateNetworkActive.value = "";
  }
}

async function doBulkAction() {
  bulkActionError.value = false;
  bulkActionText.value = false;
  actionArrayFiltered.value = [];

  if (!selectedActionPlugins.value) {
    bulkActionError.value = "No action selected";
  } else if (selectedPlugins.value.length == 0) {
    bulkActionError.value = "No plugin selected";
  } else {
    let actionArray = [];
    selectedPlugins.value.forEach((plugin) => {
      const maybePlugin = plugins_data.value.find(
        (element) => element.File === plugin
      );
      if (maybePlugin !== undefined) {
        actionArray.push(maybePlugin);
      }
    });

    if (selectedActionPlugins.value.value == "update") {
      actionArrayFiltered.value = actionArray.filter(
        (element) => element.Update != false
      );
      bulkActionText.value = "update";
    }

    if (selectedActionPlugins.value.value == "activate") {
      actionArrayFiltered.value = actionArray.filter(
        (element) =>
          (element.Active != true && element.Multisite != true) ||
          (element.Active != true &&
            element.NetworkActive != true &&
            element.Multisite == true &&
            element.Network != true)
      );
      bulkActionText.value = "activate";
    }

    if (selectedActionPlugins.value.value == "network_activate") {
      actionArrayFiltered.value = actionArray.filter(
        (element) =>
          (element.Active != true &&
            element.NetworkActive != true &&
            element.Multisite == true &&
            element.Network != true) ||
          (element.Active != true &&
            element.NetworkActive != true &&
            element.Multisite == true &&
            element.Network == true)
      );
      bulkActionText.value = "network activate";
    }

    if (selectedActionPlugins.value.value == "deactivate") {
      actionArrayFiltered.value = actionArray.filter(
        (element) => element.Active == true || element.NetworkActive == true
      );
      bulkActionText.value = "deactivate";
    }
  }

  if (actionArrayFiltered.value.length == 0) {
    bulkActionText.value = `There are no plugins to ${bulkActionText.value} in your selection`;
  } else {
    bulkActionText.value = `You are going to ${bulkActionText.value} these plugins:`;
  }

  bulkActionInProgress.value = false;
  dialogBulkAction.value = true;
}

async function InitiateAction() {
  bulkActionsNumber.value = actionArrayFiltered.value.length;
  bulkActionInProgress.value = true;
  bulkActionFinished.value = false;

  processAction();
}

async function processAction() {
  if (actionArrayFiltered.value.length > 0) {
    currentAction.value = actionArrayFiltered.value.shift();
    progressIndicator.value = Math.ceil(
      ((bulkActionsNumber.value - actionArrayFiltered.value.length) /
        bulkActionsNumber.value) *
        100
    );

    if (selectedActionPlugins.value.value == "update") {
      await updatePlugin(currentAction.value.Slug, true);
    }

    if (selectedActionPlugins.value.value == "activate") {
      await activatePlugin(currentAction.value.Slug, false, true);
    }

    if (selectedActionPlugins.value.value == "network_activate") {
      await activatePlugin(currentAction.value.Slug, true, true);
    }

    if (selectedActionPlugins.value.value == "deactivate") {
      await deactivatePlugin(currentAction.value.Slug, true);
    }

    processAction();
  } else {
    currentAction.value = {
      Name: "Refreshing plugin data",
    };
    await refreshPlugins(null, true);
    bulkActionFinished.value = true;
  }
}

//

const persistDialog = computed(() => {
  return bulkActionInProgress.value && !bulkActionFinished.value;
});
</script>

<template>
  <div class="text-center pa-4">
    <v-dialog
      v-model="store.activatedThemes"
      transition="dialog-bottom-transition"
      fullscreen
    >
      <v-card>
        <v-toolbar>
          <v-btn
            icon="mdi-close"
            @click="store.activatedThemes = false"
          ></v-btn>

          <v-toolbar-title>{{ store.activatedSite.title }} </v-toolbar-title>

          <v-spacer></v-spacer>

          <v-toolbar-items> </v-toolbar-items>
        </v-toolbar>

        <v-card-text v-if="data">
          <v-sheet
            class="pa-4 text-right mx-auto"
            elevation="0"
            width="100%"
            rounded="lg"
          >
            <div v-if="data.wp_juggler_plugins_timestamp">
              <v-icon class="me-1 pb-1" icon="mdi-refresh" size="18"></v-icon>
              {{ data.wp_juggler_plugins_timestamp }}
              <v-btn
                class="ml-3 text-none text-caption"
                :loading="refreshActive"
                @click="refreshPlugins"
                variant="outlined"
                >Refresh
              </v-btn>
            </div>

            <div v-else>
              <v-icon class="me-1 pb-1" icon="mdi-refresh" size="18"></v-icon>
              Never
              <v-btn
                class="ml-3 text-none text-caption"
                :loading="refreshActive"
                @click="refreshPlugins"
                variant="outlined"
                >Refresh
              </v-btn>
            </div>
          </v-sheet>

          <v-card>
            <v-tabs v-model="tab" bg-color="surface">
              <v-tab value="plugins">Plugins</v-tab>
              <v-tab value="themes">Themes</v-tab>
            </v-tabs>

            <v-card-text class="mt-4">
              <v-tabs-window v-model="tab">
                <v-tabs-window-item
                  value="plugins"
                  transition="false"
                  reverse-transition="false"
                >
                  <v-divider></v-divider>

                  <v-sheet>
                    <v-row
                      align="center"
                      justify="center"
                      alignContent="center"
                      class="px-4"
                    >
                      <v-select
                        v-model="selectedActionPlugins"
                        :items="bulkActionsPlugins"
                        item-title="text"
                        item-value="value"
                        return-object
                        single-line
                        density="compact"
                        label="Bulk Actions"
                        max-width="300"
                        variant="outlined"
                        class="mt-6"
                      >
                      </v-select>
                      <v-btn
                        class="ml-3 text-none text-caption"
                        @click="doBulkAction()"
                        variant="outlined"
                        >Apply
                      </v-btn>
                      <v-spacer></v-spacer>

                      <v-text-field
                        v-model="search"
                        density="compact"
                        label="Search"
                        prepend-inner-icon="mdi-magnify"
                        variant="outlined"
                        flat
                        hide-details
                        single-line
                        max-width="800"
                      ></v-text-field>
                    </v-row>
                    <v-row>
                      <v-data-table
                        v-model:search="search"
                        :items="plugins_data"
                        :headers="plugin_headers"
                        item-value="File"
                        items-per-page="50"
                        show-select
                        v-model="selectedPlugins"
                        class="pb-4"
                      >
                        <template v-slot:item.active="{ item }">
                          <div v-if="item.Active && !item.NetworkActive">
                            <v-icon
                              color="success"
                              icon="mdi-check-bold"
                              size="large"
                              class="rm-4"
                            ></v-icon>
                          </div>
                          <div v-if="item.NetworkActive">
                            <v-icon
                              color="success"
                              icon="mdi-check-network-outline"
                              size="large"
                              class="rm-4"
                            ></v-icon>
                          </div>
                        </template>

                        <template v-slot:item.update="{ item }">
                          <div v-if="item.Update">
                            <v-icon
                              color="success"
                              icon="mdi-check-bold"
                              size="large"
                              class="rm-4"
                            ></v-icon>
                            {{ item.UpdateVersion }}
                          </div>
                        </template>

                        <template v-slot:item.vulnerabilities="{ item }">
                          <div
                            v-if="
                              item.Vulnerabilities.length > 0 &&
                              item.Wporg &&
                              !item.WpJuggler
                            "
                          >
                            <v-icon
                              color="error"
                              icon="mdi-bug-check-outline"
                              size="large"
                              class="mr-1"
                            ></v-icon>
                            {{ item.Vulnerabilities.length }}
                            <v-btn
                              class="ml-3 text-none text-caption"
                              @click="openVulnerabilities(item)"
                              variant="outlined"
                              >Details
                            </v-btn>
                          </div>
                          <div v-else-if="!item.Wporg || item.WpJuggler">
                            <v-icon
                              color="blue-lighten-5"
                              icon="mdi-help"
                              size="large"
                              class="rm-4"
                            ></v-icon>
                          </div>
                        </template>

                        <template v-slot:item.checksum="{ item }">
                          <div
                            v-if="
                              !item.Checksum && !item.WpJuggler && item.Wporg
                            "
                          >
                            <v-icon
                              color="error"
                              icon="mdi-alert-outline"
                              size="large"
                              class="mr-1"
                            ></v-icon>
                            <v-btn
                              class="ml-3 text-none text-caption"
                              @click="openChecksum(item)"
                              variant="outlined"
                              >Details
                            </v-btn>
                          </div>
                          <div v-else-if="!item.Wporg || item.WpJuggler">
                            <v-icon
                              color="blue-lighten-5"
                              icon="mdi-help"
                              size="large"
                              class="rm-4"
                            ></v-icon>
                          </div>
                          <div v-else>
                            <v-icon
                              color="success"
                              icon="mdi-check-bold"
                              size="large"
                              class="rm-4"
                            ></v-icon>
                          </div>
                        </template>

                        <template v-slot:item.source="{ item }">
                          <div v-if="item.Tgmpa">
                            <v-icon
                              color="grey-lighten-1"
                              icon="mdi-package-variant-closed"
                              size="large"
                              class="rm-4"
                            ></v-icon>
                          </div>
                          <div v-else-if="item.WpJuggler">
                            <v-icon
                              color="grey-lighten-1"
                              icon="mdi-lan"
                              size="large"
                              class="rm-4"
                            ></v-icon>
                          </div>
                          <div v-else-if="item.Wporg">
                            <v-icon
                              color="grey-lighten-1"
                              icon="mdi-wordpress"
                              size="large"
                              class="mr-1"
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
                        </template>
                        <template v-slot:item.actions="{ item }">
                          <v-btn
                            v-if="item.Active || item.NetworkActive"
                            :loading="item.Slug == deactivateActive"
                            @click="deactivatePlugin(item.Slug)"
                            class="ml-3 text-none text-caption"
                            variant="outlined"
                            >Deactivate
                          </v-btn>
                          <v-btn
                            v-if="!item.Active && !item.Multisite"
                            :loading="item.Slug == activateActive"
                            @click="activatePlugin(item.Slug, false)"
                            class="ml-3 text-none text-caption"
                            variant="outlined"
                            >Activate
                          </v-btn>
                          <v-btn
                            v-if="
                              !item.Active &&
                              !item.NetworkActive &&
                              item.Multisite &&
                              !item.Network
                            "
                            :loading="item.Slug == activateActive"
                            @click="activatePlugin(item.Slug, false)"
                            class="ml-3 text-none text-caption"
                            variant="outlined"
                            >Activate
                          </v-btn>
                          <v-btn
                            v-if="
                              !item.Active &&
                              !item.NetworkActive &&
                              item.Multisite &&
                              !item.Network
                            "
                            :loading="item.Slug == activateNetworkActive"
                            @click="activatePlugin(item.Slug, true)"
                            class="ml-3 text-none text-caption"
                            variant="outlined"
                            >Network Activate
                          </v-btn>
                          <v-btn
                            v-if="
                              !item.Active &&
                              !item.NetworkActive &&
                              item.Multisite &&
                              item.Network
                            "
                            :loading="item.Slug == activateNetworkActive"
                            @click="activatePlugin(item.Slug, true)"
                            class="ml-3 text-none text-caption"
                            variant="outlined"
                            >Network Activate
                          </v-btn>
                          <v-btn
                            v-if="item.Update"
                            :loading="item.Slug == updateActive"
                            @click="updatePlugin(item.Slug)"
                            color="#2196f3"
                            class="text-none text-caption ml-3"
                            variant="outlined"
                            >Update
                          </v-btn>
                        </template>
                      </v-data-table>
                    </v-row>
                  </v-sheet>
                </v-tabs-window-item>

                <v-tabs-window-item
                  value="themes"
                  transition="false"
                  reverse-transition="false"
                  class="px-4"
                >
                  <v-divider></v-divider>

                  <v-sheet>
                    <v-row align="center" class="py-6">
                      <v-spacer></v-spacer>

                      <v-text-field
                        v-model="search"
                        density="compact"
                        label="Search"
                        prepend-inner-icon="mdi-magnify"
                        variant="outlined"
                        flat
                        hide-details
                        single-line
                        max-width="800"
                      ></v-text-field>
                    </v-row>

                    <v-data-table
                      v-model:search="search"
                      :items="themes_data"
                      :headers="theme_headers"
                      item-key="id"
                      items-per-page="50"
                    >
                      <template v-slot:item.active="{ item }">
                        <div v-if="item.Active && !item.Network">
                          <v-icon
                            color="success"
                            icon="mdi-check-bold"
                            size="large"
                            class="rm-4"
                          ></v-icon>
                        </div>
                        <div v-if="item.Active && item.Network">
                          <v-icon
                            color="success"
                            icon="mdi-check-network-outline"
                            size="large"
                            class="rm-4"
                          ></v-icon>
                        </div>
                      </template>

                      <template v-slot:item.child="{ item }">
                        <div v-if="item.IsChildTheme">
                          <v-icon
                            color="success"
                            icon="mdi-check-bold"
                            size="large"
                            class="rm-4"
                          ></v-icon>
                        </div>
                      </template>

                      <template v-slot:item.update="{ item }">
                        <div v-if="item.Update">
                          <v-icon
                            color="success"
                            icon="mdi-check-bold"
                            size="large"
                            class="rm-4"
                          ></v-icon>
                          {{ item.UpdateVersion }}
                        </div>
                      </template>
                    </v-data-table>
                  </v-sheet>
                </v-tabs-window-item>
              </v-tabs-window>
            </v-card-text>
          </v-card>
        </v-card-text>

        <v-card-text v-else>
          <v-skeleton-loader type="table" class="mt-15"> </v-skeleton-loader>
        </v-card-text>
      </v-card>

      <v-snackbar v-model="ajaxError" color="red-lighten-2">
        {{ ajaxErrorText }}

        <template v-slot:actions>
          <v-btn
            color="red-lighten-4"
            variant="text"
            @click="ajaxError = false"
          >
            Close
          </v-btn>
        </template>
      </v-snackbar>
    </v-dialog>

    <v-dialog v-model="dialogInner" min-width="600">
      <v-card>
        <v-toolbar>
          <v-btn icon="mdi-close" @click="dialogInner = false"></v-btn>

          <v-toolbar-title
            >List of vulnerabilities - {{ vulnerabilitiesItem.Name }} -
            {{ vulnerabilitiesItem.Version }}</v-toolbar-title
          >
        </v-toolbar>

        <v-card-text>
          <v-sheet v-for="vul in vulnerabilitiesItem.Vulnerabilities">
            <div class="text-h7">
              <strong>{{ vul.name }}</strong>
            </div>
            <div v-if="'cwe' in vul.impact" class="ml-6 mt-2 wpjs-plugin-vul">
              <div>
                {{ vul.impact.cwe[0].name }}
              </div>
              <div>
                {{ vul.impact.cwe[0].description }}
              </div>
            </div>

            <div class="mt-4 ml-6"><strong>Sources:</strong></div>

            <div v-for="src in vul.source" class="mt-2 ml-6 wpjs-plugin-vul">
              <div class="ml-4">
                {{ src.date }} -
                <a :href="src.link" target="_blank">{{ src.name }}</a>
              </div>
              <div class="ml-4">
                {{ src.description }}
              </div>
            </div>
            <v-divider class="mt-4 mb-4"></v-divider>
          </v-sheet>
        </v-card-text>
      </v-card>
    </v-dialog>

    <v-dialog v-model="dialogChecksum" min-width="600">
      <v-card>
        <v-toolbar>
          <v-btn icon="mdi-close" @click="dialogChecksum = false"></v-btn>

          <v-toolbar-title
            >List of Checksum Errors - {{ checksumItem.Name }} -
            {{ checksumItem.Version }}</v-toolbar-title
          >
        </v-toolbar>

        <v-card-text>
          <v-sheet v-if="checksumItem.ChecksumDetails.length > 0">
            <v-row class="wpjs-debug-table-row pl-5">
              <v-col class="text-left">
                <strong>File</strong>
              </v-col>
              <v-col class="text-left">
                <strong>Checksum problem</strong>
              </v-col>
            </v-row>
            <v-row
              class="wpjs-debug-table-row pl-5"
              v-for="item in checksumItem.ChecksumDetails"
            >
              <v-col class="text-left">
                <div class="wpjs-plugin-vul">{{ item.file }}</div>
              </v-col>
              <v-col class="text-left">
                <div class="wpjs-plugin-vul">{{ item.message }}</div>
              </v-col>
            </v-row>
          </v-sheet>
        </v-card-text>
      </v-card>
    </v-dialog>

    <v-dialog
      v-model="dialogBulkAction"
      width="800"
      :persistent="persistDialog"
    >
      <v-card>
        <v-toolbar>
          <v-btn
            v-if="!(bulkActionInProgress && !bulkActionFinished)"
            icon="mdi-close"
            @click="dialogBulkAction = false"
          ></v-btn>

          <v-toolbar-title v-if="bulkActionError">Bulk Action</v-toolbar-title>
          <v-toolbar-title v-else>{{
            selectedActionPlugins.text
          }}</v-toolbar-title>
        </v-toolbar>

        <v-card-text>
          <v-sheet v-if="bulkActionError">
            {{ bulkActionError }}
          </v-sheet>
          <v-sheet v-else-if="!bulkActionInProgress" class="mb-4">
            <div class="my-8">{{ bulkActionText }}</div>

            <v-row
              class="wpjs-debug-table-row pl-5"
              v-for="item in actionArrayFiltered"
            >
              <v-col class="text-left">
                <div class="wpjs-plugin-vul">{{ item.Name }}</div>
              </v-col>
            </v-row>

            <v-btn
              v-if="actionArrayFiltered.length > 0"
              class="ml-3 mt-10 text-none text-caption"
              @click="InitiateAction()"
              variant="outlined"
              >Confirm
            </v-btn>
          </v-sheet>
          <v-sheet
            v-else-if="bulkActionInProgress && !bulkActionFinished"
            class="mb-4"
            height="200"
          >
            <div class="my-8">
              Bulk action in progress - do not close the window, you will
              interrupt the progress:
            </div>
            <div class="my-8">
              <strong>{{ currentAction.Name }}</strong>
            </div>
            <v-progress-linear
              color="light-blue"
              height="30"
              :model-value="progressIndicator"
              striped
            >
              <strong
                >{{ bulkActionsNumber - actionArrayFiltered.length }}/{{
                  bulkActionsNumber
                }}</strong
              >
            </v-progress-linear>
          </v-sheet>

          <v-sheet
            v-else-if="bulkActionInProgress && bulkActionFinished"
            class="mb-4"
            height="200"
          >
            <div class="my-8">Bulk action finished</div>
          </v-sheet>
        </v-card-text>
      </v-card>
    </v-dialog>
  </div>
</template>

<style>
.wpjs-plugin-vul {
  font-size: 14px;
}
</style>
