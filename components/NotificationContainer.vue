<template>
  <div class="notif-container" v-if="notifs && notifs.length > 0">
    <TransitionGroup name="notif" tag="div">
      <div
        v-for="notif in notifs"
        :key="notif?.id"
        v-if="notif && notif.type && notif.message"
        :class="['notif', `notif--${notif.type}`]"
        @click="remove(notif.id)"
      >
        <div class="notif__icon">
          <span v-if="notif.type === 'success'">✅</span>
          <span v-else-if="notif.type === 'error'">❌</span>
          <span v-else-if="notif.type === 'warning'">⚠️</span>
          <span v-else>ℹ️</span>
        </div>
        <div class="notif__content">
          <p class="notif__message">{{ notif.message }}</p>
        </div>
        <button 
          @click.stop="remove(notif.id)"
          class="notif__close"
        >
          ×
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { useNotificationStore } from '~/stores/notification'

const notificationStore = useNotificationStore()
const { notifications: notifs, remove } = notificationStore
</script>

<style scoped>
.notif-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  max-width: 400px;
}

.notif {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  padding: 16px;
  margin-bottom: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  border-left: 4px solid #e5e7eb;
}

.notif:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

.notif--success {
  border-left-color: #10b981;
}

.notif--error {
  border-left-color: #ef4444;
}

.notif--warning {
  border-left-color: #f59e0b;
}

.notif--info {
  border-left-color: #3b82f6;
}

.notif__icon {
  flex-shrink: 0;
  font-size: 20px;
  line-height: 1;
}

.notif__content {
  flex: 1;
  min-width: 0;
}

.notif__message {
  margin: 0;
  font-size: 14px;
  line-height: 1.4;
  color: #374151;
}

.notif__close {
  background: none;
  border: none;
  font-size: 18px;
  color: #9ca3af;
  cursor: pointer;
  padding: 0;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.notif__close:hover {
  background: #f3f4f6;
  color: #6b7280;
}

.notif-enter-active,
.notif-leave-active {
  transition: all 0.3s ease;
}

.notif-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.notif-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.notif-move {
  transition: transform 0.3s ease;
}
</style>
