<script setup>
import { useWpjsStore } from "./store.js";
import { onMounted, computed, ref } from "vue";
import { useQueryClient, useQuery, useMutation } from "@tanstack/vue-query";

const props = defineProps(["activated"]);

const store = useWpjsStore()

const dialog = ref(false)

console.log('bkah');

</script>

<template>



</template>

<style>
</style>
