export default defineNuxtPlugin(() => {
  const config = useRuntimeConfig()
  
  const api = {
    async get(url, options = {}) {
      try {
        const token = localStorage.getItem('mypet_token')
        
        const response = await $fetch(`${config.public.apiBase}${url}`, {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': token ? `Bearer ${token}` : '',
            ...options.headers
          },
          ...options
        })
        
        return response
      } catch (error) {
        throw error
      }
    },

    async post(url, data = {}, options = {}) {
      try {
        const token = localStorage.getItem('mypet_token')
        
        const response = await $fetch(`${config.public.apiBase}${url}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': token ? `Bearer ${token}` : '',
            ...options.headers
          },
          body: data,
          ...options
        })
        
        return response
      } catch (error) {
        throw error
      }
    },

    async put(url, data = {}, options = {}) {
      try {
        const token = localStorage.getItem('mypet_token')
        
        const response = await $fetch(`${config.public.apiBase}${url}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': token ? `Bearer ${token}` : '',
            ...options.headers
          },
          body: data,
          ...options
        })
        return response
      } catch (error) {
        throw error
      }
    },

    async delete(url, options = {}) {
      try {
        const token = localStorage.getItem('mypet_token')
        
        const response = await $fetch(`${config.public.apiBase}${url}`, {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': token ? `Bearer ${token}` : '',
            ...options.headers
          },
          ...options
        })
        return response
      } catch (error) {
        throw error
      }
    }
  }

  return {
    provide: {
      api
    }
  }
})
