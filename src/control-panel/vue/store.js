import { defineStore } from "pinia";
import { ref, watch, computed } from "vue";

export const useWpjsStore = defineStore("wpjsstore", () => {
  const initial = ref("Dashboard");
  const activated_themes = ref(false)

  /* watch(activetab, (newactivetab, prevactivetab) => {
      
    }) */

  /* function increment() {
      count.value++
    } */

  //return { zoomlevel, doubleCount, increment }
  return {
    initial,
    activated_themes
  };
});
