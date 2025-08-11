<template>
  <div>
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </div>
</template>

<script setup>
// Initialiser l'authentification au démarrage de l'application
const authStore = useAuthStore()

onMounted(async () => {
  if (process.client) {
    await authStore.initAuth()
  }
})
</script>
