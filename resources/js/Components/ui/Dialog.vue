<script setup>
defineProps({
  open: Boolean,
  maxWidth: { type: String, default: 'max-w-lg' },
});

defineEmits(['close']);
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/80" @click="$emit('close')" />
        <Transition
          enter-active-class="duration-200 ease-out"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="duration-150 ease-in"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
        >
          <div v-if="open" :class="['relative z-10 w-full rounded-lg border border-border bg-background shadow-lg max-h-[90vh] overflow-y-auto', maxWidth]">
            <slot />
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>
