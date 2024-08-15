import { defineStore } from "pinia";
import { ref, watch, computed } from "vue";

export const useDirekttStore = defineStore("direkttstore", () => {
  const initial = ref("Settings");

  /* watch(activetab, (newactivetab, prevactivetab) => {
      
    }) */

  /* function increment() {
      count.value++
    } */

  //return { zoomlevel, doubleCount, increment }
  return {
    initial,
  };
});
