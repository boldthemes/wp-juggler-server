<script setup>
import { useWpjsStore } from "./store.js";
import { onMounted, computed, ref } from "vue";
import { useQueryClient, useQuery, useMutation } from "@tanstack/vue-query";

const queryClient = useQueryClient();
const store = useWpjsStore();

const props = defineProps(["items"]);

const searchSites = ref("");

const pluginUrl = ref("");

const dialogInner = ref(false);

const dialogInstallPlugin = ref(false);
const dialogBulkType = ref("");
const updateActive = ref("");

const ajaxError = ref(false);
const ajaxErrorText = ref("");

const bulkActionError = ref(false);
const bulkActionText = ref(false);
const actionArray = ref([]);
const bulkActionInProgress = ref(false);
const bulkActionFinished = ref(false);
const bulkActionsNumber = ref(0);
const currentAction = ref(null);
const progressIndicator = ref(0);

const selectedSites = ref([]);

const headersSites = [
  { title: "Title", value: "title", align: "start", sortable: true },
  {
    title: "Url",
    key: "wp_juggler_server_site_url",
    align: "start",
    sortable: true,
  },
  { title: "Multisite", key: "network", align: "center", sortable: false },
];

const tab = ref(0);

const selectedActionPlugins = ref(null);
const selectedActionThemes = ref(null);

// Refresh Dialog Params
const dialogRefreshTabs = ref(false);
const refreshTabsInProgress = ref(false);
const refreshTabsFinished = ref(false);
const refreshProgressIndicator = ref(0);
const currentProgressIndex = ref(0);
const currentRefreshAction = ref("");

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

function sanitizeUrl(url) {
  // Trim whitespace from both ends
  url = url.trim();

  // Remove any additional unwanted characters using a regular expression
  url = url.replace(/\/+$/, "");

  return url;
}

function isValidUrl(string) {
  try {
    new URL(string);
    return true;
  } catch (err) {
    return false;
  }
}

async function checkFileExists(url) {
  const sanitizedUrl = sanitizeUrl(url);
  if (sanitizedUrl === "") return false;
  if (!isValidUrl(url)) return false;
  if (!sanitizedUrl.toLowerCase().endsWith('.zip')) return false;
  try {
    const response = await fetch(url, { method: "HEAD" });
    return response.ok;
  } catch (error) {
    return false;
  }
}

const persistDialog = computed(() => {
  return bulkActionInProgress.value && !bulkActionFinished.value;
});

const activeSites = computed(() => {
  return props.items.filter((site) => site.wp_juggler_site_activation === true);
  //return props.items;
});

async function startInstallPlugin() {
  bulkActionError.value = false;
  bulkActionText.value = false;
  actionArray.value = [];

  const fileExist = await checkFileExists(pluginUrl.value);

  if (!fileExist) {
    bulkActionError.value = "Plugin URL is not valid";
  } else {
    selectedSites.value.forEach((plugin) => {
      const maybePlugin = activeSites.value.find(
        (element) => element.id === plugin
      );
      if (maybePlugin !== undefined) {
        actionArray.value.push(maybePlugin);
      }
    });

    bulkActionText.value = "install plugin";
  }

  if (actionArray.value.length == 0) {
    bulkActionText.value = `There are no sites to ${bulkActionText.value} in your selection`;
  } else {
    bulkActionText.value = `You are going to ${bulkActionText.value} from ${pluginUrl.value} on these sites:`;
  }

  bulkActionInProgress.value = false;
  dialogInstallPlugin.value = true;
}

async function InitiateAction() {
  bulkActionsNumber.value = actionArray.value.length;
  bulkActionInProgress.value = true;
  bulkActionFinished.value = false;

  processAction();
}

async function processAction() {
  if (actionArray.value.length > 0) {
    currentAction.value = actionArray.value.shift();
    progressIndicator.value = Math.ceil(
      ((bulkActionsNumber.value - actionArray.value.length) /
        bulkActionsNumber.value) *
        100
    );

    await installPlugin(
        pluginUrl.value,
        currentAction.value.id,
        true
      );

    processAction();
    
  } else {
    queryClient.invalidateQueries({
      queryKey: ["wpjs-control-panel"],
    });

    bulkActionFinished.value = true;
    dialogInstallPlugin.value = false;
  }
}

async function installPlugin(pluginUrl, siteId, withoutRefresh = false) {
  updateActive.value = siteId;

  if (withoutRefresh) {
    updateActive.value = "";
  }

  let ret = {};

  try {
    const response = await doAjax({
      action: "wpjs-install-plugin", // the action to fire in the server
      siteId: siteId,
      pluginUrl: pluginUrl,
    });

    if (response.success) {
      ret = response.data;

      if (!withoutRefresh) {
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

    queryClient.invalidateQueries({
      queryKey: ["wpjs-control-panel"],
    });

    updateActive.value = "";
  }
}

</script>

<template>
  <div class="text-center pa-4">
    <v-dialog
      v-model="store.activatedPluginInstall"
      transition="dialog-bottom-transition"
      fullscreen
    >
      <v-card>
        <v-toolbar>
          <v-btn
            icon="mdi-close"
            @click="store.activatedPluginInstall = false"
          ></v-btn>

          <v-toolbar-title>Install Plugin</v-toolbar-title>

          <v-spacer></v-spacer>

          <v-toolbar-items> </v-toolbar-items>
        </v-toolbar>

        <v-card-text>
          <v-card flat>
            <v-card-title class="d-flex align-center pe-2 mb-6">
              <v-text-field
                v-model="pluginUrl"
                density="compact"
                label="Plugin URL"
                variant="outlined"
                flat
                hide-details
                single-line
                max-width="800"
              ></v-text-field>
              <v-btn
                class="ml-3 text-none text-caption"
                @click="startInstallPlugin()"
                variant="outlined"
                >Install
              </v-btn>
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
              :items="activeSites"
              :headers="headersSites"
              item-value="id"
              items-per-page="50"
              show-select
              v-model="selectedSites"
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
            </v-data-table>
          </v-card>
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

    <v-dialog
      v-model="dialogInstallPlugin"
      width="800"
      :persistent="persistDialog"
    >
      <v-card>
        <v-toolbar>
          <v-btn
            v-if="!(bulkActionInProgress && !bulkActionFinished)"
            icon="mdi-close"
            @click="dialogInstallPlugin = false"
          ></v-btn>

          <v-toolbar-title v-if="bulkActionError"
            >Install plugin</v-toolbar-title
          >
          <v-toolbar-title v-else>Install plugin</v-toolbar-title>
        </v-toolbar>

        <v-card-text>
          <v-sheet v-if="bulkActionError">
            {{ bulkActionError }}
          </v-sheet>
          <v-sheet v-else-if="!bulkActionInProgress" class="mb-4">
            <div class="my-8">{{ bulkActionText }}</div>

            <v-row
              class="wpjs-debug-table-row pl-5"
              v-for="item in actionArray"
            >
              <v-col class="text-left">
                <div class="wpjs-plugin-vul">{{ item.title }}</div>
              </v-col>
            </v-row>

            <v-btn
              v-if="actionArray.length > 0"
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
              <strong>{{ currentAction.title }}</strong>
            </div>
            <v-progress-linear
              color="light-blue"
              height="30"
              :model-value="progressIndicator"
              striped
            >
              <strong
                >{{ bulkActionsNumber - actionArray.length }}/{{
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

  <v-dialog v-model="dialogRefreshTabs" width="800" :persistent="true">
    <v-card>
      <v-toolbar>
        <v-btn
          v-if="!(refreshTabsInProgress && !refreshTabsFinished)"
          icon="mdi-close"
          @click="dialogRefreshTabs = false"
        ></v-btn>
        <v-toolbar-title>Plugins and Themes Data</v-toolbar-title>
      </v-toolbar>

      <v-card-text>
        <v-sheet
          v-if="refreshTabsInProgress && !refreshTabsFinished"
          class="mb-4"
          height="200"
        >
          <div class="my-8">
            Refresh in progress - do not close the window, you will interrupt
            the progress:
          </div>
          <div class="my-8">
            <strong>{{ currentRefreshAction }}</strong>
          </div>
          <v-progress-linear
            color="light-blue"
            height="30"
            :model-value="refreshProgressIndicator"
            striped
          >
            <strong
              >{{ currentProgressIndex + 1 }}/{{ refreshArr.length }}</strong
            >
          </v-progress-linear>
        </v-sheet>

        <v-sheet
          v-else-if="refreshTabsInProgress && refreshTabsFinished"
          class="mb-4"
          height="200"
        >
          <div class="my-8">Refresh finished</div>
        </v-sheet>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<style>
.wpjs-plugin-vul {
  font-size: 14px;
}

.wpjs-timestamp {
  font-size: 14px;
}
</style>
