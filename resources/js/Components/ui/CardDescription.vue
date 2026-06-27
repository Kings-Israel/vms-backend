<script setup>
import { cn } from '@/lib/utils.js';
const props = defineProps({ class: String });
</script>

<template>
  <p :class="cn('text-sm text-muted-foreground', props.class)"><slot /></p>
</template>
