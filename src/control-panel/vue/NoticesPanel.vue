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

const noticeHistoryItems = ref([]);
const noticePage = ref(0);

const refreshActive = ref(false)
const refreshNeeded = ref(true)
const ajaxError = ref(false);
const ajaxErrorText = ref('');

let infiniteScrollEvents

const queryClient = useQueryClient()
const { isLoading, isError, isFetching, data, error, refetch } = useQuery({
  queryKey: ["wpjs-notices-panel", store.activatedSite.id],
  queryFn: getNoticesPanel,
});

async function getNoticesPanel() {
  let ret = {};
  const response = await doAjax({
    action: "wpjs-get-notices-panel", // the action to fire in the server
    siteId: store.activatedSite.id,
  });
  ret = response.data[0];
  return ret;
}

async function getNoticeHistory() {
  let ret = {};
  const response = await doAjax({
    action: "wpjs-get-notices-history", // the action to fire in the server
    siteId: store.activatedSite.id,
    page: noticePage.value,
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

    return result

  } catch (error) {
    throw new Error('No response from the WP Juggler Server');
  }
}

async function loadNoticeHistory({ done }) {

  infiniteScrollEvents = done;

  const res = await getNoticeHistory();
  if (res.length == 0) {
    done("empty");
  } else {
    noticeHistoryItems.value.push(...res);
    noticePage.value =
      noticeHistoryItems.value[noticeHistoryItems.value.length - 1].ID;
    done("ok");
  }

}

async function refreshNotices() {
  refreshActive.value = true

  let ret = {};

  try {
    const response = await doAjax({
      action: "wpjs-refresh-notices", // the action to fire in the server
      siteId: store.activatedSite.id
    });

    if (response.success) {

      ret = response.data;

      refreshNeeded.value = false

      noticeHistoryItems.value = []
      noticePage.value = 0

      queryClient.invalidateQueries({
        queryKey: ["wpjs-notices-panel", store.activatedSite.id],
      })
      queryClient.invalidateQueries({
        queryKey: ["wpjs-control-panel"],
      })

      refreshNeeded.value = true

      refreshActive.value = false

      if (infiniteScrollEvents) {
        infiniteScrollEvents('ok');
      }

    } else {
      throw new Error(`${response.data.code} - ${response.data.message}`);
    }

  } catch (error) {
    ajaxError.value = true;
    ajaxErrorText.value = error.message
    refreshActive.value = false
  }

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

  const hitems = noticeHistoryItems.value;

  hitems.forEach((log) => {
    const date = new Date(log.log_timestamp * 1000);
    const monthYear = `${months[date.getMonth()]} ${date.getFullYear()}`;

    if (!groupedLogs[monthYear]) {
      groupedLogs[monthYear] = [];
    }
    log.notices = JSON.parse(log.log_data);
    groupedLogs[monthYear].push(log);
  });

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
    <v-dialog v-model="store.activatedNotices" transition="dialog-bottom-transition" fullscreen>
      <v-card>
        <v-toolbar>
          <v-btn icon="mdi-close" @click="store.activatedNotices = false"></v-btn>

          <v-toolbar-title>{{ store.activatedSite.title }} </v-toolbar-title>

          <v-spacer></v-spacer>

          <v-toolbar-items> </v-toolbar-items>
        </v-toolbar>

        <v-card-text v-if="data">
          <v-card>
            <v-card-text>
              <v-sheet class="pa-4 text-right mx-auto" elevation="0" width="100%" rounded="lg">
                <div v-if="data.wp_juggler_notices_timestamp">
                  <v-icon class="me-1 pb-1" icon="mdi-refresh" size="18"></v-icon>
                  {{ data.wp_juggler_notices_timestamp }}
                  <v-btn class="ml-3 text-none text-caption" :loading="refreshActive" @click="refreshNotices" variant="outlined">Refresh
                  </v-btn>
                </div>

                <div v-else>
                  <v-icon class="me-1 pb-1" icon="mdi-refresh" size="18"></v-icon>
                  Never
                  <v-btn class="ml-3 text-none text-caption" :loading="refreshActive" @click="refreshNotices" variant="outlined">Refresh
                  </v-btn>
                </div>
              </v-sheet>

              <v-sheet max-width="1200" class="align-left justify-left text-left mx-auto px-4 pb-4 mb-10">
                <div class="text-h6">Currently active Notices:</div>
                <v-divider class="mb-10"></v-divider>

                <v-sheet v-if="data.wp_juggler_notices.length > 0">
                  <v-row class="wpjs-debug-table-row" v-for="notice in data.wp_juggler_notices">
                    <v-col class="text-left" v-html="notice.NoticeHTML">
                    </v-col>
                  </v-row>
                </v-sheet>
                <v-sheet v-else>
                  <v-row class="wpjs-debug-table-row">
                    <v-col class="text-left"> No active notices </v-col>
                  </v-row>
                </v-sheet>

                <v-sheet v-if="data.wp_juggler_history_count > 0" class="align-left justify-left text-left px-5 mb-15">
                  <div class="text-h6 mt-15">Notices History:</div>

                  <v-infinite-scroll v-if="refreshNeeded" :height="600" :items="organizeByMonth"
                    :onLoad="loadNoticeHistory">
                    <template v-for="(item, name) in organizeByMonth" :key="item.ID">
                      <div v-if="item.length == 0" class="mt-10">
                        <div class="text-h6">{{ name }}</div>
                        <v-divider class="mb-4"></v-divider>
                        No incidents reported
                      </div>

                      <div v-else class="mt-10">
                        <div class="text-h6">{{ name }}</div>
                        <v-divider class="mb-4"></v-divider>
                        <v-expansion-panels class="mt-8" variant="accordion">
                          <v-expansion-panel v-for="notice in item">
                            <v-expansion-panel-title>
                              {{ notice.log_time }}
                              <v-spacer></v-spacer>
                              <div>{{ notice.notices.length }} Notices</div>
                            </v-expansion-panel-title>
                            <v-expansion-panel-text>
                              <v-sheet class="mt-2">
                                <v-row v-for="single_item in notice.notices" class="wpjs-debug-table-row">
                                  <v-col class="text-left px-5" v-html="single_item.NoticeHTML">
                                  </v-col>
                                </v-row>
                              </v-sheet>
                            </v-expansion-panel-text>
                          </v-expansion-panel>
                        </v-expansion-panels>
                      </div>
                    </template>
                  </v-infinite-scroll>
                </v-sheet>

                <v-sheet v-else class="align-left justify-left text-left px-5 mb-15">
                  <div class="text-h6 mt-15">No Recorded Notices History</div>
                </v-sheet>

              </v-sheet>
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
  </div>
</template>

<style>
.wpjs-debug-table-row {
  &:nth-child(odd) {
    background-color: #f7f7f7;
  }
}
</style>
