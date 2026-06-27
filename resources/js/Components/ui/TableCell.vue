<script setup>
import { cn } from '@/lib/utils.js';
const props = defineProps({ class: String });
</script>

<template>
  <td :class="cn('px-4 py-3 align-middle', props.class)"><slot /></td>
</template>
