<script setup>
import { useWpjsStore } from "./store.js";
import { onMounted, computed, ref } from "vue";
import { useQueryClient, useQuery, useMutation } from "@tanstack/vue-query";

const store = useWpjsStore();
const passedOpen = ref( false );

const search = ref("");

const dialogInner = ref(false);
const vulnerabilitiesItem = ref(null);

const tab = ref(0);

const { isLoading, isError, isFetching, data, error, refetch } = useQuery({
  queryKey: ["wpjs-health-panel", store.activatedSite.id ],
  queryFn: getHealthPanel,
});

async function getHealthPanel() {
  let ret = {};
  const response = await doAjax({
    action: "wpjs-get-health-panel", // the action to fire in the server
    siteId: store.activatedSite.id
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
    throw error;
  }
}

const recommendations = computed(() => {
  return data.value.wp_juggler_health_data_status.filter(item => item.status === 'recommended' && item.test !== 'rest_availability');
})

const goods = computed(() => {
  return data.value.wp_juggler_health_data_status.filter(item => item.status === 'good' && item.test !== 'rest_availability');
})

const openIcon = computed(() => {
  return passedOpen.value? 'mdi-chevron-up': 'mdi-chevron-down'
})

function debugFields( fieldArray ){
  return fieldArray.filter(item => item.debug !== 'loading...');
}

</script>

<template>
  <div class="text-center pa-4">
    <v-dialog v-model="store.activatedHealth" transition="dialog-bottom-transition" fullscreen>
      <v-card>
        <v-toolbar>
          <v-btn icon="mdi-close" @click="store.activatedHealth = false"></v-btn>

          <v-toolbar-title>{{ store.activatedSite.title }}
          </v-toolbar-title>

          <v-spacer></v-spacer>

          <v-toolbar-items> </v-toolbar-items>
        </v-toolbar>

        <v-card-text>
          <v-sheet class="pa-4 text-right mx-auto" elevation="0" width="100%" rounded="lg">
            <div v-if="data">
              <v-icon class="me-1 pb-1" icon="mdi-refresh" size="18"></v-icon>
              {{
                data.wp_juggler_health_data_timestamp
              }}
              <v-btn class="ml-3 text-none text-caption">Refresh
              </v-btn>
            </div>

            <div v-else>
              <v-icon class="me-1 pb-1" icon="mdi-refresh" size="18"></v-icon>
              Never
              <v-btn class="ml-3 text-none text-caption">Refresh
              </v-btn>
            </div>
          </v-sheet>

          <v-card v-if="data">
            <v-tabs v-model="tab" bg-color="surface">
              <v-tab value="status" >Status</v-tab>
              <v-tab value="info">Info</v-tab>
            </v-tabs>

            <v-card-text>
              <v-tabs-window v-model="tab">
                <v-tabs-window-item value="status" transition="false" reverse-transition="false">

                  <v-sheet max-width="800" class="align-center justify-center text-center mx-auto px-4 pb-4">

                    <v-sheet max-width="800" class="align-left justify-left text-left mb-10">
                      <div class="text-h6">Site Health Status</div>
                      <div class="mt-3 mb-4">The site health check shows information about your WordPress configuration
                        and items that may need your attention.</div>

                      <div class="text-h6">{{ recommendations.length }} recommended improvements</div>
                      <div class="mt-3 mb-4">Recommended items are considered beneficial to your site, although not as
                        important to prioritize as a critical issue, they may include improvements to things such as;
                        Performance, user experience, and more.</div>

                      <v-expansion-panels class="mt-8" variant="accordion">
                        <v-expansion-panel v-for="recommendation in recommendations">
                          <v-expansion-panel-title>
                            {{ recommendation.label }}
                            <v-spacer></v-spacer>
                            <div class="mr-3 border-sm pa-2 blue">{{ recommendation.badge.label }}</div>
                          </v-expansion-panel-title>
                          <v-expansion-panel-text >
                            <div class="wpjs-health-panel-description" v-html="recommendation.description" ></div>
                            <div class="wpjs-health-panel-actions" v-html="recommendation.actions" ></div>
                          </v-expansion-panel-text>
                        </v-expansion-panel>
                      </v-expansion-panels>
                    </v-sheet>

                    <v-btn class="ml-3 text-none text-caption" :append-icon="openIcon" @click="passedOpen = !passedOpen">Passed tests</v-btn>

                    <v-sheet v-if="passedOpen" max-width="800" class="align-left justify-left text-left my-10" >

                      <div class="text-h6">{{ goods.length }} items with no issues detected</div>


                      <v-expansion-panels class="mt-8" variant="accordion">
                        <v-expansion-panel v-for="good in goods">
                          <v-expansion-panel-title>
                            {{ good.label }}
                            <v-spacer></v-spacer>
                            <div class="mr-3 border-sm pa-2 blue">{{ good.badge.label }}</div>
                          </v-expansion-panel-title>
                          <v-expansion-panel-text>
                            <div class="wpjs-health-panel-description" v-html="good.description" ></div>
                          </v-expansion-panel-text>
                        </v-expansion-panel>

                      </v-expansion-panels>
                    </v-sheet>

                  </v-sheet>
                </v-tabs-window-item>

                <v-tabs-window-item value="info" transition="false" reverse-transition="false">

                  <v-sheet max-width="800" class="align-center justify-center text-center mx-auto px-4">

                    <v-sheet max-width="800" class="align-left justify-left text-left mb-10">
                      <div class="text-h6">Site Health Info</div>


                      <v-expansion-panels class="mt-8" variant="accordion">
                        <v-expansion-panel v-for="debug in data.wp_juggler_health_data_info">
                          <v-expansion-panel-title v-if=" debug.fields.length > 0 && debug.show_count">
                            {{ debug.label }} ({{ debug.fields.length }})
                          </v-expansion-panel-title>
                          <v-expansion-panel-title v-else-if=" debug.fields.length > 0">
                            {{ debug.label }}
                          </v-expansion-panel-title>
                          <v-expansion-panel-text>
                            <v-table density="compact" >
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

                </v-tabs-window-item>

              </v-tabs-window>
            </v-card-text>
          </v-card>
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

.health-check-accordion-trigger .badge.blue,
.privacy-settings-accordion-trigger .badge.blue {
  border: 1px solid #72aee6
}

.health-check-accordion-trigger .badge.orange,
.privacy-settings-accordion-trigger .badge.orange {
  border: 1px solid #dba617
}

.health-check-accordion-trigger .badge.red,
.privacy-settings-accordion-trigger .badge.red {
  border: 1px solid #e65054
}

.health-check-accordion-trigger .badge.green,
.privacy-settings-accordion-trigger .badge.green {
  border: 1px solid #00ba37
}

.health-check-accordion-trigger .badge.purple,
.privacy-settings-accordion-trigger .badge.purple {
  border: 1px solid #2271b1
}

.health-check-accordion-trigger .badge.gray,
.privacy-settings-accordion-trigger .badge.gray {
  border: 1px solid #c3c4c7
}
</style>
