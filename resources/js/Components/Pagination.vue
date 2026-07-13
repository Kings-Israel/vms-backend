<script setup>
import { Link } from '@inertiajs/vue3';
import { cn } from '@/lib/utils.js';

defineProps({ links: Array });
</script>

<template>
  <div class="flex flex-wrap gap-1 justify-center mt-4">
    <template v-for="link in links" :key="link.label">
      <component
        :is="link.url ? Link : 'span'"
        :href="link.url"
        preserve-scroll
        :class="cn(
          'inline-flex items-center justify-center h-8 min-w-[2rem] px-3 rounded-md text-sm border transition-colors',
          link.active
            ? 'bg-primary text-primary-foreground border-primary'
            : 'bg-background text-muted-foreground border-border hover:bg-accent hover:text-accent-foreground',
          !link.url ? 'opacity-40 cursor-not-allowed pointer-events-none' : ''
        )"
      ><span v-html="link.label" /></component>
    </template>
  </div>
</template>
