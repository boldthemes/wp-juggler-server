<script setup>
import { useWpjsStore } from "./store.js";
import { onMounted, computed, ref } from "vue";
import { useQueryClient, useQuery, useMutation } from "@tanstack/vue-query";

const store = useWpjsStore();
const passedOpen = ref(false);

const search = ref("");

const dialogInner = ref(false);
const vulnerabilitiesItem = ref(null);

const tab = ref(0)

const noticeHistoryItems = ref([])
const noticePage = ref(0)

const { isLoading, isError, isFetching, data, error, refetch } = useQuery({
  queryKey: ["wpjs-notices-panel", store.activatedSite.id],
  queryFn: getNoticesPanel,
});

async function getNoticesPanel() {
  let ret = {};
  const response = await doAjax({
    action: "wpjs-get-notices-panel", // the action to fire in the server
    siteId: store.activatedSite.id
  });
  ret = response.data[0];
  return ret;
}

async function getNoticeHistory() {
  let ret = {};
  console.log('Ucitavam');
  console.log(noticePage.value);
  const response = await doAjax({
    action: "wpjs-get-notices-history", // the action to fire in the server
    siteId: store.activatedSite.id,
    page: noticePage.value
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
    throw error;
  }
}

async function loadNoticeHistory({ done }) {
  // Perform API call
    const res = await getNoticeHistory();
    console.log(res)
    if (res.length == 0) {
      done("empty");
    } else {
      noticeHistoryItems.value.push(...res);

      console.log(noticeHistoryItems.value);

      noticePage.value = noticeHistoryItems.value[noticeHistoryItems.value.length - 1].ID
      done("ok");
    }
}

</script>

<template>
  <div class="text-center pa-4">
    <v-dialog v-model="store.activatedNotices" transition="dialog-bottom-transition" fullscreen>
      <v-card>
        <v-toolbar>
          <v-btn icon="mdi-close" @click="store.activatedNotices = false"></v-btn>

          <v-toolbar-title>{{ store.activatedSite.title }}
          </v-toolbar-title>

          <v-spacer></v-spacer>

          <v-toolbar-items> </v-toolbar-items>
        </v-toolbar>

        <v-card-text>

          <v-card v-if="data">

            <v-card-text>
              <v-sheet class="pa-4 text-right mx-auto" elevation="0" width="100%" rounded="lg">
                <div v-if="data">
                  <v-icon class="me-1 pb-1" icon="mdi-refresh" size="18"></v-icon>
                  {{
                    data.wp_juggler_notices_timestamp
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
                    <v-col class="text-left">
                      No active notices
                    </v-col>
                  </v-row>
                </v-sheet>



                <div class="text-h6 mt-15">Notices History:</div>

                <v-sheet class="align-left justify-left text-left mb-15 mt-10">

                  <v-infinite-scroll :height="600" :items="noticeHistoryItems" :onLoad="loadNoticeHistory">
                    <!--<template v-for="(item, index) in noticeHistoryItems" :key="item">
                      <div class="text-h6 mt-10">Sep 2024</div>
                  <v-divider class="mb-4"></v-divider>
                  No incidents reported


                  <div class="text-h6 mt-10">Aug 2024</div>
                  <v-divider class="mb-4"></v-divider>
                  <v-expansion-panels class="mt-8" variant="accordion">
                    <v-expansion-panel>
                      <v-expansion-panel-title>
                        Aug 24, 2024 - 13:24:01
                        <v-spacer></v-spacer>
                        <div>3 Notices</div>
                      </v-expansion-panel-title>
                      <v-expansion-panel-text>
                        <v-sheet>
                          <v-row class="wpjs-debug-table-row">
                            <v-col class="text-left">
                              No route was found matching the URL and request method
                            </v-col>
                          </v-row>
                          <v-row class="wpjs-debug-table-row">
                            <v-col class="text-left">
                              No route was found matching the URL and request method
                            </v-col>
                          </v-row>

                        </v-sheet>
                      </v-expansion-panel-text>
                    </v-expansion-panel>
                  </v-expansion-panels>
                    </template>-->
                  </v-infinite-scroll>

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
.wpjs-debug-table-row {

  &:nth-child(odd) {
    background-color: #f7f7f7;
  }

}
</style>
