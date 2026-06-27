<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Components/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Pagination from '@/Components/Pagination.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Input from '@/components/ui/Input.vue';
import Select from '@/components/ui/Select.vue';
import { formatDateTime } from '@/lib/utils.js';
import { Search } from 'lucide-vue-next';

const props = defineProps({ visits: Object, filters: Object });
const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');

function applyFilters() {
  router.get(route('tenant.history'), { search: search.value, status: status.value }, { preserveState: true });
}

const columns = [
  { key: 'visitor', label: 'Visitor' },
  { key: 'purpose', label: 'Purpose' },
  { key: 'vehicle', label: 'Vehicle' },
  { key: 'status', label: 'Status' },
  { key: 'checked_in_at', label: 'Check-in' },
  { key: 'checked_out_at', label: 'Check-out' },
];
</script>

<template>
  <AppLayout>
    <div class="space-y-5">
      <h1 class="text-2xl font-bold text-foreground">Visit History</h1>

      <div class="flex flex-wrap gap-3">
        <div class="relative">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
          <Input v-model="search" @input="applyFilters" type="text" placeholder="Search..." class="pl-9" />
        </div>
        <Select v-model="status" @change="applyFilters">
          <option value="">All</option>
          <option value="expected">Expected</option>
          <option value="checked_in">Checked In</option>
          <option value="checked_out">Checked Out</option>
          <option value="cancelled">Cancelled</option>
        </Select>
      </div>

      <DataTable :columns="columns" :rows="visits.data">
        <template #visitor="{ row }">
          <p class="font-medium text-sm text-foreground">{{ row.visitor?.first_name }} {{ row.visitor?.last_name }}</p>
          <p class="text-xs text-muted-foreground">{{ row.visitor?.national_id }}</p>
        </template>
        <template #vehicle="{ row }">
          <span class="text-muted-foreground">{{ row.vehicle?.plate_number ?? '-' }}</span>
        </template>
        <template #status="{ row }"><StatusBadge :status="row.status" /></template>
        <template #checked_in_at="{ row }">
          <span class="text-muted-foreground text-sm">{{ formatDateTime(row.checked_in_at) }}</span>
        </template>
        <template #checked_out_at="{ row }">
          <span class="text-muted-foreground text-sm">{{ formatDateTime(row.checked_out_at) }}</span>
        </template>
      </DataTable>

      <Pagination :links="visits.links" />
    </div>
  </AppLayout>
</template>
