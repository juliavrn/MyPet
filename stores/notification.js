import { defineStore } from 'pinia'

export const useNotificationStore = defineStore('notification', () => {
  const notifs = ref([])

  const show = (message, type = 'info', time = 5000) => {
    const id = Date.now()
    const notif = {
      id,
      message,
      type,
      time
    }
    
    notifs.value.push(notif)
    
    setTimeout(() => {
      remove(id)
    }, time)
    
    return id
  }
  
  const success = (message, time) => show(message, 'success', time)
  const error = (message, time) => show(message, 'error', time)
  const warning = (message, time) => show(message, 'warning', time)
  const info = (message, time) => show(message, 'info', time)
  
  const remove = (id) => {
    const index = notifs.value.findIndex(n => n.id === id)
    if (index > -1) {
      notifs.value.splice(index, 1)
    }
  }
  
  const clear = () => {
    notifs.value = []
  }
  
  return {
    notifications: readonly(notifs),
    show,
    success,
    error,
    warning,
    info,
    remove,
    clear
  }
})
