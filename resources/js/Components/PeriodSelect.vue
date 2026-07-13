<script setup>
import Select from '@/components/ui/Select.vue';

defineProps({ modelValue: String });
defineEmits(['update:modelValue']);

const options = [
  { value: 'week', label: 'Past Week' },
  { value: 'month', label: 'Past Month' },
  { value: '2months', label: 'Past 2 Months' },
  { value: '6months', label: 'Past 6 Months' },
  { value: 'year', label: 'Past Year' },
];
</script>

<template>
  <Select
    :model-value="modelValue"
    @update:model-value="$emit('update:modelValue', $event)"
    class="w-auto text-xs h-8"
  >
    <option v-for="opt in options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
  </Select>
</template>
