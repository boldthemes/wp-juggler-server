<script setup>

import { useWpjsStore } from './store.js'
import { onMounted, computed, ref } from 'vue'
import { useQueryClient, useQuery, useMutation } from '@tanstack/vue-query'

const queryClient = useQueryClient()

const store = useWpjsStore()

const nonce = ref(wpjs_settings_object.nonce)

const wpjs_cp_slug = ref('')
const save_loading = ref(false)

const snackbar = ref(false)
const snackbar_color = ref('success')
const snackbar_text = ref(snack_succ_text)
const snack_succ_text = 'WP Juggler Settings Saved'


const { isLoading, isError, isFetching, data, error, refetch } = useQuery({
  queryKey: ['wpjs-settings'],
  queryFn: getSettings
})

const mutation = useMutation({
  mutationFn: saveSettings,
  onSuccess: async () => {
    // Invalidate and refetch
    queryClient.invalidateQueries({ queryKey: ['wpjs-settings'] })
    save_loading.value = false

    snackbar_color.value = 'success'
    snackbar_text.value = snack_succ_text
    snackbar.value = true
  },
  onError: (error, variables, context) => {
    queryClient.invalidateQueries({ queryKey: ['wpjs-settings'] })
    save_loading.value = false

    snackbar_color.value = 'error'
    snackbar_text.value = error.responseJSON.data[0].message
    snackbar.value = true
  },
})

async function doAjax(args) {
  let result;
  try {
    result = await jQuery.ajax({
      url: wpjs_settings_object.ajaxurl,
      type: 'POST',
      data: args
    });
    return result;
  } catch (error) {
    throw (error)
  }
}

async function getSettings() {

  let ret = {}
  const response = await doAjax(
    {
      action: "wpjs_get_settings",  // the action to fire in the server
    }
  )
  ret = response.data

  wpjs_cp_slug.value = response.data.wpjs_cp_slug

  return ret
}

function clickSaveSettings() {
  save_loading.value = true
  mutation.mutate({
    wpjs_cp_slug: wpjs_cp_slug.value,
  })
}

async function saveSettings(obj) {

  obj.action = "wpjs_save_settings"
  obj.nonce = nonce.value

  const response = await doAjax(obj)
}

</script>

<template>

  <h1>WP Juggler Server Settings</h1>

  <v-card class="pa-4 mr-4">

    <table class="form-table" role="presentation">

      <tbody v-if="data">
        <tr>
          <th scope="row"><label for="blogname">Page Slug of Control Panel</label></th>
          <td>
            <input type="text" name="wpjscpslug" id="wpjscpslug" size="50" placeholder="" v-model="wpjs_cp_slug">
          </td>
        </tr>

      </tbody>
    </table>
    <p></p>
    <v-divider class="border-opacity-100"></v-divider>

    <p></p>

    <v-btn variant="flat" class="text-none text-caption" color="#2271b1" @click="clickSaveSettings"
      :loading="save_loading">
      Save Settings
    </v-btn>

    <v-snackbar v-model="snackbar" :timeout="3000" :color="snackbar_color">
      {{ snackbar_text }}
      <template v-slot:actions>
        <v-btn variant="text" @click="snackbar = false">
          X
        </v-btn>
      </template>
    </v-snackbar>

  </v-card>
</template>

<style></style>