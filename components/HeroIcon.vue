<template>
  <span
    :class="iconClasses"
    v-bind="$attrs"
  >
    {{ emojiIcon }}
  </span>
</template>

<script setup>
const props = defineProps({
  name: {
    type: String,
    required: true
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg', 'xl'].includes(value)
  }
})

const emojiMap = {
  'x-mark': '❌',
  'plus': '➕',
  'heart': '❤️',
  'calendar': '📅',
  'check': '✅',
  'exclamation-triangle': '⚠️',
  'clock': '🕐',
  'clipboard-document-list': '📋',
  'pencil': '✏️',
  'trash': '🗑️',
  'arrow-path': '🔄',
  'caret-left': '⬅️',
  'caret-right': '➡️',
  'document-text': '📄',
  'user': '👤',
  'eye': '👁️',
  'eye-slash': '🙈',
  'check-circle': '✅',
  'x-circle': '❌',
  'warning-circle': '⚠️',
  'spinner': '⏳',
  'arrow-left': '⬅️',
  'arrow-right': '➡️',
  'refresh': '🔄',
  'arrow-path': '🔄'
}

const emojiIcon = computed(() => {
  return emojiMap[props.name] || '❓'
})

const iconClasses = computed(() => {
  const sizeClasses = {
    sm: 'text-sm',
    md: 'text-lg',
    lg: 'text-xl',
    xl: 'text-3xl'
  }
  
  return sizeClasses[props.size] || sizeClasses.md
})
</script>
