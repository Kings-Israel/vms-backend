<script setup>
import { useVModel } from '@vueuse/core';
import { cn } from '@/lib/utils.js';

defineOptions({ inheritAttrs: false });

const props = defineProps({
  class: String,
  modelValue: [String, Number, Array],
  defaultValue: { type: [String, Number, Array], default: '' },
});

const emit = defineEmits(['update:modelValue']);
const modelValue = useVModel(props, 'modelValue', emit, { passive: true, defaultValue: props.defaultValue });
</script>

<template>
  <select
    v-bind="$attrs"
    v-model="modelValue"
    :class="cn(
      'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground ring-offset-background',
      'focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
      'disabled:cursor-not-allowed disabled:opacity-50',
      props.class
    )"
  >
    <slot />
  </select>
</template>
