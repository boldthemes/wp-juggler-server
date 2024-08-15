import { defineStore } from "pinia";
import { ref, watch, computed } from "vue";

export const useWpjsStore = defineStore("wpjsstore", () => {
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
