<script setup>
import { cn } from '@/lib/utils.js';
const props = defineProps({ class: String });
</script>

<template>
  <tr :class="cn('border-b border-border transition-colors hover:bg-muted/50', props.class)"><slot /></tr>
</template>
