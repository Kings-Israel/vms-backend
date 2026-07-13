<script setup>
import { cn } from '@/lib/utils.js';
const props = defineProps({ class: String });
</script>

<template>
  <h3 :class="cn('text-lg font-semibold leading-none tracking-tight text-card-foreground', props.class)"><slot /></h3>
</template>
