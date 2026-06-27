<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Components/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Pagination from '@/Components/Pagination.vue';
import Input from '@/components/ui/Input.vue';
import Select from '@/components/ui/Select.vue';
import { formatDateTime } from '@/lib/utils.js';

const props = defineProps({ logs: Object, users: Array, filters: Object });

const causerId = ref(props.filters.causer_id ?? '');
const from = ref(props.filters.from ?? '');
const to = ref(props.filters.to ?? '');

function applyFilters() {
  router.get(route('reports.activity-log'), { causer_id: causerId.value, from: from.value, to: to.value }, { preserveState: true });
}

const columns = [
  { key: 'created_at', label: 'Time' },
  { key: 'causer', label: 'User' },
  { key: 'description', label: 'Activity' },
  { key: 'subject', label: 'Subject' },
];
</script>

<template>
  <AppLayout>
    <div class="space-y-5">
      <h1 class="text-2xl font-bold text-foreground">Activity Log</h1>

      <div class="flex flex-wrap gap-3">
        <Select v-model="causerId" @change="applyFilters">
          <option value="">All Users</option>
          <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
        </Select>
        <Input v-model="from" @change="applyFilters" type="date" class="w-auto" placeholder="From" />
        <Input v-model="to" @change="applyFilters" type="date" class="w-auto" placeholder="To" />
      </div>

      <DataTable :columns="columns" :rows="logs.data">
        <template #created_at="{ row }">
          <span class="text-muted-foreground text-sm">{{ formatDateTime(row.created_at) }}</span>
        </template>
        <template #causer="{ row }">
          <span class="text-foreground">{{ row.causer?.name ?? 'System' }}</span>
        </template>
        <template #description="{ row }">
          <span class="text-foreground">{{ row.description }}</span>
        </template>
        <template #subject="{ row }">
          <span class="text-muted-foreground">{{ row.subject_type ? row.subject_type.split('\\').pop() : '-' }}</span>
        </template>
      </DataTable>

      <Pagination :links="logs.links" />
    </div>
  </AppLayout>
</template>
