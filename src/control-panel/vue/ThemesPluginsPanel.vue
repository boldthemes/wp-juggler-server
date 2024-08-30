<script setup>
import { useWpjsStore } from "./store.js";
import { onMounted, computed, ref } from "vue";
import { useQueryClient, useQuery, useMutation } from "@tanstack/vue-query";

const store = useWpjsStore()

const dialog = ref(false)

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
          <v-btn icon="mdi-close" @click="store.activatedThemes = false"></v-btn>

          <v-toolbar-title>Themes and Plugins - {{ store.activatedSite.title }} </v-toolbar-title>

          <v-spacer></v-spacer>

          <v-toolbar-items>
            
          </v-toolbar-items>
        </v-toolbar>

        
      </v-card>
    </v-dialog>
  </div>



</template>

<style>
</style>
