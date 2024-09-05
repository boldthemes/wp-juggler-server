<script setup>
import { useWpjsStore } from "./store.js";
import { onMounted, computed, ref } from "vue";
import { useQueryClient, useQuery, useMutation } from "@tanstack/vue-query";

const store = useWpjsStore();
const passedOpen = ref(false);

const search = ref("");

const dialogInner = ref(false);
const vulnerabilitiesItem = ref(null);

const tab = ref(0);

const apiPopOverIndex = ref(-1)
const fePopOverIndex = ref(-1)

const { isLoading, isError, isFetching, data, error, refetch } = useQuery({
  queryKey: ["wpjs-uptime-panel", store.activatedSite.id],
  queryFn: getUptimePanel,
});

async function getUptimePanel() {
  let ret = {};
  const response = await doAjax({
    action: "wpjs-get-uptime-panel", // the action to fire in the server
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

function graphApiMouseOver(index){
  apiPopOverIndex.value = index;
}

function graphApiMouseOut(index){
  apiPopOverIndex.value = -1;
}

function graphFEMouseOver(index){
  fePopOverIndex.value = index;
}

function graphFEMouseOut(index){
  fePopOverIndex.value = -1;
}

const apidowns = computed(() => {

  const incidentsLast90Days = Array.from({ length: 90 }, () => [])

  const now = Math.floor(Date.now() / 1000);
  const startOfToday = new Date();
  startOfToday.setUTCHours(0, 0, 0, 0);
  const startOfTodayTimestamp = Math.floor(startOfToday.getTime() / 1000);

  data.value.wp_juggler_api_downs.forEach(incident => {
    const daysAgo = Math.floor((startOfTodayTimestamp - incident.log_timestamp) / 86400);
    if (daysAgo >= 0 && daysAgo < 90) {
      incidentsLast90Days[89 - daysAgo].push(incident);
    }
  });

  console.log(incidentsLast90Days)

  return incidentsLast90Days
})

function formatDate(unixTimestamp) {
    const date = new Date(unixTimestamp * 1000);
    const options = { month: 'short', day: '2-digit' };
    return date.toLocaleDateString('en-US', options);
}

const fedowns = computed(() => {

  const incidentsLast90Days = Array.from({ length: 90 }, () => [])

  const now = Math.floor(Date.now() / 1000);
  const startOfToday = new Date();
  startOfToday.setUTCHours(0, 0, 0, 0);
  const startOfTodayTimestamp = Math.floor(startOfToday.getTime() / 1000);

  data.value.wp_juggler_fe_downs.forEach(incident => {
    const daysAgo = Math.floor((startOfTodayTimestamp - incident.log_timestamp) / 86400);
    if (daysAgo >= 0 && daysAgo < 90) {
      incidentsLast90Days[89 - daysAgo].push(incident);
    }
  });

  console.log(incidentsLast90Days)

  return incidentsLast90Days
})



</script>

<template>
  <div class="text-center pa-4">
    <v-dialog v-model="store.activatedUptime" transition="dialog-bottom-transition" fullscreen>
      <v-card>
        <v-toolbar>
          <v-btn icon="mdi-close" @click="store.activatedUptime = false"></v-btn>

          <v-toolbar-title>{{ store.activatedSite.title }}
          </v-toolbar-title>

          <v-spacer></v-spacer>

          <v-toolbar-items> </v-toolbar-items>
        </v-toolbar>

        <v-card-text>

          <v-card v-if="data" class="mt-10">

            <v-card-text>

              <v-sheet max-width="1200" class="align-center justify-center text-center mx-auto px-4 pb-4 mb-10">

                <div class="text-h6">Incidents over the past 90 days</div>

                <v-sheet class="align-center justify-center text-center mb-15 mt-10 mx-auto">
                  <v-row>
                    <v-col>
                      <v-row class="py-0">
                        <v-col class="text-left pl-15 py-4">
                          <div class="text-h6">API</div>
                        </v-col>
                      </v-row>
                      <v-row style="position: relative;">
                        
                        <div v-if="apiPopOverIndex > -1" class="wpjs-api-tooltip text-left" :style="{ left: (apiPopOverIndex * 5) + 'px' }">
                          <p>{{ formatDate(apidowns[apiPopOverIndex][0].log_timestamp) }} - <strong>{{ apidowns[apiPopOverIndex].length }} Incidents</strong></p>
                        </div>

                        <v-col class="align-center justify-center text-center py-0">
                          <svg class="availability-time-line-graphic mx-auto" id="uptime-component-qgf0gk4xsbmd"
                            preserveAspectRatio="none" height="34" viewBox="0 0 448 34">
                            <g v-for="(apidown, index) in apidowns">
                              
                              <rect v-if="apidown.length == 0" height="34" width="3" :x="index * 5" y="0" fill="#26b47f"
                                role="tab" class="uptime-day component-qgf0gk4xsbmd day-0" data-html="true" tabindex="0"
                                aria-describedby="uptime-tooltip"></rect>
                              
                                <rect v-else height="34" width="3" :x="index * 5" y="0" fill="#E57373" role="tab"
                                  class="uptime-day component-qgf0gk4xsbmd day-0" data-html="true" tabindex="0"
                                  aria-describedby="uptime-tooltip"  @mouseover="graphApiMouseOver(index)"
                                  @mouseout="graphApiMouseOut(index)"></rect>

                            </g>
                          </svg>
                        </v-col>
                      </v-row>
                      <v-row class="py-0">
                        <v-col class="text-left pl-15 py-0">
                          90 days ago
                        </v-col>
                        <v-col class="text-right pr-15 py-0">
                          Today
                        </v-col>
                      </v-row>
                    </v-col>
                    <v-col>
                      <v-row class="py-0">
                        <v-col class="text-left pl-15 py-4">
                          <div class="text-h6">Front-End</div>
                        </v-col>
                      </v-row>
                      <v-row style="position: relative;">
                        
                        <div v-if="fePopOverIndex > -1" class="wpjs-api-tooltip text-left" :style="{ left: (fePopOverIndex * 5) + 'px' }">
                          <p>{{ formatDate(fedowns[fePopOverIndex][0].log_timestamp) }} - <strong>{{ fedowns[fePopOverIndex].length }} Incidents</strong></p>
                        </div>

                        <v-col class="align-center justify-center text-center py-0">
                          <svg class="availability-time-line-graphic mx-auto" id="uptime-component-qgf0gk4xsbmd"
                            preserveAspectRatio="none" height="34" viewBox="0 0 448 34">
                            <g v-for="(fedown, index) in fedowns">
                              
                              <rect v-if="fedown.length == 0" height="34" width="3" :x="index * 5" y="0" fill="#26b47f"
                                role="tab" class="uptime-day component-qgf0gk4xsbmd day-0" data-html="true" tabindex="0"
                                aria-describedby="uptime-tooltip"></rect>
                              
                                <rect v-else height="34" width="3" :x="index * 5" y="0" fill="#E57373" role="tab"
                                  class="uptime-day component-qgf0gk4xsbmd day-0" data-html="true" tabindex="0"
                                  aria-describedby="uptime-tooltip"  @mouseover="graphFEMouseOver(index)"
                                  @mouseout="graphFEMouseOut(index)"></rect>

                            </g>
                          </svg>
                        </v-col>
                      </v-row>
                      <v-row>
                        <v-col class="text-left pl-15 py-0">
                          90 days ago
                        </v-col>
                        <v-col class="text-right pr-15 py-0">
                          Today
                        </v-col>
                      </v-row>
                    </v-col>

                  </v-row>
                </v-sheet>

                <div class="text-h6 mt-15">Incident History</div>

                <v-sheet class="align-left justify-left text-left mb-15 mt-10">
                  <div class="text-h6 mt-10">Sep 2024</div>
                  <v-divider class="mb-4"></v-divider>
                  No incidents reported
                  <div class="text-h6 mt-10">Aug 2024</div>
                  <v-divider class="mb-4"></v-divider>
                  No incidents reported
                  <div class="text-h6 mt-10">Aug 2024</div>
                  <v-divider class="mb-4"></v-divider>
                  <v-sheet>
                    <v-row class="wpjs-debug-table-row">
                      <v-col class="text-left">
                        Aug 24, 2024 - 13:24:01
                      </v-col>
                      <v-col class="text-left">
                        Front-End
                      </v-col>
                      <v-col class="text-left" cols="6">
                        No route was found matching the URL and request method
                      </v-col>
                    </v-row>

                    <v-row class="wpjs-debug-table-row">
                      <v-col class="text-left">
                        Aug 24, 2024 - 13:24:01
                      </v-col>
                      <v-col class="text-left">
                        Front-End
                      </v-col>
                      <v-col class="text-left" cols="6">
                        No route was found matching the URL and request method
                      </v-col>
                    </v-row>

                  </v-sheet>
                </v-sheet>

              </v-sheet>


            </v-card-text>
          </v-card>
        </v-card-text>
      </v-card>
    </v-dialog>
  </div>
</template>

<style>

.wpjs-api-tooltip{
  position:absolute;
  top:-20px
}

.wpjs-debug-table-row {

  &:nth-child(odd) {
    background-color: #f7f7f7;
  }

}
</style>
