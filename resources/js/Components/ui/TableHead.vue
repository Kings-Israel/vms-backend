<script setup>
import { cn } from '@/lib/utils.js';
const props = defineProps({ class: String });
</script>

<template>
  <th :class="cn('h-12 px-4 text-left align-middle text-xs font-medium uppercase tracking-wider text-muted-foreground', props.class)"><slot /></th>
</template>
