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

const { isLoading, isError, isFetching, data, error, refetch } = useQuery({
  queryKey: ["wpjs-health-panel", store.activatedSite.id],
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
  return passedOpen.value ? 'mdi-chevron-up' : 'mdi-chevron-down'
})

function debugFields(fieldArray) {
  return fieldArray.filter(item => item.debug !== 'loading...');
}

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

          <v-card v-if="data">

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
                      <v-row>
                        <v-col class="align-center justify-center text-center py-0">
                          <svg class="availability-time-line-graphic mx-auto" id="uptime-component-qgf0gk4xsbmd"
                            preserveAspectRatio="none" height="34" viewBox="0 0 448 34">

                            <rect height="34" width="3" x="0" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-0" data-html="true" tabindex="0"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="5" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-1" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="10" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-2" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="15" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-3" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="20" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-4" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="25" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-5" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="30" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-6" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="35" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-7" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="40" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-8" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="45" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-9" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="50" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-10" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="55" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-11" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="60" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-12" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="65" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-13" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="70" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-14" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="75" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-15" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="80" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-16" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="85" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-17" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="90" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-18" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="95" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-19" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="100" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-20" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="105" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-21" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="110" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-22" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="115" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-23" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="120" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-24" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="125" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-25" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="130" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-26" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="135" y="0" fill="#97b43d" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-27" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="140" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-28" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="145" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-29" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="150" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-30" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="155" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-31" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="160" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-32" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="165" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-33" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="170" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-34" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="175" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-35" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="180" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-36" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="185" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-37" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="190" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-38" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="195" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-39" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="200" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-40" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="205" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-41" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="210" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-42" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="215" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-43" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="220" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-44" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="225" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-45" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="230" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-46" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="235" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-47" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="240" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-48" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="245" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-49" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="250" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-50" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="255" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-51" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="260" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-52" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="265" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-53" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="270" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-54" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="275" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-55" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="280" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-56" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="285" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-57" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="290" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-58" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="295" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-59" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="300" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-60" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="305" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-61" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="310" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-62" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="315" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-63" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="320" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-64" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="325" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-65" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="330" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-66" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="335" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-67" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="340" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-68" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="345" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-69" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="350" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-70" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="355" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-71" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="360" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-72" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="365" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-73" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="370" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-74" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="375" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-75" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="380" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-76" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="385" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-77" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="390" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-78" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="395" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-79" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="400" y="0" fill="#e3b411" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-80" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="405" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-81" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="410" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-82" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="415" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-83" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="420" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-84" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="425" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-85" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="430" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-86" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="435" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-87" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="440" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-88" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="445" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-89" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
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
                      <v-row>
                        <v-col class="align-center justify-center text-center py-0">
                          <svg class="availability-time-line-graphic mx-auto" id="uptime-component-qgf0gk4xsbmd"
                            preserveAspectRatio="none" height="34" viewBox="0 0 448 34">

                            <rect height="34" width="3" x="0" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-0" data-html="true" tabindex="0"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="5" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-1" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="10" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-2" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="15" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-3" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="20" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-4" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="25" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-5" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="30" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-6" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="35" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-7" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="40" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-8" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="45" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-9" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="50" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-10" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="55" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-11" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="60" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-12" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="65" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-13" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="70" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-14" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="75" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-15" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="80" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-16" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="85" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-17" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="90" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-18" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="95" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-19" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="100" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-20" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="105" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-21" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="110" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-22" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="115" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-23" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="120" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-24" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="125" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-25" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="130" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-26" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="135" y="0" fill="#97b43d" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-27" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="140" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-28" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="145" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-29" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="150" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-30" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="155" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-31" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="160" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-32" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="165" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-33" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="170" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-34" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="175" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-35" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="180" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-36" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="185" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-37" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="190" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-38" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="195" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-39" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="200" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-40" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="205" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-41" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="210" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-42" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="215" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-43" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="220" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-44" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="225" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-45" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="230" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-46" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="235" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-47" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="240" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-48" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="245" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-49" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="250" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-50" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="255" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-51" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="260" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-52" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="265" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-53" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="270" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-54" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="275" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-55" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="280" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-56" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="285" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-57" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="290" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-58" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="295" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-59" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="300" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-60" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="305" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-61" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="310" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-62" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="315" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-63" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="320" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-64" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="325" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-65" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="330" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-66" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="335" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-67" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="340" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-68" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="345" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-69" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="350" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-70" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="355" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-71" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="360" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-72" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="365" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-73" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="370" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-74" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="375" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-75" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="380" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-76" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="385" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-77" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="390" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-78" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="395" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-79" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="400" y="0" fill="#e3b411" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-80" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="405" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-81" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="410" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-82" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="415" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-83" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="420" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-84" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="425" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-85" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="430" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-86" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="435" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-87" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="440" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-88" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
                            <rect height="34" width="3" x="445" y="0" fill="#26b47f" role="tab"
                              class="uptime-day component-qgf0gk4xsbmd day-89" data-html="true" tabindex="-1"
                              aria-describedby="uptime-tooltip"></rect>
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


.wpjs-debug-table-row {

  &:nth-child(odd) {
    background-color: #f7f7f7;
  }

}

</style>
