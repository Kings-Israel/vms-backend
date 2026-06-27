<script setup>
import { useVModel } from '@vueuse/core';
import { cn } from '@/lib/utils.js';

defineOptions({ inheritAttrs: false });

const props = defineProps({
  class: String,
  modelValue: [String, Number],
  defaultValue: String,
});

const emit = defineEmits(['update:modelValue']);
const modelValue = useVModel(props, 'modelValue', emit, { passive: true, defaultValue: props.defaultValue });
</script>

<template>
  <textarea
    v-bind="$attrs"
    v-model="modelValue"
    :class="cn(
      'flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground ring-offset-background',
      'placeholder:text-muted-foreground',
      'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
      'disabled:cursor-not-allowed disabled:opacity-50',
      props.class
    )"
  />
</template>
