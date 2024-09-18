<script setup>
import { useWpjsStore } from "./store.js";
import { onMounted, computed, ref } from "vue";
import { useQueryClient, useQuery, useMutation } from "@tanstack/vue-query";

const store = useWpjsStore();

const apiPopOverIndex = ref(-1)
const fePopOverIndex = ref(-1)

const uptimeHistoryItems = ref([]);
const uptimePage = ref(0);

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

    return result

  } catch (error) {
    throw new Error('No response from the WP Juggler Server');
  }
}

function graphApiMouseOver(index) {
  apiPopOverIndex.value = index;
}

function graphApiMouseOut(index) {
  apiPopOverIndex.value = -1;
}

function graphFEMouseOver(index) {
  fePopOverIndex.value = index;
}

function graphFEMouseOut(index) {
  fePopOverIndex.value = -1;
}

const apidowns = computed(() => {

  const incidentsLast90Days = Array.from({ length: 90 }, () => [])

  const now = new Date()
  const startOfToday = new Date(Date.UTC(
        now.getUTCFullYear(),
        now.getUTCMonth(),
        now.getUTCDate() + 1,
        0, 0, 0, 0
    ))
  
  const startOfTodayTimestamp = Math.floor(startOfToday.getTime() / 1000);

  data.value.wp_juggler_api_downs.forEach(incident => {

    const daysAgo = Math.floor((startOfTodayTimestamp - incident.log_timestamp) / 86400);

    if (daysAgo >= 0 && daysAgo < 90) {
      incidentsLast90Days[89 - daysAgo].push(incident);
    }
  });

  return incidentsLast90Days
})

function formatDate(unixTimestamp) {
  const date = new Date(unixTimestamp * 1000);
  const options = { month: 'short', day: '2-digit' };
  return date.toLocaleDateString('en-US', options);
}

function historyDateTime(dateTime) {
    const dateObj = new Date(dateTime);
    const options = { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
    return dateObj.toLocaleDateString('en-US', options);
}

const fedowns = computed(() => {

  const incidentsLast90Days = Array.from({ length: 90 }, () => [])

  const now = new Date()
  const startOfToday = new Date(Date.UTC(
        now.getUTCFullYear(),
        now.getUTCMonth(),
        now.getUTCDate() + 1,
        0, 0, 0, 0
    ))
  
  const startOfTodayTimestamp = Math.floor(startOfToday.getTime() / 1000);

  data.value.wp_juggler_fe_downs.forEach(incident => {
    const daysAgo = Math.floor((startOfTodayTimestamp - incident.log_timestamp) / 86400);

    console.log(daysAgo)

    if (daysAgo >= 0 && daysAgo < 90) {
      incidentsLast90Days[89 - daysAgo].push(incident);
    }
  });

  return incidentsLast90Days
})

async function loadUptimeHistory({ done }) {
  // Perform API call
  const res = await getUptimeHistory();
  if (res.length == 0) {
    done("empty");
  } else {
    uptimeHistoryItems.value.push(...res);
    uptimePage.value =
      uptimeHistoryItems.value[uptimeHistoryItems.value.length - 1].ID;
    done("ok");
  }
}

async function getUptimeHistory() {
  let ret = {};
  const response = await doAjax({
    action: "wpjs-get-uptime-history", // the action to fire in the server
    siteId: store.activatedSite.id,
    page: uptimePage.value,
  });

  ret = response.data;
  return ret;
}


const organizeByMonth = computed(() => {
  const months = [
    "Jan",
    "Feb",
    "Mar",
    "Apr",
    "May",
    "Jun",
    "Jul",
    "Aug",
    "Sep",
    "Oct",
    "Nov",
    "Dec",
  ];
  const groupedLogs = {};

  const hitems = uptimeHistoryItems.value;

  hitems.forEach((log) => {
    const date = new Date(log.log_timestamp * 1000);
    const monthYear = `${months[date.getMonth()]} ${date.getFullYear()}`;

    if (!groupedLogs[monthYear]) {
      groupedLogs[monthYear] = [];
    }
    groupedLogs[monthYear].push(log);
  });

  if (hitems.length > 0) {
    const earliestLog = new Date(hitems[0].log_timestamp * 1000);
    const currentDate = new Date();

    const startDate = new Date(earliestLog.getFullYear(), earliestLog.getMonth(), 1);

    while (startDate <= currentDate) {
      const year = startDate.getFullYear();
      const month = months[startDate.getMonth()];
      const monthYear = `${month} ${year}`;

      if (!groupedLogs[monthYear]) {
        groupedLogs[monthYear] = [];
      }

      startDate.setMonth(startDate.getMonth() + 1);
    }
  }

  const sortedMonths = Object.keys(groupedLogs).sort(
    (a, b) => new Date(b + " 1") - new Date(a + " 1")
  );

  const result = {};
  sortedMonths.forEach((monthYear) => {
    result[monthYear] = groupedLogs[monthYear];
  });

  return result;
});

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

        <v-card-text  v-if="data">

          <v-card class="mt-10">

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

                        <div v-if="apiPopOverIndex > -1" class="wpjs-api-tooltip text-left"
                          :style="{ left: (apiPopOverIndex * 5) + 'px' }">
                          <p>{{ formatDate(apidowns[apiPopOverIndex][0].log_timestamp) }} - {{
                            apidowns[apiPopOverIndex].length }} Incidents</p>
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
                                aria-describedby="uptime-tooltip" @mouseover="graphApiMouseOver(index)"
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

                        <div v-if="fePopOverIndex > -1" class="wpjs-api-tooltip text-left"
                          :style="{ left: (fePopOverIndex * 5) + 'px' }">
                          <p>{{ formatDate(fedowns[fePopOverIndex][0].log_timestamp) }} - {{
                            fedowns[fePopOverIndex].length }} Incidents</p>
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
                                aria-describedby="uptime-tooltip" @mouseover="graphFEMouseOver(index)"
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

                <v-sheet class="align-left justify-left text-left mb-15 mt-4">

                  <v-infinite-scroll :height="600" :items="uptimeHistoryItems" :onLoad="loadUptimeHistory" style="overflow-x: hidden;">

                    <template v-if="uptimeHistoryItems.length > 0" v-for="(item, name, index) in organizeByMonth"
                      :key="index">
                      <div v-if="item.length == 0" class="mt-0 mb-10">
                        <div class="text-h6 mb-4">{{ name }}</div>
                        <v-divider class="mb-4"></v-divider>
                        No incidents reported
                      </div>

                      <div v-else class="mt-0 mb-10">
                        <div class="text-h6 mb-4">{{ name }}</div>
                        <v-divider class="mb-4"></v-divider>

                        <v-sheet>
                          <v-row class="wpjs-debug-table-row" v-for="inc in item">
                            <v-col class="text-left pl-5">
                              {{ historyDateTime(inc.log_time) }}
                            </v-col>
                            <v-col class="text-left">
                              <div v-if="inc.log_type == 'confirmClientApi'">
                                API
                              </div>
                              <div v-if="inc.log_type == 'confirmFrontEnd'">
                                Front-End
                              </div>
                            </v-col>
                            <v-col class="text-left" cols="8">
                              {{ inc.log_value }}
                            </v-col>
                          </v-row>

                        </v-sheet>

                      </div>
                    </template>
                  </v-infinite-scroll>

                </v-sheet>

              </v-sheet>


            </v-card-text>
          </v-card>
        </v-card-text>

        <v-card-text v-else>
          <v-skeleton-loader type="heading, image, table-row-divider, list-item-two-line@4, table-tfoot"
            class="mt-15 mx-auto" max-width="1200">

          </v-skeleton-loader>

        </v-card-text>

      </v-card>
    </v-dialog>
  </div>
</template>

<style>
.wpjs-api-tooltip {
  position: absolute;
  top: -20px
}

.wpjs-debug-table-row {

  &:nth-child(odd) {
    background-color: #f7f7f7;
  }

}
</style>
