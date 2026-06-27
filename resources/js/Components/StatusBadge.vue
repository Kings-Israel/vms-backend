<script setup>
import { computed } from 'vue';
import { cn } from '@/lib/utils.js';

const props = defineProps({ status: String });

const colors = {
  expected:    'border-blue-500/30 bg-blue-500/10 text-blue-400',
  checked_in:  'border-green-500/30 bg-green-500/10 text-green-400',
  checked_out: 'border-zinc-500/30 bg-zinc-500/10 text-zinc-400',
  denied:      'border-red-500/30 bg-red-500/10 text-red-400',
  cancelled:   'border-yellow-500/30 bg-yellow-500/10 text-yellow-400',
  scheduled:   'border-purple-500/30 bg-purple-500/10 text-purple-400',
  active:      'border-green-500/30 bg-green-500/10 text-green-400',
  completed:   'border-zinc-500/30 bg-zinc-500/10 text-zinc-400',
  missed:      'border-red-500/30 bg-red-500/10 text-red-400',
};

const label = computed(() => props.status?.replace(/_/g, ' '));
const color = computed(() => colors[props.status] ?? 'border-zinc-500/30 bg-zinc-500/10 text-zinc-400');
</script>

<template>
  <span :class="cn('inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize border', color)">
    {{ label }}
  </span>
</template>
