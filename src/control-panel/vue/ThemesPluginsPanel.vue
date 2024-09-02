<script setup>
import { useWpjsStore } from "./store.js";
import { onMounted, computed, ref } from "vue";
import { useQueryClient, useQuery, useMutation } from "@tanstack/vue-query";

const store = useWpjsStore();

const dialog = ref(false);

const tab = ref(0);

console.log(store.activatedSite);
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

          <v-toolbar-title
            >Themes and Plugins 1 - {{ store.activatedSite.title }}
          </v-toolbar-title>

          <v-spacer></v-spacer>

          <v-toolbar-items> </v-toolbar-items>
        </v-toolbar>

        <v-card-text>
          <v-card>
            <v-tabs v-model="tab" bg-color="surface">
              <v-tab value="plugins">Plugins</v-tab>
              <v-tab value="themes">Themes</v-tab>
            </v-tabs>

            <v-card-text>
              <v-tabs-window v-model="tab">
                <v-tabs-window-item value="plugins">
                  <v-sheet
                    class="pa-4 text-right mx-auto"
                    elevation="0"
                    width="100%"
                    rounded="lg"
                  >
                    <div
                      v-if="
                        store.activatedSite.wp_juggler_plugins_summary_timestamp
                      "
                    >
                      <v-icon
                        class="me-1 pb-1"
                        icon="mdi-refresh"
                        size="18"
                      ></v-icon>
                      {{
                        store.activatedSite.wp_juggler_plugins_summary_timestamp
                      }}
                      <v-btn class="ml-3 text-none text-caption"
                        >Refresh
                      </v-btn>
                    </div>

                    <div v-else>
                      <v-icon
                        class="me-1 pb-1"
                        icon="mdi-refresh"
                        size="18"
                      ></v-icon>
                      Never
                      <v-btn class="ml-3 text-none text-caption"
                        >Refresh
                      </v-btn>
                    </div>
                  </v-sheet>
                  <v-divider></v-divider>

                  <v-sheet>
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

                    <v-data-table
                      v-model:search="search"
                      :items="data"
                      :headers="headers"
                      item-key="id"
                      show-select
                      v-model:expanded="expanded"
                      show-expand
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

                      <template v-slot:item.events="{ item }">
                        <div v-if="item.wp_juggler_automatic_login">
                          <v-icon
                            color="#2196f3"
                            icon="mdi-email-alert-outline"
                            size="large"
                            class="rm-4"
                          ></v-icon>
                        </div>
                      </template>

                      <template v-slot:item.uptime="{ item }">
                        <div v-if="item.wp_juggler_site_activation">
                          <v-icon
                            v-for="day in item.wp_juggler_uptime_stats
                              .uptime_timeline"
                            :color="calculateColor(day)"
                            icon="mdi-square"
                            size="large"
                            class="rm-4"
                          ></v-icon>
                        </div>
                        <div v-if="!item.wp_juggler_site_activation">
                          Inactive
                        </div>
                      </template>

                      <template v-slot:item.updates="{ item }">
                        <div v-if="item.wp_juggler_site_activation">
                          <div v-if="item.wp_juggler_plugins_summary">
                            <v-icon
                              v-if="
                                item.wp_juggler_plugins_summary
                                  .vulnerabilities_num > 0
                              "
                              color="error"
                              icon="mdi-bug-check-outline"
                              size="large"
                              class="rm-4"
                            ></v-icon>
                            <v-icon
                              v-else-if="
                                item.wp_juggler_plugins_summary.updates_num >
                                  0 ||
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
                        <div v-if="!item.wp_juggler_site_activation">
                          Inactive
                        </div>
                      </template>

                      <template v-slot:item.checksum="{ item }">
                        <div v-if="item.wp_juggler_site_activation">
                          <div
                            v-if="
                              item.wp_juggler_plugins_checksum &&
                              item.wp_juggler_core_checksum
                            "
                          >
                            <v-icon
                              v-if="
                                item.wp_juggler_plugins_checksum.failures > 0 ||
                                item.wp_juggler_core_checksum.errors
                              "
                              color="error"
                              icon="mdi-alert-outline"
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
                        <div v-if="!item.wp_juggler_site_activation">
                          Inactive
                        </div>
                      </template>

                      <template v-slot:item.links="{ item }">
                        <div v-if="item.wp_juggler_site_activation">
                          <v-btn
                            v-for="button in item.wp_juggler_login_tools"
                            variant="elevated"
                            @click="gotoUrl(button.wp_juggler_tool_url)"
                            class="text-none text-caption mr-1 ml-1"
                            >{{ button.wp_juggler_tool_label }}</v-btn
                          >
                        </div>
                        <div v-if="!item.wp_juggler_site_activation">
                          Inactive
                        </div>
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
                            variant="elevated"
                            class="text-none text-caption"
                            prepend-icon="mdi-login"
                            @click="gotoUrl(item.wp_juggler_login_url)"
                            >Login</v-btn
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
                            variant="elevated"
                            class="text-none text-caption"
                            prepend-icon="mdi-account-remove"
                            @click="gotoUrl(item.wp_juggler_login_url)"
                            >Login</v-btn
                          >
                        </div>
                        <div v-if="!item.wp_juggler_site_activation">
                          Inactive
                        </div>
                      </template>

                      <template v-slot:expanded-row="{ columns, item }">
                        <ExpandedRow
                          :columns="columns"
                          :item="item"
                        ></ExpandedRow>
                      </template>
                    </v-data-table>
                  </v-sheet>
                </v-tabs-window-item>

                <v-tabs-window-item value="themes"> Two </v-tabs-window-item>
              </v-tabs-window>
            </v-card-text>
          </v-card>
        </v-card-text>
      </v-card>
    </v-dialog>
  </div>
</template>

<style></style>
