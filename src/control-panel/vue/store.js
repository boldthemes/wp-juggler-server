import { defineStore } from "pinia";
import { ref, watch, computed } from "vue";

export const useWpjsStore = defineStore("wpjsstore", () => {
  const initial = ref("Dashboard");
  
  const activatedThemes = ref(false)
  const activatedHealth = ref(false)
  const activatedUptime = ref(false)
  const activatedSite = ref(null)
  const activatedNotices = ref(null)

  const nonce = ref('')
  const ajaxUrl = ref('')

  /* watch(activetab, (newactivetab, prevactivetab) => {
      
    }) */

  /* function increment() {
      count.value++
    } */

  //return { zoomlevel, doubleCount, increment }
  return {
    initial,
    nonce,
    ajaxUrl,
    activatedSite,
    activatedThemes,
    activatedHealth,
    activatedUptime,
    activatedNotices
  };
});
