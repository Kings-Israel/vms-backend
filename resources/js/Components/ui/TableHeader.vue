<script setup>
import { cn } from '@/lib/utils.js';
const props = defineProps({ class: String });
</script>

<template>
  <thead :class="cn('[&_tr]:border-b [&_tr]:border-border', props.class)"><slot /></thead>
</template>
