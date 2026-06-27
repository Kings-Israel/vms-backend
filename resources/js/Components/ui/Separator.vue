<script setup>
import { cn } from '@/lib/utils.js';

const props = defineProps({
  class: String,
  orientation: { type: String, default: 'horizontal' },
});
</script>

<template>
  <div
    role="none"
    :class="cn(
      'shrink-0 bg-border',
      props.orientation === 'horizontal' ? 'h-[1px] w-full' : 'h-full w-[1px]',
      props.class
    )"
  />
</template>
